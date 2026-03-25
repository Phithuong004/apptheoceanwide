<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'workspace_id'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
