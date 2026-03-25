<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreLeaveRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Workspace;
use App\Services\HRService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(private HRService $hrService) {}

    public function index(Workspace $workspace)
    {
        $leaves = LeaveRequest::whereHas('employee', fn($q) => $q->where('workspace_id', $workspace->id))
                              ->with(['employee.department'])
                              ->latest()->paginate(20);
        return view('hr.leaves.index', compact('workspace','leaves'));
    }

    public function store(StoreLeaveRequest $request, Workspace $workspace)
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        $start    = \Carbon\Carbon::parse($request->start_date);
        $end      = \Carbon\Carbon::parse($request->end_date);
        $days     = $start->diffInWeekdays($end) + 1;

        LeaveRequest::create([
            ...$request->validated(),
            'employee_id' => $employee->id,
            'total_days'  => $days,
        ]);

        return back()->with('success', 'Đơn xin nghỉ đã được gửi!');
    }

    public function approve(Workspace $workspace, LeaveRequest $leave)
    {
        $this->authorize('manage-hr');
        $this->hrService->approveLeave($leave, auth()->id());
        return back()->with('success', 'Đơn xin nghỉ đã được duyệt.');
    }

    public function reject(Request $request, Workspace $workspace, LeaveRequest $leave)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->hrService->rejectLeave($leave, $request->reason);
        return back()->with('success', 'Đơn xin nghỉ đã bị từ chối.');
    }
}
