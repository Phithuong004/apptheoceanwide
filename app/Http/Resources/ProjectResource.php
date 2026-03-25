<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'color'       => $this->color,
            'icon'        => $this->icon,
            'status'      => $this->status,
            'type'        => $this->type,
            'visibility'  => $this->visibility,
            'start_date'  => $this->start_date?->toDateString(),
            'end_date'    => $this->end_date?->toDateString(),
            'budget'      => $this->budget,
            'currency'    => $this->currency,
            'progress'    => $this->progress,

            'owner' => $this->whenLoaded('owner', fn() => [
                'id'         => $this->owner->id,
                'name'       => $this->owner->name,
                'avatar_url' => $this->owner->avatar_url,
            ]),
            'members_count'   => $this->whenLoaded('members', fn() => $this->members->count()),
            'active_sprint'   => $this->whenLoaded('activeSprint', fn() =>
                $this->activeSprint ? new SprintResource($this->activeSprint) : null
            ),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
