<?php
namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bạn được giao task: {$this->task->title}")
            ->line("Task **{$this->task->title}** đã được giao cho bạn.")
            ->line("Project: {$this->task->project->name}")
            ->action('Xem Task', url("/projects/{$this->task->project->slug}/tasks/{$this->task->id}"))
            ->line('Vui lòng kiểm tra và xử lý sớm nhất có thể.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'task_assigned',
            'task_id'    => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'message'    => "Bạn được giao task: {$this->task->title}",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
