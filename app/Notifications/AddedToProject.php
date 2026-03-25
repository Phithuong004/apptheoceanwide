<?php
namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AddedToProject extends Notification
{
    use Queueable;

    public function __construct(public Project $project) {}

    public function via($notifiable): array
    {
        return ['database']; // Lưu vào DB, không gửi email
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'added_to_project',
            'project_id'  => $this->project->id,
            'project_name'=> $this->project->name,
            'project_slug'=> $this->project->slug,
            'workspace_slug' => $this->project->workspace->slug,
            'message'     => 'Bạn đã được thêm vào dự án "' . $this->project->name . '"',
        ];
    }
}
