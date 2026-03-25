<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id','user_id','department_id','position_id','manager_id',
        'employee_code','full_name','email','phone','birth_date','gender',
        'address','avatar','id_card','tax_code','bank_account','bank_name',
        'base_salary','salary_type','hired_date','probation_end',
        'terminated_date','status','skills','notes',
    ];

    protected $casts = [
        'birth_date'      => 'date',
        'hired_date'      => 'date',
        'probation_end'   => 'date',
        'terminated_date' => 'date',
        'skills'          => 'array',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function position()    { return $this->belongsTo(Position::class); }
    public function manager()     { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function subordinates(){ return $this->hasMany(Employee::class, 'manager_id'); }
    public function leaveRequests(){ return $this->hasMany(LeaveRequest::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function payrolls()    { return $this->hasMany(Payroll::class); }

    public function getYearsOfServiceAttribute(): float
    {
        return round($this->hired_date->diffInMonths(now()) / 12, 1);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/'.$this->avatar)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=8b5cf6&color=fff';
    }

    public function getRemainingLeaveAttribute(): float
    {
        $annual = 12; // Default annual leave days
        $taken  = $this->leaveRequests()
                       ->where('type','annual')
                       ->where('status','approved')
                       ->whereYear('start_date', now()->year)
                       ->sum('total_days');
        return max(0, $annual - $taken);
    }
}
