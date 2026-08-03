<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WorkspaceCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaborationRoleChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $collaborator;
    public $owner;
    public $workspaceName;
    public $oldRole;
    public $newRole;

    public function __construct(WorkspaceCollaborator $collaborator, User $owner, $oldRole, $newRole)
    {
        $this->collaborator = $collaborator;
        $this->owner = $owner;
        $this->workspaceName = $owner->name ?? 'el espacio de trabajo';
        $this->oldRole = $oldRole;
        $this->newRole = $newRole;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔄 Rol actualizado - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.collaboration-role-changed',
            with: [
                'collaborator' => $this->collaborator,
                'owner' => $this->owner,
                'workspaceName' => $this->workspaceName,
                'oldRole' => $this->oldRole,
                'newRole' => $this->newRole,
                'appName' => config('app.name'),
            ]
        );
    }
}