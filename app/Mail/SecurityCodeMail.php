<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecurityCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public string $code,
        public string $headline,
        public array $lines = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->headline.' - NampungYuk');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.security-code');
    }
}
