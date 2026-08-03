<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WorkspaceCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaborationRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $collaborator;
    public $owner;
    public $workspaceName;

    public function __construct(WorkspaceCollaborator $collaborator, User $owner)
    {
        $this->collaborator = $collaborator;
        $this->owner = $owner;
        $this->workspaceName = $owner->name ?? 'el espacio de trabajo';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Acceso revocado - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaboration-revoked',
            with: [
                'collaborator' => $this->collaborator,
                'owner' => $this->owner,
                'workspaceName' => $this->workspaceName,
                'appName' => config('app.name'),
            ]
        );
    }
}