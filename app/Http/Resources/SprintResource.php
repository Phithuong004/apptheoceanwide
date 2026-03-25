<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SprintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'goal'             => $this->goal,
            'status'           => $this->status,
            'start_date'       => $this->start_date?->toDateString(),
            'end_date'         => $this->end_date?->toDateString(),
            'started_at'       => $this->started_at?->toIso8601String(),
            'completed_at'     => $this->completed_at?->toIso8601String(),
            'velocity'         => $this->velocity,
            'capacity'         => $this->capacity,
            'total_points'     => $this->total_points,
            'completed_points' => $this->completed_points,
            'completion_rate'  => $this->completion_rate,
            'days_remaining'   => $this->days_remaining,
            'tasks_count'      => $this->whenCounted('tasks'),
            'project_id'       => $this->project_id,
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
