<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class SuperAdminMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $messageContent;
    public $subjectLine;
    public $recipientName;

    public function __construct($messageContent, $subjectLine, $recipientName)
    {
        $this->messageContent = $messageContent;
        $this->subjectLine = $subjectLine;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine ?: 'Aviso de MedFlow',
            from: new Address(config('mail.from.address', 'no-reply@wiroos.com'), config('mail.from.name', 'MedFlow Administrativo'))
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.superadmin_message',
            with: [
                'body' => $this->messageContent,
                'name' => $this->recipientName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
