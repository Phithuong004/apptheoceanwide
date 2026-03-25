<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'owner_id',
        'plan',
        'settings',
    ];

    protected $casts = ['settings' => 'array'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($workspace) {
            $workspace->slug = $workspace->slug ?? Str::slug($workspace->name);
        });
    }

    // ─── Relationships ───────────────────────────────────────
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // app/Models/Workspace.php
    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }


    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function invitations()
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getMemberRole(User $user): ?string
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->first()?->pivot->role;
    }
}
