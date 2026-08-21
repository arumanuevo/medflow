<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MobileAccessInvite extends Mailable
{
    use Queueable, SerializesModels;

    public string $deepLink;
    public string $senderName;
    public string $groupName;

    public function __construct(string $deepLink, string $senderName, string $groupName = '')
    {
        $this->deepLink = $deepLink;
        $this->senderName = $senderName;
        $this->groupName = $groupName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MedFlow] Acceso de Inspector Móvil',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobile_access',
            with: [
                'deepLink' => $this->deepLink,
                'senderName' => $this->senderName,
                'groupName' => $this->groupName,
            ],
        );
    }
}
