<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasActivityLog;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasActivityLog;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'phone',
        'timezone', 'locale', 'status', 'last_active_at',
        'two_factor_secret', 'two_factor_enabled', 'preferences',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'last_active_at'      => 'datetime',
        'two_factor_enabled'  => 'boolean',
        'preferences'         => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function ownedWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function reportedTasks()
    {
        return $this->hasMany(Task::class, 'reporter_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function isWorkspaceAdmin(Workspace $workspace): bool
    {
        return $this->workspaces()
                    ->where('workspace_id', $workspace->id)
                    ->wherePivotIn('role', ['owner', 'admin'])
                    ->exists();
    }
}
