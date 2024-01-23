<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

use App\Models\Language;


class WelcomeToMailer extends Mailable
{
    private $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


    public function build()
    {
        // Apply that user's language - or default
        $defaultLocale = config('app.fallback_locale');
        $oldLocale = \App::getLocale();
        $userLang = Language::find( $this->user->language1_id );
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        $templateView = \View::exists('mails.welcomeTo');
        $footerView = \View::exists('mails.footer');

        $templateData = [
            'userName' => trim($this->user->first_name . ' ' . $this->user->last_name),
            'actionURL' => null,
            'footerView' => $footerView,

            //SCHOOL DATA - none
            'schoolName' => '',
            'schoolLogo' => '',
            'schoolEmail' => '',
            'schoolConditionsURL' => '',
        ];

        $subject = __('emails.welcomeTo.subject');
        \App::setLocale($oldLocale);

        return $this->to($this->user->email)
                    ->subject($subject)
                    ->view($templateView)->with($templateData);
    }
}
