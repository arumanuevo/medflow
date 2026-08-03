<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaborationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $invitedUser;
    public $inviter;
    public $token;
    public $personalMessage;
    public $acceptUrl;
    public $isNewUser;

    public function __construct(User $invitedUser, User $inviter, $token, $personalMessage = null)
    {
        $this->invitedUser = $invitedUser;
        $this->inviter = $inviter;
        $this->token = $token;
        $this->personalMessage = $personalMessage;
        $this->acceptUrl = url('/invitacion/aceptar/' . $token);
        $this->isNewUser = $invitedUser->wasRecentlyCreated ?? false;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 ' . config('app.name') . ' - Invitación a colaborar',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaboration-invitation',
            with: [
                'invitedUser' => $this->invitedUser,
                'inviter' => $this->inviter,
                'token' => $this->token,
                'personalMessage' => $this->personalMessage,
                'acceptUrl' => $this->acceptUrl,
                'appName' => config('app.name'),
                'isNewUser' => $this->isNewUser,
            ]
        );
    }
}