<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    protected $fillable = ['workspace_id', 'name', 'description', 'is_public', 'settings'];

    protected $casts = [
        'is_public' => 'boolean',
        'settings'  => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
