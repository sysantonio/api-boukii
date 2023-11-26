<?php

/**
 * Class BookingNoticePayMailer
 */

namespace App\Mail;

use Illuminate\Mail\Mailable;

use App\Models\Language;

class BookingNoticePayMailer extends Mailable
{
    private $schoolData;
    private $bookingData;
    private $userData;
    private $payLink;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\School $schoolData Where it was bought
     * @param \App\Models\Booking2 $bookingData What
     * @param \App\Models\User $userData Who
     * @param string $payLink How
     * @return void
     */
    public function __construct($schoolData, $bookingData, $userData, $payLink)
    {
        $this->schoolData = $schoolData;
        $this->bookingData = $bookingData;
        $this->userData = $userData;
        $this->payLink = $payLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Apply that user's language - or default
        $defaultLocale = config('app.fallback_locale');
        $oldLocale = \App::getLocale();
        $userLang = Language::find( $this->userData->language_id_1 );
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        $templateView = \View::exists('mails.bookingPayNotice_' . $userLocale) ? 'mails.bookingPayNotice_' . $userLocale : 'mails.bookingPayNotice_' . $defaultLocale;
        $footerView = \View::exists('mails.footer_' . $userLocale) ? 'mails.footer_' . $userLocale : 'mails.footer_' . $defaultLocale;

        $templateData = [
            'userName' => trim($this->userData->first_name . ' ' . $this->userData->last_name),
            'schoolName' => $this->schoolData->name,
            'schoolLogo' => $this->schoolData->logo,
            'schoolEmail' => $this->schoolData->contact_email,
            'schoolConditionsURL' => $this->schoolData->conditions_url,
            'reference' => $this->bookingData->payrexx_reference,
            'bookingNotes' => $this->bookingData->notes,
            // 'courses' => $this->bookingData->parseBookedCourses(),
            'hasCancellationInsurance' => $this->bookingData->has_cancellation_insurance,
            'amount' => number_format($this->bookingData->price_total, 2),
            'currency' => $this->bookingData->currency,
            'actionURL' => $this->payLink,
            'footerView' => $footerView
        ];

        $subject = __('emails.bookingNoticePay.subject');
        \App::setLocale($oldLocale);

        return $this->to($this->userData->email)
                    ->subject($subject)
                    ->view($templateView)->with($templateData);

    }
}