<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'workspace_id',
        'department_id',
        'min_salary',
        'max_salary',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
