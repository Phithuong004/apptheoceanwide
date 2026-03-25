<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'status'           => $this->status,
            'priority'         => $this->priority,
            'priority_color'   => $this->priority_color,
            'type'             => $this->type,
            'story_points'     => $this->story_points,
            'estimated_hours'  => $this->estimated_hours,
            'actual_hours'     => $this->actual_hours,
            'position'         => $this->position,
            'start_date'       => $this->start_date?->toDateString(),
            'due_date'         => $this->due_date?->toDateString(),
            'completed_at'     => $this->completed_at?->toIso8601String(),
            'is_overdue'       => $this->isOverdue(),
            'tags'             => $this->tags ?? [],
            'is_recurring'     => $this->is_recurring,
            'subtask_progress' => $this->subtask_progress,

            // Relations (when loaded)
            'assignee'    => $this->whenLoaded('assignee', fn() => [
                'id'         => $this->assignee->id,
                'name'       => $this->assignee->name,
                'avatar_url' => $this->assignee->avatar_url,
            ]),
            'reporter'    => $this->whenLoaded('reporter', fn() => [
                'id'   => $this->reporter->id,
                'name' => $this->reporter->name,
            ]),
            'labels'      => $this->whenLoaded('labels', fn() =>
                $this->labels->map(fn($l) => [
                    'id'    => $l->id,
                    'name'  => $l->name,
                    'color' => $l->color,
                ])
            ),
            'subtasks'    => $this->whenLoaded('subtasks', fn() =>
                TaskResource::collection($this->subtasks)
            ),
            'comments_count'    => $this->whenLoaded('comments', fn() => $this->comments->count()),
            'attachments_count' => $this->whenLoaded('attachments', fn() => $this->attachments->count()),
            'watchers_count'    => $this->whenLoaded('watchers', fn() => $this->watchers->count()),

            'project_id'   => $this->project_id,
            'sprint_id'    => $this->sprint_id,
            'parent_id'    => $this->parent_id,
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}
