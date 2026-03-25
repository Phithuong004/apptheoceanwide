<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentAttachment extends Model
{
    protected $fillable = ['task_comment_id', 'original_name', 'path', 'mime_type', 'size'];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }
}
