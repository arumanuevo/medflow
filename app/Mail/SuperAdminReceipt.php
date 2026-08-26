<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class SuperAdminReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $pdfContent;
    public $filename;

    public function __construct($recipientName, $pdfContent, $filename)
    {
        $this->recipientName = $recipientName;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso de Facturación / Boleta de Pago - MedFlow',
            from: new Address(config('mail.from.address', 'no-reply@wiroos.com'), config('mail.from.name', 'MedFlow Facturación'))
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.superadmin_receipt',
            with: [
                'name' => $this->recipientName,
            ]
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn() => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
