<?php

namespace App\Http\Controllers;

use App\Mail\BookingCreateMailer;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Payrexx\Models\Response\Transaction as TransactionResponse;
use App\Models\Voucher;


class PayrexxController
{
    /**
     * Process the data returned from Payrexx after an operation
     * As of 2022-10, we're only interested in Transactions with status=confirmed
     * (i.e. not refunds)
     *
     * @link https://developers.payrexx.com/docs/transaction
     *
     * @return an empty page with "OK"
     */
    public function processNotification(Request $request)
    {
        // 0. Log Payrexx response
        Log::channel('payrexx')->debug('processNotification');
        Log::channel('payrexx')->debug(print_r($request->all(), 1));

        // Can be Booking or Voucher
        try {
            // 1. Pick their Transaction data
            $data = $request->transaction;
            if ($data && is_array($data) && isset($data['status']) &&
                $data['status'] === TransactionResponse::CONFIRMED) {
                // 2. Pick related Booking from our database:
                // we sent its ReferenceID when the payment was requested
                $referenceID = trim($data['referenceId'] ?? '');

                $booking = (strlen($referenceID) > 2)
                    ? Booking::withTrashed()->with(['school', 'bookingUsers'])
                        ->where('payrexx_reference', '=', $referenceID)
                        ->first()
                    : null;

                if ($booking) {

                    // Continue if still unpaid and user chose Payrexx (i.e. BoukiiPay or Online payment methods) - else ignore
                    if (!$booking->paid &&
                        ($booking->payment_method_id == 2 || $booking->payment_method_id == 3)) {
                        // 3. Pick its related School and its Payrexx credentials...
                        $schoolData = $booking->school;
                        if ($schoolData && $schoolData->getPayrexxInstance() && $schoolData->getPayrexxKey()) {
                            // ...to check that it's a legitimate Transaction:
                            // we can't assert that this Notification really came from Payrexx
                            // (or was faked by someone who just did a POST to our URL)
                            // because it has no special signature.
                            // So we just pick its ID and ask Payrexx for the details
                            $transactionID = intval($data['id'] ?? -1);
                            $data2 = PayrexxHelpers::retrieveTransaction(
                                $schoolData->getPayrexxInstance(),
                                $schoolData->getPayrexxKey(),
                                $transactionID
                            );

                            if ($data2 && $data2->getStatus() === TransactionResponse::CONFIRMED) {
                                if ($booking->trashed()) {
                                    $booking->restore(); // Restaurar la reserva eliminada
                                    foreach($booking->bookingUsers as $bookinguser){
                                        if($bookinguser->trashed()) {
                                            $bookinguser->restore();
                                        }
                                    }
                                }
                                $buyerUser = Client::find($booking->client_main_id);
                                if ($booking->payment_method_id == 2 && $booking->source == 'web') {
                                    $pendingVouchers = $booking->vouchersLogs()->where('status', 'pending')->get();

                                    foreach ($pendingVouchers as $voucherLog) {
                                        // Encuentra el voucher asociado al log
                                        $voucher = Voucher::find($voucherLog->voucher_id);

                                        if ($voucher) {
                                            // Resta el amount del log al remaining_balance del voucher
                                            $voucher->remaining_balance -= abs($voucherLog->amount);
                                            $voucher->save();

                                            // Actualiza el estado del log a 'confirmed'
                                            $voucherLog->status = null;
                                            $voucherLog->save();
                                        }
                                    }
                                    // As of 2022-10-25 tell buyer user by email at this point, even before payment, and continue
                                    dispatch(function () use ($schoolData, $booking, $buyerUser) {
                                        // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                                        try {
                                            \Mail::to($buyerUser->email)
                                                ->send(new BookingCreateMailer(
                                                    $schoolData,
                                                    $booking,
                                                    $buyerUser,
                                                    true
                                                ));
                                        } catch (\Exception $ex) {
                                            \Illuminate\Support\Facades\Log::debug('BookingController->createBooking BookingCreateMailer: ' .
                                                $ex->getMessage());
                                        }
                                    })->afterResponse();
                                }

                                // Everything seems to fit, so mark booking as paid,
                                // storing some Transaction info for future refunds
                                // N.B: as of 2022-10-08 field $data2->invoice->totalAmount is null
                                // (at least on Test mode)
                                // fallback to $data->amount
                                // (which might been faked)
                                $booking->paid = true;
                                $booking->setPayrexxTransaction([
                                    'id' => $transactionID,
                                    'time' => $data2->getTime(),
                                    'totalAmount' => $data2->getInvoice()['totalAmount'] ?? $data['amount'],
                                    'refundedAmount' => $data2->getInvoice()['refundedAmount'] ?? 0,
                                    'currency' => $data2->getInvoice()['currencyAlpha3'],
                                    'brand' => $data2->getPayment()['brand'],
                                    'referenceId' => $referenceID
                                ]);

                                $booking->paid_total = $booking->paid_total +
                                    ($data2->getInvoice()['totalAmount'] ?? $data['amount']) / 100;

                                $payment = new Payment();
                                $payment->booking_id = $booking->id;
                                $payment->school_id = $booking->school_id;
                                $payment->amount = ($data2->getInvoice()['totalAmount'] ?? $data['amount']) / 100;
                                $payment->status = 'paid';
                                $payment->payrexx_reference = $referenceID;
                                $payment->payrexx_transaction = $booking->payrexx_transaction;
                                $payment->save();

                                $booking->save();
                            }
                        }
                    }

                } else {
                    $voucher = (strlen($referenceID) > 2)
                        ? Voucher::with('school')->where('payrexx_reference', '=', $referenceID)->first()
                        : null;

                    if (!$voucher) {
                        throw new \Exception('No Booking or Voucher found with payrexx_reference: ' . $referenceID);
                    }

                    if (!$voucher->payed) {
                        $schoolData = $voucher->school;
                        if ($schoolData && $schoolData->getPayrexxInstance() && $schoolData->getPayrexxKey()) {
                            $transactionID = intval($data['id'] ?? -1);
                            $data2 = PayrexxHelpers::retrieveTransaction(
                                $schoolData->getPayrexxInstance(),
                                $schoolData->getPayrexxKey(),
                                $transactionID
                            );

                            if ($data2 && $data2->getStatus() === TransactionResponse::CONFIRMED) {

                                $buyerUser = User::find($booking->client_main_id);
                                if ($booking->payment_method_id == 2 && $booking->source == 'web') {
                                    // As of 2022-10-25 tell buyer user by email at this point, even before payment, and continue
                                    dispatch(function () use ($schoolData, $booking, $buyerUser) {
                                        // N.B. try-catch because some test users enter unexistant emails, throwing Swift_TransportException
                                        try {
                                            \Mail::to($buyerUser->email)
                                                ->send(new BookingCreateMailer(
                                                    $schoolData,
                                                    $booking,
                                                    $buyerUser,
                                                    true
                                                ));
                                        } catch (\Exception $ex) {
                                            \Illuminate\Support\Facades\Log::debug('BookingController->createBooking BookingCreateMailer: ' .
                                                $ex->getMessage());
                                        }
                                    })->afterResponse();
                                }

                                $voucher->payed = true;
                                $voucher->setPayrexxTransaction([
                                    'id' => $transactionID,
                                    'time' => $data2->getTime(),
                                    'totalAmount' => $data2->getInvoice()['totalAmount'] ?? $data['amount'],
                                    'refundedAmount' => $data2->getInvoice()['refundedAmount'] ?? 0,
                                    'currency' => $data2->getInvoice()['currencyAlpha3'],
                                    'brand' => $data2->getPayment()['brand'],
                                    'referenceId' => $referenceID
                                ]);

                                $voucher->save();
                            }
                        }
                    }
                }

            }
        } catch (\Exception $e) {
            Log::channel('payrexx')->error('processNotification');
            Log::channel('payrexx')->error($e->getMessage());
        }

        return response()->make('OK');
    }
}
