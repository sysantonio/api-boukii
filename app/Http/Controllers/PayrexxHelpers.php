<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Client;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use Payrexx\Models\Request\Gateway as GatewayRequest;
use Payrexx\Models\Request\Invoice as InvoiceRequest;
use Payrexx\Models\Request\Transaction as TransactionRequest;
use Payrexx\Models\Response\Transaction as TransactionResponse;
use Payrexx\Payrexx;
use Payrexx\PayrexxException;

use App\Mail\BookingPayMailer;
use App\Models\Language;
use App\Models\School;


/**
 * Convenient wrapper for Payrexx API
 * @see https://github.com/payrexx/payrexx-php/tree/master/examples
 */
class PayrexxHelpers
{
    /**
     * Prepare a Payrexx Gateway link to start a Transaction.
     * @see https://developers.payrexx.com/reference/create-a-gateway
     *
     * @param School $schoolData i.e. who wants the money
     * @param Booking $bookingData i.e. the Booking ID this payment is for
     * @param Client|null $buyerUser to get his payment & contact details
     * @param string $redirectTo tell Payrexx to redirect back to a certain URL,
     * or "panel" frontend page, or to an special empty screen for "app", or nowhere
     *
     * @return string empty if something failed
     */
    public static function createGatewayLinkNew($schoolData, $bookingData,
                                             $basketData, Client $buyerUser = null, $redirectTo = null)
    {
        $link = '';

        try {

            // Check that School has Payrexx credentials
            //dd($schoolData->getPayrexxInstance());
            if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                throw new \Exception('No credentials for School ID=' . $schoolData->id);
            }

            // Prepare gateway: basic data
            $gr = new GatewayRequest();
            $gr->setReferenceId($bookingData->getOrGeneratePayrexxReference());
            $gr->setAmount($bookingData->price_total * 100);
            $gr->setCurrency($bookingData->currency);
            $gr->setVatRate($schoolData->bookings_comission_cash);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???

            // Add School's legal terms, if set
            if ($schoolData->conditions_url) {
                $gr->addField('terms', $schoolData->conditions_url);
            }

            // Calcular el precio total del "basket"
            $totalAmount = array_reduce($basketData->all(), function($carry, $item) {
                return $carry + $item['amount'];
            }, 0);

            $gr->setBasket($basketData->all());
            $gr->setAmount($totalAmount);

            // Buyer data
            if ($buyerUser) {
                $gr->addField('forename', $buyerUser->first_name);
                $gr->addField('surname', $buyerUser->last_name);
                $gr->addField('phone', $buyerUser->phone);
                $gr->addField('email', $buyerUser->email);
                $gr->addField('street', $buyerUser->address);
                $gr->addField('postcode', $buyerUser->cp);

                $gr->addField('place', $buyerUser->province);
                $gr->addField('country',  $buyerUser->country);
            }

            // OK/error pages to redirect user after payment
            if ($redirectTo == 'panel') {
                $gr->setSuccessRedirectUrl(env('ADMIM_URL') . '/bookings?status=success');
                $gr->setFailedRedirectUrl(env('ADMIM_URL') . '/bookings?status=failed');
                $gr->setCancelRedirectUrl(env('ADMIM_URL') . '/bookings?status=cancel');
            } else if ($redirectTo == 'app') {
                $gr->setSuccessRedirectUrl(route('api.payrexx.finish', ['status' => 'success']));
                $gr->setFailedRedirectUrl(route('api.payrexx.finish', ['status' => 'failed']));
                $gr->setCancelRedirectUrl(route('api.payrexx.finish', ['status' => 'cancel']));
            } else if ($redirectTo != null) {
                $gr->setSuccessRedirectUrl($redirectTo . '?status=success');
                $gr->setFailedRedirectUrl($redirectTo . '?status=failed');
                $gr->setCancelRedirectUrl($redirectTo . '?status=cancel');
            }

            if($bookingData->source == 'web') {
                $gr->setValidity(15);
            }


            // Launch it
            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );

