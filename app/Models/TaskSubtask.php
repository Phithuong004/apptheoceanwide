<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TaskSubtask extends Model
{
    use HasFactory;

    protected $table = 'task_subtasks';

    protected $fillable = ['task_id', 'title', 'assignee_id', 'status', 'deadline', 'sort_order', 'completed_at', 'comments'];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('sort_order');
    }
}
