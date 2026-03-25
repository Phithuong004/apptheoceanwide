<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',          // present, late, leave, absent
        'overtime_hours',
        'notes',
    ];

    protected $casts = [
        'date'           => 'date',
        'check_in'       => 'datetime:H:i',
        'check_out'      => 'datetime:H:i',
        'overtime_hours' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
