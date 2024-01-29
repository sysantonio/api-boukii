<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Course;
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
            $basket[] = [
                'name' => [1 => $basketData['reduction']['name']],
                'quantity' => $basketData['reduction']['quantity'],
                'amount' => $basketData['reduction']['price'] * 100, // Convertir el precio a centavos
            ];

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
            $totalAmount = $basketData['price_total'] * 100;

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

    public static function test2()
    {
        $link = '';

        try {

            // Prepare gateway: basic data
            $gr = new GatewayRequest();
            $gr->setReferenceId('Boukii #');
            $gr->setAmount(13000);
            $gr->setCurrency('CHF');
            $gr->setVatRate(null);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???


            // Product basket i.e. courses booked plus maybe cancellation insurance
            $basket = [];


            $basket[] = [
                'name' => [1 => 'TEST'],
                'quantity' => 1,
                'amount' => 43.33 * 100
            ];
            $basket[] = [
                'name' => [1 => 'TEST'],
                'quantity' => 1,
                'amount' => 43.33 * 100
            ];
            $basket[] = [
                'name' => [1 => 'TEST'],
                'quantity' => 1,
                'amount' => 43.33 * 100
            ];

            // Suma los amounts del basket
            $basketTotal = array_reduce($basket, function ($carry, $item) {
                return $carry + $item['amount'];
            }, 0);


            if ($basketTotal !== 130 * 100) {
                // Calcula la diferencia
                $difference = (130 * 100) - $basketTotal;

                // Encuentra un elemento en el basket para ajustar
                $basket[0]['amount'] += $difference;
            }


            $gr->setBasket($basket);


            $gr->addField('forename', 'Antoine');
            $gr->addField('surname', 'GRiezman');
            $gr->addField('phone', '6000450505');
            $gr->addField('email', 'a@a.com');
            $gr->addField('street', 'plaza serralta');
            $gr->addField('postcode', '07013');

            // $province = $buyerUser->province_id ? Province::find($buyerUser->province_id) : null;
            $gr->addField('place', 'Baleares');
            $gr->addField('country', 'España');


            $gr->setSuccessRedirectUrl(env('APP_URL') . '/bookings?status=success');
            $gr->setFailedRedirectUrl(env('APP_URL') . '/bookings?status=failed');
            $gr->setCancelRedirectUrl(env('APP_URL') . '/bookings?status=cancel');


            // Launch it
            $payrexx = new Payrexx(
                'swissmountainsports',
                't5WxWJWqOvigxG8cOTSEBhdwW5rIgO',
                '',
                'pay.boukii.com'
            );
            $gateway = $payrexx->create($gr);
            if ($gateway) {
                $link = $gateway->getLink();
            }
        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers createGatewayLink Booking ID=' . 'TYEST');
            Log::channel('payrexx')->error($e->getTraceAsString());
            Log::channel('payrexx')->error($e->getMessage());
            $link = '';
        }

        return $link;
    }

    public static function test()
    {
        $mySchool = UserSchools::getAdminSchoolById(2);
        if (!$mySchool) {
            $mySchool = UserSchools::getAdminSchoolById(8);
        }
        $schoolData = $mySchool;
        $booking = $mySchool
            ? Bookings2::where('id', '=', intval(4851))->where('school_id', '=', $mySchool->id)->first()
            : null;
        $booking->paid = false;
        $booking->save();
        $bookingData = $booking;
        $buyerUser = $booking->main_user;

        $voucherAmount = 0;
        try {
            // Check that School has Payrexx credentials
            /*      if (!$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey())
                  {
                      throw new \Exception('No credentials for School ID=' . $schoolData->id);
                  }*/


            // Prepare gateway: basic data
            $gr = new GatewayRequest();
            $gr->setReferenceId($bookingData->getOrGeneratePayrexxReference());
            $gr->setAmount($bookingData->price_total * 100);
            $gr->setCurrency($bookingData->currency);
            $gr->setVatRate(null);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???

            // Add School's legal terms, if set
            if ($schoolData->conditions_url) {
                $gr->addField('terms', $schoolData->conditions_url);
            }

            // Product basket i.e. courses booked plus maybe cancellation insurance
            $basket = [];

            foreach ($bookingData->parseBookedCourses() as $c) {

                $basket[] = [
                    'name' => [1 => $c['name']],
                    'quantity' => count($c['users']),
                    'amount' => $c['unit_price'] * 100 * count($c['users'])
                ];
                /* if(count($c['users']) > 1) {
                     dd($c['unit_price'] );
                 }*/
            }


            if ($bookingData->has_cancellation_insurance) {
                // Apply buyer user's language - or default
                $defaultLocale = config('app.fallback_locale');
                $oldLocale = \App::getLocale();
                $userLang = $buyerUser ? Language::find($buyerUser->language1_id) : null;
                $userLocale = $userLang ? $userLang->code : $defaultLocale;
                \App::setLocale($userLocale);

                $basket[] = [
                    'name' => [1 => __('bookings.cancellationInsurance')],
                    'quantity' => 1,
                    'amount' => $bookingData->price_cancellation_insurance * 100
                ];

                \App::setLocale($oldLocale);
            }

            $basketTotal = array_reduce($basket, function ($carry, $item) {
                return $carry + $item['amount'];
            }, 0);

            // Compara con el total_price
            $difference = ($bookingData->price_total * 100) - $basketTotal;
            dd($basketTotal);
            // Compara con el total_price
            if ($basketTotal !== $bookingData->price_total * 100 && $difference <= 10 && $difference >= 1) {
                // Calcula la diferencia

                $basket[0]['amount'] += $difference;
            } else if ($difference != 0) {
                Log::channel('payrexx')->error('PayrexxHelpers createGatewayLink Booking ID=' . $bookingData->id);
                Log::channel('payrexx')->error('Price diff' . $difference);
                Log::channel('payrexx')->error('Basket', $basket);
                throw new \Exception('Problem with calculated payment for Booking ID=' . $bookingData->id);
            }

            $gr->setBasket($basket);


            // Buyer data
            if ($buyerUser) {
                $gr->addField('forename', $buyerUser->first_name);
                $gr->addField('surname', $buyerUser->last_name);
                $gr->addField('phone', $buyerUser->phone);
                $gr->addField('email', $buyerUser->email);
                $gr->addField('street', $buyerUser->address);
                $gr->addField('postcode', $buyerUser->cp);

                $province = $buyerUser->province_id ? Province::find($buyerUser->province_id) : null;
                $gr->addField('place', $province ? $province->name : '');
                $gr->addField('country', $province ? $province->country_iso : '');
            }


            $gr->setSuccessRedirectUrl('?status=success');
            $gr->setFailedRedirectUrl('?status=failed');
            $gr->setCancelRedirectUrl('?status=cancel');


            // Launch it
            $payrexx = new Payrexx(
                'swissmountainsports',
                't5WxWJWqOvigxG8cOTSEBhdwW5rIgO',
                '',
                'pay.boukii.com'
            );
            $gateway = $payrexx->create($gr);

            if ($gateway) {
                $link = $gateway->getLink();
            }
        } catch (\Exception $e) {
            // Altought not stated by API documentation (as of 2022-10),
            // missing or wrong params will throw an Exception, plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers createGatewayLink Booking ID=' . $bookingData->id);
            Log::channel('payrexx')->error($e->getMessage());
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
            // Check it was paid via a Payrexx Transaction
            $transactionData = $bookingData->getPayrexxTransaction();
            $transactionID = $transactionData['id'] ?? '';
            if (empty($transactionID)) {
                throw new \Exception('No payrexx_transaction for Booking ID=' . $bookingData->id);
            }

            // Check related School has Payrexx credentials
            $schoolData = $bookingData->school;
            if (!$schoolData || !$schoolData->getPayrexxInstance() || !$schoolData->getPayrexxKey()) {
                throw new \Exception('No credentials for School ID=' . $schoolData->id);
            }


            // Trigger refund
            $tr = new TransactionRequest();
            $tr->setId($transactionID);
            $tr->setAmount($amountToRefund * 100);

            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );
            $response = $payrexx->refund($tr);

            // Status will be "refunded" if the amount was the whole Booking price,
            // or "partially refunded" if just some of its BookingUsers
            $responseStatus = $response->getStatus() ?? '';

            return ($responseStatus == TransactionResponse::REFUNDED ||
                $responseStatus == TransactionResponse::PARTIALLY_REFUNDED);
        } catch (\Exception $e) {
            // Altough not stated by API documentation (as of 2022-10),
            // if it had no enough amount will throw an Exception "could not be refunded".
            // Plus other connection etc issues
            Log::channel('payrexx')->error('PayrexxHelpers refundTransaction Booking ID=' . $bookingData->id);
            Log::channel('payrexx')->error($e->getMessage());
            return false;
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
            $ir->setAmount($bookingData->price_total * 100);
            $ir->setCurrency($bookingData->currency);
            $ir->setVatRate($schoolData->bookings_comission_cash);                  // TODO TBD as of 2022-10 all Schools are at Switzerland and there's no VAT ???


            // Product data i.e. courses booked plus maybe cancellation insurance
            $ir->setTitle($schoolData->name);

            $basket = [];

            $basket[] = $basketData['price_base']['name'];

/*            // Agregar bonos al "basket"
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
            $basket[] = [
                'name' => [1 => $basketData['reduction']['name']],
                'quantity' => $basketData['reduction']['quantity'],
                'amount' => $basketData['reduction']['price'] * 100, // Convertir el precio a centavos
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
            }*/

            // Calcular el precio total del "basket"
            $totalAmount = $basketData['price_total'] * 100;

            //$ir->setAmount($totalAmount);
            $ir->setDescription(implode(', ', $basket));
            $ir->setName($bookingData->getOrGeneratePayrexxReference());
            $ir->setPurpose(implode(', ', $basket));

            // Add School's legal terms, if set
            // (InvoiceRequest DOES accept "terms" as a valid field)
            if ($schoolData->conditions_url) {
                $ir->addField('terms', true, $schoolData->conditions_url);
            }

            // Buyer data
            $ir->addField('forename', true, $buyerUser ? $buyerUser->first_name : '');
            $ir->addField('surname', true, $buyerUser ? $buyerUser->last_name : '');
            $ir->addField('phone', false, $buyerUser ? $buyerUser->phone : '');
            $ir->addField('email', true, $buyerUser ? $buyerUser->email : '');
            $ir->addField('street', false, $buyerUser ? $buyerUser->address : '');
            $ir->addField('postcode', false, $buyerUser ? $buyerUser->cp : '');
            $ir->addField('place', $buyerUser ? $buyerUser->province : '');
            $ir->addField('country', $buyerUser ? $buyerUser->country : '');


            // Launch it
            $payrexx = new Payrexx(
                $schoolData->getPayrexxInstance(),
                $schoolData->getPayrexxKey(),
                '',
                env('PAYREXX_API_BASE_DOMAIN')
            );
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
            $link = '';
        }

        return $link;
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
