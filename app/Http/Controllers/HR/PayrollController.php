<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Workspace;
use App\Services\HRService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index(Workspace $workspace, Request $request)
    {
        $month     = $request->input('month', now()->month);
        $year      = $request->input('year', now()->year);
        $payrolls  = Payroll::whereHas('employee', fn($q) => $q->where('workspace_id', $workspace->id))
                            ->with('employee.department')
                            ->where('year', $year)->where('month', $month)
                            ->get();
        $totalNet   = $payrolls->where('status','!=','draft')->sum('net_salary');

        return view('hr.payroll.index', compact('workspace','payrolls','month','year','totalNet'));
    }

    public function generate(Workspace $workspace, Request $request)
    {
        $request->validate(['year' => 'required|integer', 'month' => 'required|integer|min:1|max:12']);

        $employees = Employee::where('workspace_id', $workspace->id)
                             ->where('status', 'active')->get();
        $count = 0;
        foreach ($employees as $employee) {
            $this->hrService->generatePayroll($employee, $request->year, $request->month);
            $count++;
        }

        return back()->with('success', "Đã tính lương cho {$count} nhân viên tháng {$request->month}/{$request->year}!");
    }

    public function confirm(Workspace $workspace, Payroll $payroll)
    {
        $payroll->update(['status' => 'confirmed']);
        return back()->with('success', 'Phiếu lương đã được xác nhận.');
    }

    public function markPaid(Workspace $workspace, Payroll $payroll)
    {
        $payroll->update(['status' => 'paid', 'paid_date' => today()]);
        return back()->with('success', 'Đã đánh dấu đã thanh toán lương.');
    }
}
