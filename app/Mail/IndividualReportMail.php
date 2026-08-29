<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class IndividualReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sensor;
    public $url;
    public $financialText;

    /**
     * Create a new message instance.
     */
    public function __construct($sensor, $url, $financialText = null)
    {
        $this->sensor = $sensor;
        $this->url = $url;
        $this->financialText = $financialText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informe Avanzado de Consumo - ' . ($this->sensor->name ?? 'MedFlow'),
            from: new Address(config('mail.from.address', 'no-reply@wiroos.com'), config('mail.from.name', 'MedFlow Reports'))
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.individual_report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
