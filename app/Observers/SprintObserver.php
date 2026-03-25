<?php
namespace App\Observers;

use App\Models\Sprint;

class SprintObserver
{
    public function creating(Sprint $sprint): void
    {
        if (empty($sprint->name)) {
            $count = Sprint::where('project_id', $sprint->project_id)->count() + 1;
            $sprint->name = "Sprint {$count}";
        }
    }

    public function created(Sprint $sprint): void
    {
        \App\Models\ActivityLog::create([
            'user_id'      => auth()->id(),
            'subject_type' => Sprint::class,
            'subject_id'   => $sprint->id,
            'project_id'   => $sprint->project_id,
            'action'       => 'created',
            'description'  => "Created sprint: {$sprint->name}",
        ]);
    }

    public function updating(Sprint $sprint): void
    {
        if ($sprint->isDirty('status')) {
            if ($sprint->status === 'active' && !$sprint->started_at) {
                $sprint->started_at = now();
            }
            if ($sprint->status === 'completed' && !$sprint->completed_at) {
                $sprint->completed_at = now();
            }
        }
    }

    public function updated(Sprint $sprint): void
    {
        if ($sprint->wasChanged('status')) {
            \App\Models\ActivityLog::create([
                'user_id'      => auth()->id(),
                'subject_type' => Sprint::class,
                'subject_id'   => $sprint->id,
                'project_id'   => $sprint->project_id,
                'action'       => 'status_changed',
                'description'  => "Sprint '{$sprint->name}' → {$sprint->status}",
            ]);
        }
    }

    public function deleting(Sprint $sprint): void
    {
        $sprint->tasks()->update([
            'sprint_id' => null,
            'status'    => 'backlog',
        ]);
    }
}
