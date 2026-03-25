<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HRService
{
    public function createEmployee(array $data, int $workspaceId): Employee
    {
        $data['workspace_id']   = $workspaceId;
        $data['employee_code']  = $data['employee_code'] ?? $this->generateCode($workspaceId);

        return Employee::create($data);
    }

    public function approveLeave(LeaveRequest $leave, int $approverId): void
    {
        $leave->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // Create attendance records for leave period
        $period = Carbon::parse($leave->start_date)->daysUntil($leave->end_date);
        foreach ($period as $date) {
            Attendance::updateOrCreate(
                ['employee_id' => $leave->employee_id, 'date' => $date->toDateString()],
                ['status' => 'leave', 'work_hours' => 0]
            );
        }
    }

    public function rejectLeave(LeaveRequest $leave, string $reason): void
    {
        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function checkIn(Employee $employee): Attendance
    {
        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => today()->toDateString()],
            ['check_in' => now()->toTimeString(), 'status' => 'present']
        );
    }

    public function checkOut(Employee $employee): Attendance
    {
        $attendance = Attendance::where('employee_id', $employee->id)
                                ->where('date', today()->toDateString())
                                ->firstOrFail();

        $checkIn    = Carbon::parse($attendance->check_in);
        $checkOut   = now();
        $workHours  = round($checkIn->diffInMinutes($checkOut) / 60, 2);
        $overtime   = max(0, $workHours - 8);

        $attendance->update([
            'check_out'      => $checkOut->toTimeString(),
            'work_hours'     => $workHours,
            'overtime_hours' => $overtime,
        ]);

        return $attendance;
    }

    public function generatePayroll(Employee $employee, int $year, int $month): Payroll
    {
        $workDays   = $this->countWorkDays($employee->id, $year, $month);
        $leaveDays  = $this->countLeaveDays($employee->id, $year, $month);
        $overtime   = $this->calculateOvertime($employee->id, $year, $month);

        $baseSalary   = $employee->base_salary;
        $dailyRate    = $baseSalary / 22; // 22 working days/month
        $earnedSalary = $dailyRate * $workDays;
        $overtimePay  = ($dailyRate / 8) * 1.5 * $overtime; // 1.5x overtime rate
        $gross        = $earnedSalary + $overtimePay;
        $insurance    = round($gross * 0.105, 2); // 10.5% BHXH+BHYT+BHTN
        $taxable      = $gross - $insurance - 11000000; // Personal deduction 11M
        $tax          = $taxable > 0 ? $this->calculateIncomeTax($taxable) : 0;
        $net          = $gross - $insurance - $tax;

        return Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'year' => $year, 'month' => $month],
            [
                'created_by'   => auth()->id(),
                'base_salary'  => $baseSalary,
                'overtime_pay' => $overtimePay,
                'gross_salary' => $gross,
                'insurance'    => $insurance,
                'tax'          => $tax,
                'net_salary'   => max(0, $net),
                'work_days'    => $workDays,
                'leave_days'   => $leaveDays,
                'breakdown'    => compact('dailyRate','earnedSalary','taxable'),
            ]
        );
    }

    private function calculateIncomeTax(float $taxableIncome): float
    {
        // Vietnam PIT brackets (monthly)
        $brackets = [
            [5000000,  0.05],
            [5000000,  0.10],
            [8000000,  0.15],
            [14000000, 0.20],
            [20000000, 0.25],
            [28000000, 0.30],
            [PHP_INT_MAX, 0.35],
        ];

        $tax       = 0;
        $remaining = $taxableIncome;

        foreach ($brackets as [$limit, $rate]) {
            if ($remaining <= 0) break;
            $taxable   = min($remaining, $limit);
            $tax      += $taxable * $rate;
            $remaining -= $taxable;
        }

        return round($tax, 2);
    }

    private function countWorkDays(int $employeeId, int $year, int $month): int
    {
        return Attendance::where('employee_id', $employeeId)
                         ->whereYear('date', $year)
                         ->whereMonth('date', $month)
                         ->whereIn('status', ['present','late'])
                         ->count();
    }

    private function countLeaveDays(int $employeeId, int $year, int $month): float
    {
        return Attendance::where('employee_id', $employeeId)
                         ->whereYear('date', $year)
                         ->whereMonth('date', $month)
                         ->where('status', 'leave')
                         ->count();
    }

    private function calculateOvertime(int $employeeId, int $year, int $month): float
    {
        return Attendance::where('employee_id', $employeeId)
                         ->whereYear('date', $year)
                         ->whereMonth('date', $month)
                         ->sum('overtime_hours');
    }

    private function generateCode(int $workspaceId): string
    {
        $count = Employee::where('workspace_id', $workspaceId)->count() + 1;
        return 'EMP-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
