<?php
namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Task $task) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('project.' . $this->task->project_id)];
    }

    public function broadcastWith(): array
    {
        return ['task' => $this->task->load(['assignee','labels'])->toArray()];
    }
}
