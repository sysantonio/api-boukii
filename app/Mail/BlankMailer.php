<?php

/**
 * Class BlankMailer
 */

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Send a generic email to a bunch of inboxes.
 * ex. @see \App\Http\Controllers\Admin\MailerController::sendToAllBookers()
 */
class BlankMailer extends Mailable
{
    private $subjectText;
    private $bodyText;
    private $toEmail;
    private $bccEmails;


    /**
     * Create a new message instance.
     *
     * @param string $subjectText
     * @param string $bodyText
     * @param string[] $toEmail
     * @param string[] $bccEmails
     * @return void
     */
    public function __construct($subjectText, $bodyText, $toEmail, $bccEmails, $schoolData)
    {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->toEmail = $toEmail;
        $this->bccEmails = $bccEmails;
        $this->schoolData = $schoolData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $templateView = 'mails.blank';
        $footerView = 'mails.footer';

        $templateData = [
            'bodyContent' => $this->bodyText,
            'actionURL' => null,
            'footerView' => $footerView,

			//SCHOOL DATA
            'schoolName' => $this->schoolData->name,
            'schoolLogo' => $this->schoolData->logo,
            'schoolEmail' => $this->schoolData->contact_email,
            'schoolConditionsURL' => $this->schoolData->conditions_url
        ];

        return $this->to($this->toEmail)
                    ->replyTo($this->toEmail)
                    ->bcc($this->bccEmails)
                    ->subject($this->subjectText)
                    ->view($templateView)->with($templateData);
    }
}
