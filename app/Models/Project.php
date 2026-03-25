<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Project extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'workspace_id',
        'client_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'status',
        'type',
        'visibility',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'settings',
    ];

    protected $casts = [
        'settings'   => 'array',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($p) => $p->slug = $p->slug ?? Str::slug($p->name));
    }

    // ─── Relationships ───────────────────────────────────────
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'joined_at')->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }
    public function labels()
    {
        return $this->hasMany(TaskLabel::class);
    }
    public function backlog()
    {
        return $this->hasMany(Backlog::class);
    }

    public function activeSprint()
    {
        return $this->hasOne(Sprint::class)->where('status', 'active');
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
    public function scopeForUser($q, User $user)
    {
        return $q->whereHas('members', fn($m) => $m->where('user_id', $user->id));
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->whereNull('parent_id')->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->whereNull('parent_id')->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }

    public function toSearchableArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'description' => $this->description];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
