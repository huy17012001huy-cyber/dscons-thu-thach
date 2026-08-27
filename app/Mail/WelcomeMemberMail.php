<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $loginUrl,
        public string $brandName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Chào mừng bạn tới '.$this->brandName);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome-member');
    }
}
