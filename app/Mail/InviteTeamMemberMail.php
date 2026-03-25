<?php
namespace App\Mail;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteTeamMemberMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkspaceInvitation $invitation,
        public Workspace           $workspace
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Lời mời tham gia workspace: {$this->workspace->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invite-member');
    }
}
