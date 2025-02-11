<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user; // User data
    public $code; // 4-digit code to include in the email

    /**
     * Create a new message instance.
     *
     * @param  mixed  $user
     * @param  string  $code
     * @return void
     */
    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Verify Your Email Address',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {

        return new Content(  // إرسال البريد الإلكتروني كنص عادي (plain text) مباشرة
            text: 'Hello ' . $this->user->first_name . ",\n\n"
                . "Thank you for registering. Please use the following code to verify your email address:\n\n"
                . "Verification Code: " . $this->code . "\n\n"
                . "If you did not create an account, no further action is required.\n\n"
                . "Regards,\n"
                . config('app.name')
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
