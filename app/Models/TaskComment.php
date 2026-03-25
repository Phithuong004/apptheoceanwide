<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['task_id', 'user_id', 'parent_id', 'content', 'is_edited'];

    protected $casts = ['is_edited' => 'boolean'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parent()
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }
    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_id');
    }

    public function getMentionedUsersAttribute(): array
    {
        preg_match_all('/@(\w+)/', $this->content, $matches);
        return User::whereIn('name', $matches[1])->get()->toArray();
    }

    public function attachments()
    {
        return $this->hasMany(TaskCommentAttachment::class, 'task_comment_id');
    }
}
