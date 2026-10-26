<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $senderEmail,
        public string $senderName,
        public string $recipientName,
        public string $mailSubject,
        public string $mailBody,
        public ?string $senderRoleTitle = 'Kliniek Leiding'
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->senderEmail, $this->senderName),
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: $this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-custom',
            with: [
                'senderEmail' => $this->senderEmail,
                'senderName' => $this->senderName,
                'recipientName' => $this->recipientName,
                'mailSubject' => $this->mailSubject,
                'mailBody' => $this->mailBody,
                'senderRoleTitle' => $this->senderRoleTitle,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
