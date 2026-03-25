<?php
namespace App\Events;

use App\Models\Sprint;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SprintCompleted
{
    use Dispatchable, SerializesModels;
    public function __construct(public Sprint $sprint) {}
}
