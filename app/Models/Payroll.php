<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id','created_by','year','month',
        'base_salary','allowances','overtime_pay','bonuses',
        'deductions','tax','insurance','gross_salary','net_salary',
        'work_days','leave_days','status','paid_date','notes','breakdown',
    ];

    protected $casts = [
        'paid_date' => 'date',
        'breakdown' => 'array',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }
}
