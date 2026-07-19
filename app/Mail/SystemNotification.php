<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $notifTitle,
        public string $notifBody,
        public ?string $notifUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notifTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-notification',
        );
    }
}
