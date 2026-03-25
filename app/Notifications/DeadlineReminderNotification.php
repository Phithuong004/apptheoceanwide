<?php
namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task, public int $daysLeft) {}

    public function via($notifiable): array { return ['mail','database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Task sắp đến hạn: {$this->task->title}")
            ->line("Task **{$this->task->title}** còn {$this->daysLeft} ngày nữa đến hạn.")
            ->action('Xem Task', url("/tasks/{$this->task->id}"));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'      => 'deadline_reminder',
            'task_id'   => $this->task->id,
            'days_left' => $this->daysLeft,
            'message'   => "Task '{$this->task->title}' còn {$this->daysLeft} ngày đến hạn",
        ];
    }
}
