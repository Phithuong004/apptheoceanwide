<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $fillable = [
        'project_id','name','goal','status',
        'start_date','end_date','velocity','capacity',
        'started_at','completed_at',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function project() { return $this->belongsTo(Project::class); }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function getCompletedPointsAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->sum('story_points') ?? 0;
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->tasks()->sum('story_points') ?? 0;
    }

    public function getCompletionRateAttribute(): int
    {
        $total = $this->total_points;
        if ($total === 0) return 0;
        return (int) round(($this->completed_points / $total) * 100);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) return 0;
        return max(0, now()->diffInDays($this->end_date, false));
    }
}
