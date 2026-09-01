<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotoExpirationWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $count;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $count)
    {
        $this->user = $user;
        $this->count = $count;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Aviso de Vencimiento de Fotos (' . $this->count . ' por borrar)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.photo_expiration',
            with: [
                'user' => $this->user,
                'count' => $this->count
            ]
        );
    }
}
