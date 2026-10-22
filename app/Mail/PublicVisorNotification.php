<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Models\Sensor;

class PublicVisorNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sensor;
    public $publicUrl;
    public $messageBody;
    public $destEmail;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Sensor $sensor, $messageBody = null, ?string $destEmail = null)
    {
        $this->sensor = $sensor;
        $this->publicUrl = route('public.visor', ['token' => $sensor->public_token]);
        $this->messageBody = $messageBody;
        $this->destEmail = $destEmail;
        $this->unsubscribeUrl = route('notifications.unsubscribe', [
            'token' => $sensor->public_token,
            'email' => $destEmail,
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notificación de Consumos: ' . $this->sensor->name,
        );
    }

    /**
     * Get the message headers (List-Unsubscribe para máxima reputación anti-spam).
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<' . $this->unsubscribeUrl . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'X-Entity-Ref-ID' => (string)$this->sensor->id,
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.public_visor',
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
