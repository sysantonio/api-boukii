<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

use App\Models\Language;


class RecoverPassword extends Mailable
{
    private $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $url
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
        $userLang = Language::find( $this->user->language_id_1 );
        $userLocale = $userLang ? $userLang->code : $defaultLocale;
        \App::setLocale($userLocale);

        $templateView = \View::exists('mails.recoverPassword_' . $userLocale) ? 'mails.recoverPassword_' . $userLocale : 'mails.recoverPassword_' . $defaultLocale;
        $footerView = \View::exists('mails.footer_' . $userLocale) ? 'mails.footer_' . $userLocale : 'mails.footer_' . $defaultLocale;
        
        $templateData = [
            'userName' => trim($this->user->first_name . ' ' . $this->user->last_name),
            'actionURL' => env('APP_RESETPASSWORD_URL') . '/' . $this->user->recover_token,
            'footerView' => $footerView,

            //SCHOOL DATA - none
            'schoolName' => '',
            'schoolLogo' => '',
            'schoolEmail' => '',
            'schoolConditionsURL' => '',
        ];

        $subject = __('emails.recoverPassword.subject');
        \App::setLocale($oldLocale);

        return $this->to($this->user->email)
                    ->subject($subject)
                    ->view($templateView)->with($templateData);
    }
}
