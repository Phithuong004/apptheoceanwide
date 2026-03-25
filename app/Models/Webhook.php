<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'workspace_id','name','url','secret','events','is_active',
        'last_triggered_at','failure_count',
    ];
    protected $casts = [
        'events'             => 'array',
        'is_active'          => 'boolean',
        'last_triggered_at'  => 'datetime',
    ];
    public function workspace() { return $this->belongsTo(Workspace::class); }
}
