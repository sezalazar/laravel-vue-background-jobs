<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackgroundJobFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $className,
        public string $methodName,
        public int $attempts,
        public string $errorMessage
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Background Job Failed'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.background_job_failed',
            with: [
                'className' => $this->className,
                'methodName' => $this->methodName,
                'attempts' => $this->attempts,
                'errorMessage' => $this->errorMessage,
            ]
        );
    }
}
