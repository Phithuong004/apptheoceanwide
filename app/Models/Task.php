<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;
use App\Traits\HasCustomFields;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use HasFactory, SoftDeletes, Searchable, HasActivityLog, HasCustomFields;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'sprint_id',
        'parent_id',
        'assignee_id',
        'reporter_id',
        'title',
        'description',
        'status',
        'priority',
        'type',
        'story_points',
        'estimated_hours',
        'actual_hours',
        'position',
        'start_date',
        'due_date',
        'completed_at',
        'is_recurring',
        'recurrence_pattern',
        'custom_fields',
        'tags',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'due_date'           => 'date',
        'completed_at'       => 'datetime',
        'is_recurring'       => 'boolean',
        'recurrence_pattern' => 'array',
        'custom_fields'      => 'array',
        'tags'               => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }
    public function comments()
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_id');
    }
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }
    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }
    public function watchers()
    {
        return $this->belongsToMany(User::class, 'task_watchers');
    }

    public function labels()
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_pivot');
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class);
    }

    public function blockedBy()
    {
        return $this->hasMany(TaskDependency::class, 'task_id')
            ->where('type', 'is_blocked_by');
    }


    // ─── Scopes ──────────────────────────────────────────────
    public function scopeForStatus($q, string $status)
    {
        return $q->where('status', $status);
    }
    public function scopeOverdue($q)
    {
        return $q->where('due_date', '<', now())->whereNotIn('status', ['done']);
    }
    public function scopeAssignedTo($q, int $userId)
    {
        return $q->where('assignee_id', $userId);
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function getSubtaskProgressAttribute(): int
    {
        $total = $this->subtasks()->count();
        if ($total === 0) return 0;
        $done = $this->subtasks()->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'red',
            'urgent'   => 'orange',
            'high'     => 'yellow',
            'medium'   => 'blue',
            'low'      => 'gray',
            default    => 'gray',
        };
    }

    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
        ];
    }

    // Thêm vào app/Models/Task.php

    public function collaborators()
    {
        return $this->hasMany(TaskCollaborator::class);
    }

    public function collaboratorUsers()
    {
        return $this->belongsToMany(User::class, 'task_collaborators')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function subtasks()
    {
        return $this->hasMany(TaskSubtask::class)->orderBy('sort_order');
    }

    public function taskSubtasks()
    {
        return $this->hasMany(TaskSubtask::class);  // Hoặc tên model đúng
    }
}