            $gateway = $payrexx->create($gr);

            if ($gateway) {
                $link = $gateway->getLink();
            }

        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::error('PayrexxHelpers createGatewayLink Booking ID=' . $bookingData->id);
            Log::error($e->getMessage());
            Log::error('Error line:'. $e->getLine());
            Log::error('Error file:'. $e->getFile());
            $link = '';
        }

        return $link;
    }

    public static function createGatewayLink($schoolData, $bookingData,
                                             $basketData, Client $buyerUser = null, $redirectTo = null)
    {
        $link = '';

        try {
            // Check that School has Payrexx credentials
            //dd($schoolData->getPayrexxInstance());
           if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                throw new \Exception('No credentials for School ID=' . $schoolData->id);
            }

            // Prepare gateway: basic data
            $gr = new GatewayRequest();
            $gr->setReferenceId($bookingData->getOrGeneratePayrexxReference());
            $gr->setAmount($bookingData->price_total * 100);
            $gr->setCurrency($bookingData->currency);
            $gr->setVatRate($schoolData->bookings_comission_cash);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???

            // Add School's legal terms, if set
            if ($schoolData->conditions_url) {
                $gr->addField('terms', $schoolData->conditions_url);
            }

            // Product basket i.e. courses booked plus maybe cancellation insurance
            $basket = [];

            $basket[] = [
                'name' => [1 => $basketData['price_base']['name']],
                'quantity' => $basketData['price_base']['quantity'],
                'amount' => $basketData['price_base']['price'] * 100, // Convertir el precio a centavos
            ];

            // Agregar bonos al "basket"
            if (isset($basketData['bonus']['bonuses']) && count($basketData['bonus']['bonuses']) > 0) {
                foreach ($basketData['bonus']['bonuses'] as $bonus) {
                    $basket[] = [
                        'name' => [1 => $bonus['name']],
                        'quantity' => $bonus['quantity'],
                        'amount' => $bonus['price'] * 100, // Convertir el precio a centavos
                    ];
                }
            }

            // Agregar el campo "reduction" al "basket"
            if (isset($basketData['reduction'])) {
                $basket[] = [
                    'name' => [1 => $basketData['reduction']['name']],
                    'quantity' => $basketData['reduction']['quantity'],
                    'amount' => $basketData['reduction']['price'] * 100, // Convertir el precio a centavos
                ];
            }

            // Agregar el campo "tva" al "basket"
            $basket[] = [
                'name' => [1 => $basketData['tva']['name']],
                'quantity' => $basketData['tva']['quantity'],
                'amount' => $basketData['tva']['price'] * 100, // Convertir el precio a centavos
            ];

            // Agregar "Boukii Care" al "basket"
            $basket[] = [
                'name' => [1 => $basketData['boukii_care']['name']],
                'quantity' => $basketData['boukii_care']['quantity'],
                'amount' => $basketData['boukii_care']['price'] * 100, // Convertir el precio a centavos
            ];

            // Agregar "Cancellation Insurance" al "basket"
            $basket[] = [
                'name' => [1 => $basketData['cancellation_insurance']['name']],
                'quantity' => $basketData['cancellation_insurance']['quantity'],
                'amount' => $basketData['cancellation_insurance']['price'] * 100, // Convertir el precio a centavos
            ];

            // Agregar extras al "basket"
            if (isset($basketData['extras']['extras']) && count($basketData['extras']['extras']) > 0) {
                foreach ($basketData['extras']['extras'] as $extra) {
                    $basket[] = [
                        'name' => [1 => $extra['name']],
                        'quantity' => $extra['quantity'],
                        'amount' => $extra['price'] * 100, // Convertir el precio a centavos
                    ];
                }
            }

            // Calcular el precio total del "basket"
            $totalAmount = $basketData['pending_amount'] * 100;

            $gr->setBasket($basket);
            $gr->setAmount($totalAmount);

            // Buyer data
            if ($buyerUser) {
                $gr->addField('forename', $buyerUser->first_name);
                $gr->addField('surname', $buyerUser->last_name);
                $gr->addField('phone', $buyerUser->phone);
                $gr->addField('email', $buyerUser->email);
                $gr->addField('street', $buyerUser->address);
                $gr->addField('postcode', $buyerUser->cp);

                $gr->addField('place', $buyerUser->province);
                $gr->addField('country',  $buyerUser->country);
            }

            // OK/error pages to redirect user after payment
            if ($redirectTo == 'panel') {
                $gr->setSuccessRedirectUrl(env('ADMIM_URL') . '/bookings?status=success');
                $gr->setFailedRedirectUrl(env('ADMIM_URL') . '/bookings?status=failed');
                $gr->setCancelRedirectUrl(env('ADMIM_URL') . '/bookings?status=cancel');
            } else if ($redirectTo == 'app') {
                $gr->setSuccessRedirectUrl(route('api.payrexx.finish', ['status' => 'success']));
                $gr->setFailedRedirectUrl(route('api.payrexx.finish', ['status' => 'failed']));
                $gr->setCancelRedirectUrl(route('api.payrexx.finish', ['status' => 'cancel']));
            } else if ($redirectTo != null) {
                $gr->setSuccessRedirectUrl($redirectTo . '?status=success');
                $gr->setFailedRedirectUrl($redirectTo . '?status=failed');
                $gr->setCancelRedirectUrl($redirectTo . '?status=cancel');
            }

            if($bookingData->source == 'web') {
                $gr->setValidity(15);
            }



            // Launch it
            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );

            $gateway = $payrexx->create($gr);

            if ($gateway) {
                $link = $gateway->getLink();
            }

        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::error('PayrexxHelpers createGatewayLink Booking ID=' . $bookingData->id);
            Log::error($e->getMessage());
            Log::error('Error:', $e->getTrace());
            $link = '';
        }

        return $link;
    }

    /**
     * Download from Payrexx the details of a Transaction.
     * @see https://developers.payrexx.com/reference/retrieve-a-transaction
     *
     * @param string $payrexxInstance
     * @param string $payrexxKey
     * @param int $transactionID
     * @return Payrexx\Models\Response\Transaction|null
     */
    public static function retrieveTransaction($payrexxInstance, $payrexxKey, $transactionID)
    {
        try {
            $tr = new TransactionRequest();
            $tr->setId($transactionID);

            $payrexx = new Payrexx(
                $payrexxInstance,
                $payrexxKey,
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );
            return $payrexx->getOne($tr);
        } catch (PayrexxException $pe) {
            // Altough not stated by API documentation (as of 2022-10),
            // if it was a wrong ID, getOne will throw an Exception "No Transaction found with id xxx".
            // Plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers retrieveTransaction ID=' . $transactionID);
            Log::channel('payrexx')->error($pe->getMessage());
            return null;
        }
    }

    /**
     * Prepare a Payrexx direct pay link.
     * @see https://developers.payrexx.com/reference/create-a-paylink
     *
     * @param School $schoolData i.e. who wants the money
     * @param Booking $bookingData i.e. the Booking ID this payment is for
     * @param User $buyerUser to get his payment & contact details
     *
     * @return string empty if something failed
     */
    public static function createPayLink($schoolData, $bookingData, $basketData, Client $buyerUser = null)
    {
        $link = '';

        try {
            // Check that School has Payrexx credentials
            if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                throw new \Exception('No credentials for School ID=' . $schoolData->id);
            }


            // Prepare invoice: basic data
            $ir = new InvoiceRequest();
            $ir->setReferenceId($bookingData->getOrGeneratePayrexxReference());
            //$ir->setAmount($bookingData->price_total * 100);
            $ir->setCurrency($bookingData->currency);
            $ir->setVatRate($schoolData->bookings_comission_cash);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???


            // Add School's legal terms, if set
            if ($schoolData->conditions_url) {
                $ir->addField('terms', $schoolData->conditions_url);
            }
            // Product data i.e. courses booked plus maybe cancellation insurance
            $ir->setTitle($schoolData->name);

            // Calcular el precio total del "basket"
    /*        $totalAmount = array_reduce($basketData->all(), function($carry, $item) {
                return $carry + $item['amount'];
            }, 0);*/

            $totalAmount = $basketData['pending_amount'] * 100;

            $paymentSummary = self::generatePaymentSummary($basketData->all());
            $ir->setAmount($totalAmount);
           // $ir->setDescription($basketData->all());
            $ir->setName($bookingData->getOrGeneratePayrexxReference());
          //  $ir->setPurpose($basketData->all());
            $ir->setTitle($paymentSummary['title']);
            $ir->setPurpose('Booking: #'.$bookingData->id);
            $ir->setDescription($paymentSummary['description']);
            // Add School's legal terms, if set
            // (InvoiceRequest DOES accept "terms" as a valid field)
            if ($schoolData->conditions_url) {
                $ir->addField('terms', true, $schoolData->conditions_url);
            }

            if ($buyerUser) {
                $ir->addField('forename', $buyerUser->first_name);
                $ir->addField('surname', $buyerUser->last_name);
                $ir->addField('phone', $buyerUser->phone);
                $ir->addField('email', $buyerUser->email);
                $ir->addField('street', $buyerUser->address);
                $ir->addField('postcode', $buyerUser->cp);

                $ir->addField('place', $buyerUser->province);
                $ir->addField('country',  $buyerUser->country);
            }

            // Launch it
            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );
           // dd($ir);
            $invoice = $payrexx->create($ir);
            //Log::channel('payrexx')->info('Info', $invoice);
            Log::channel('payrexx')->info($invoice->getLink());
            if ($invoice) {
                $link = $invoice->getLink();
            }
        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers createPayLink Booking ID=' . $bookingData->id);
            Log::channel('payrexx')->error($e->getMessage());
            Log::channel('payrexx')->error($e->getLine());

            $link = '';
        }

        return $link;
    }

    public static function generatePaymentSummary($basketData)
    {
        $title = "Payment";
        $descriptionLines = [];

        // Recorrer cada item en el array del basketData
        foreach ($basketData as $key => $item) {
            // Verificar si el elemento es un array y tiene las claves esperadas
            if (is_array($item) && isset($item['name'], $item['quantity'], $item['price'])) {
                $name = $item['name'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                // Formatear la línea de descripción
                $descriptionLines[] = "$name - Quantity: $quantity - Price: " . number_format($price, 2) . " CHF";
            }
        }

        // Generar el texto de la descripción
        $description = implode("\n", $descriptionLines);

        return [
            'title' => $title,
            'description' => $description,
        ];
    }


    /**
     * Tell Payrexx to refund some money from a Transaction.
     * @see https://developers.payrexx.com/reference/refund-a-transaction
     *
     * @param \App\Models\Booking $bookingData i.e. the product to be refunded
     * @param float $amountToRefund
     * @return boolean
     */
    public static function refundTransaction($bookingData, $amountToRefund)
    {
        try {
            // Check if the total booking price is greater than or equal to the amount to refund
            $totalBookingPrice = $bookingData->price_total;
            if ($totalBookingPrice < $amountToRefund) {
                throw new \Exception('Amount to refund exceeds total booking price');
            }

            // Check if any payment is greater than or equal to the amount to refund
            $paymentToUse = null;
            foreach ($bookingData->payments as $payment) {
                if ($payment->amount >= $amountToRefund
                    && $payment->payrexx_transaction != null
                    && $payment->status == 'paid') {

                    // Find other payments with the same payrexx_transaction
                    $relatedPayments = $bookingData->payments->filter(function ($relatedPayment) use ($payment) {
                        return $relatedPayment->payrexx_transaction == $payment->payrexx_transaction;
                    });

                    $refundAmount = $payment->amount;

                    // Check the status of related payments
                    foreach ($relatedPayments as $relatedPayment) {
                        if ($relatedPayment->status == 'refund') {
                            continue; // Skip payments that are fully refunded
                        } elseif ($relatedPayment->status == 'partial_refund') {
                            // Subtract the amount of partial refunds
                            $refundAmount -= $relatedPayment->amount;
                        }
                    }

                    if ($refundAmount >= $amountToRefund) {
                        $paymentToUse = $payment;
                        break;
                    }
                }
            }

            if (!$paymentToUse) {
                // If no single payment covers the refund amount, perform partial refunds
                $remainingAmountToRefund = $amountToRefund;
                foreach ($bookingData->payments as $payment) {
                    if ($payment->amount > 0 && $payment->payrexx_transaction != null) {
                        // Find other payments with the same payrexx_transaction
                        $relatedPayments = $bookingData->payments->filter(function ($relatedPayment) use ($payment) {
                            return $relatedPayment->payrexx_transaction == $payment->payrexx_transaction;
                        });

                        // Calculate the total refund amount for related payments
                        $totalRefundAmount = 0;
                        foreach ($relatedPayments as $relatedPayment) {
                            if ($relatedPayment->status == 'refund') {
                                continue; // Skip payments that are fully refunded
                            } elseif ($relatedPayment->status == 'partial_refund') {
                                // Add the amount of partial refunds
                                $totalRefundAmount += $relatedPayment->amount;
                            }
                        }

                        // Calculate the remaining refund amount for this payment
                        $refundAmount = min($payment->amount - $totalRefundAmount, $remainingAmountToRefund);

                        if ($refundAmount > 0) {
                            $refundSuccess = self::performRefund($payment, $refundAmount);
                            if ($refundSuccess) {
                                $remainingAmountToRefund -= $refundAmount;

                                if ($remainingAmountToRefund <= 0) {
                                    break;
                                }
                            }
                        }
                    }
                }
            } else {
                // Use the payment that covers the full refund amount
                $refundSuccess = self::performRefund($paymentToUse, $amountToRefund);
            }

            if ($refundSuccess) {
                return true;
            } else {
                throw new \Exception('Refund failed');
            }
        } catch (\Exception $e) {
            Log::channel('payrexx')->error('PayrexxHelpers refundTransaction Booking ID=' . $bookingData->id);
            Log::channel('payrexx')->error($e->getMessage());
            return false;
        }
    }

    private static function performRefund($payment, $refundAmount)
    {
        // Perform the actual refund using Payrexx
        $transactionData = $payment->getPayrexxTransaction();
        $transactionID = $transactionData['id'] ?? '';

        $tr = new TransactionRequest();
        $tr->setId($transactionID);
        $tr->setAmount($refundAmount * 100);

        $payrexx = new Payrexx(
            $payment->school->getPayrexxInstance(),
            $payment->school->getPayrexxKey(),
            '',
            env('PAYREXX_API_BASE_DOMAIN')
        );
        Log::channel('payrexx')->debug('PayrexxHelpers refundTransaction: ' . $transactionID);
        Log::channel('payrexx')->debug('PayrexxHelpers refund amount: ' . $refundAmount);
        $response = $payrexx->refund($tr);
        $newPayment = new Payment($payment->toArray());
        // Update payment notes based on whether it's a full or partial refund
        if ($response->getStatus() == TransactionResponse::REFUNDED) {
            $newPayment->status = 'refund';
            $newPayment->amount = $refundAmount;
            $newPayment->save();
        } elseif ($response->getStatus() == TransactionResponse::PARTIALLY_REFUNDED) {
            $newPayment->status = 'partial_refund';
            $newPayment->amount = $refundAmount;
            $newPayment->save();
        }



        return ($response->getStatus() == TransactionResponse::REFUNDED || $response->getStatus() == TransactionResponse::PARTIALLY_REFUNDED);
    }


    /**
     * Send an email with payment data: a Payrexx direct pay link both as text and as QR
     *
     * @param School $schoolData i.e. who wants the money
     * @param Booking2 $bookingData i.e. the Booking ID this payment is for
     * @param User $buyerUser to get his payment & contact details
     *
     * @return boolean telling if it was OK
     */
    public static function sendPayEmail($schoolData, $bookingData, $request, $buyerUser)
    {
        $sentOK = false;

        if ($buyerUser && $buyerUser->email) {
            try {
                // Check that School has Payrexx credentials
                if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                    throw new \Exception('No credentials for School ID=' . $schoolData->id);
                }


                // Create pay link
                $link = self::createPayLink($schoolData, $bookingData, $request, $buyerUser);
                if (strlen($link) < 1) {
                    throw new \Exception('Cant create Payrexx Direct Link for School ID=' . $schoolData->id);
                }

                // Send by email
                $bookingData = $bookingData->fresh();   // To retrieve its generated PayrexxReference
                \Mail::to($buyerUser->email)
                    ->send(new BookingPayMailer(
                        $schoolData,
                        $bookingData,
                        $buyerUser,
                        $link
                    ));
                $logData = [
                    'booking_id' => $bookingData->id,
                    'action' => 'send_pay_link',
                    'user_id' => $bookingData->user_id,
                    'description' => 'Booking pay link sent',
                ];

                BookingLog::create($logData);
                $sentOK = true;
            } catch (\Exception $e) {
                // Altought not stated by API documentation (as of 2022-10),
                // missing or wrong params will throw an Exception, plus other connection etc issues
                Log::channel('payrexx')->error('PayrexxHelpers sendPayEmail Booking ID=' . $bookingData->id);
                Log::channel('payrexx')->error($e->getTraceAsString());
            }
        }

        return $sentOK;
    }


    public static function createVoucherPayLink($schoolData, $voucherData, $buyerUser = null)
    {
        $link = '';

        try {
            // Check that School has Payrexx credentials
            if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                throw new \Exception('No credentials for School ID=' . $schoolData->id);
            }

            // Prepare gateway: basic data
            $gr = new GatewayRequest();
            $payrexx_reference = $voucherData->generatePayrexxReference();
            $gr->setReferenceId($payrexx_reference);
            $gr->setAmount($voucherData->quantity * 100);
            $gr->setCurrency('CHF');
            $gr->setVatRate(null);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???

            // Add School's legal terms, if set
            if ($schoolData->conditions_url) {
                $gr->addField('terms', $schoolData->conditions_url);
            }

            // Product basket i.e. courses booked plus maybe cancellation insurance
            $basket = [];
            $basket[] = [
                'name' => [1 => $payrexx_reference],
                'quantity' => 1,
                'amount' => $voucherData->quantity * 100
            ];
            $gr->setBasket($basket);

            // Buyer data
            if ($buyerUser) {
                $gr->addField('forename', $buyerUser->first_name);
                $gr->addField('surname', $buyerUser->last_name);
                $gr->addField('phone', $buyerUser->phone);
                $gr->addField('email', $buyerUser->email);
                $gr->addField('street', $buyerUser->address);
                $gr->addField('postcode', $buyerUser->cp);

                $province = $buyerUser->province_id ?: null;
                $gr->addField('place', $province ? $province->name : '');
                $gr->addField('country', $province ? $province->country_iso : '');
            }

            // OK/error pages to redirect user after payment
            $gr->setSuccessRedirectUrl(route('api.payrexx.finish', ['status' => 'success']));
            $gr->setFailedRedirectUrl(route('api.payrexx.finish', ['status' => 'failed']));
            $gr->setCancelRedirectUrl(route('api.payrexx.finish', ['status' => 'cancel']));

            // Launch it
            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );
            $gateway = $payrexx->create($gr);
            if ($gateway) {
                $link = $gateway->getLink();
            }
        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers createPayLink Voucher ID=' . $voucherData->id);
            Log::channel('payrexx')->error($e->getMessage());
            $link = '';
        }

        return $link;
    }
}
