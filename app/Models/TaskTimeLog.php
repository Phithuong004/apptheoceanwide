<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTimeLog extends Model
{
    protected $fillable = ['task_id','user_id','hours','description','logged_date'];

    protected $casts = ['logged_date' => 'date'];

    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
}
