<?php
namespace App\Events;

use App\Models\Sprint;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SprintStarted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;
    public function __construct(public Sprint $sprint) {}
    public function broadcastOn(): array
    {
        return [new PrivateChannel('project.' . $this->sprint->project_id)];
    }
}
