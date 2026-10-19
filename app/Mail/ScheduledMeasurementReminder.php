<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledMeasurementReminder extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public array $alerts;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $alerts)
    {
        $this->user = $user;
        $this->alerts = $alerts;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $total = $this->alerts['total_actionable'] ?? 0;
        return new Envelope(
            subject: "📅 MedFlow: Tienes {$total} aviso(s) en tu Calendario de Tomas de Medición",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.scheduled_measurement_reminder',
        );
    }
}
