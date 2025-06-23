<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupervisorCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * قم بإنشاء نسخة جديدة من الرسالة.
     *
     * @param \App\Models\User $user
     * @param string $password
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * احصل على غلاف الرسالة.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ASPU HUB Supervisor Account is Ready!',
        );
    }

    /**
     * احصل على تعريف محتوى الرسالة.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supervisor-credentials',
        );
    }

    /**
     * احصل على المرفقات للرسالة.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
