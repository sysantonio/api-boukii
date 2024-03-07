<?php

/**
 * Class BookingCreateMailer
 */

namespace App\Mail;

use App\Models\Mail;
use Illuminate\Mail\Mailable;

use App\Models\Language;
use Illuminate\Support\Facades\Log;

/**
 * When a new Booking is created, whatever the chosen payment method,
 * send buyer user the details.
 * @see \App\Http\Controllers\Admin\BookingController::createBooking()
 */
class BookingCreateMailer extends Mailable
{
    private $schoolData;
    private $bookingData;
    private $userData;
    private $paid;


    /**
     * Create a new message instance.
     *
     * @param \App\Models\School $schoolData Where it was bought
     * @param \App\Models\Booking $bookingData What
     * @param \App\Models\User $userData Who
     * @return void
     */
    public function __construct($schoolData, $bookingData, $userData, $paid)
    {
        $this->schoolData = $schoolData;
        $this->bookingData = $bookingData;
        $this->userData = $userData;
        $this->paid = $paid;
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
        $userLang = Language::find( $this->userData->language1_id );
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        $templateView = 'mails.bookingCreate';
        $footerView = 'mails.footer';

        $templateMail = Mail::where('type', 'booking_confirm')->where('school_id', $this->schoolData->id)
            ->where('lang', $userLocale)->first();

        $templateData = [
            'titleTemplate' => $templateMail ? $templateMail->title : '',
            'bodyTemplate' => $templateMail ? $templateMail->body: '',
            'userName' => trim($this->userData->first_name . ' ' . $this->userData->last_name),
            'schoolName' => $this->schoolData->name,
            'schoolLogo' => $this->schoolData->logo,
            'schoolEmail' => $this->schoolData->contact_email,
            'schoolConditionsURL' => $this->schoolData->conditions_url,
            'reference' => '#' . $this->bookingData->id,
            'bookingNotes' => $this->bookingData->notes,
            'courses' => $this->bookingData->parseBookedGroupedCourses(),
            'hasCancellationInsurance' => $this->bookingData->has_cancellation_insurance,
            'actionURL' => null,
            'footerView' => $footerView,
            'paid'=> $this->paid
        ];

        $subject = __('emails.bookingCreate.subject');
        \App::setLocale($oldLocale);

        return $this->to($this->userData->email)
            ->subject($subject)
            ->view($templateView)->with($templateData);
    }
}
