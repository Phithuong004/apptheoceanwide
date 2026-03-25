<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitedToWorkspace extends Notification
{
    use Queueable;

    public function __construct(public Workspace $workspace) {}

    public function via($notifiable): array
    {
        return ['database', 'mail']; // database + email
    }

    // app/Notifications/InvitedToWorkspace.php
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Mời tham gia workspace', // 👈 Đảm bảo luôn có key này
            'message' => "Bạn đã được mời tham gia **{$this->workspace->name}** với vai trò **{$this->workspace->getMemberRole($notifiable)}**.",
            'action' => route('workspace.show', $this->workspace->slug),
            'workspace_slug' => $this->workspace->slug,
            'workspace_name' => $this->workspace->name,
            'role' => $this->workspace->getMemberRole($notifiable),
        ];
    }



    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mời tham gia workspace ' . $this->workspace->name)
            ->line("Bạn đã được mời tham gia workspace **{$this->workspace->name}**.")
            ->line('Nhấp vào nút bên dưới để vào workspace.')
            ->action('Vào workspace', route('workspace.show', $this->workspace->slug));
    }
}
