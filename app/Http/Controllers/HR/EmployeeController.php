<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Workspace;
use App\Services\HRService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        private HRService $hrService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Danh sách nhân viên
    |--------------------------------------------------------------------------
    */

    public function index(Request $request, Workspace $workspace)
    {
        $query = Employee::where('workspace_id', $workspace->id)
            ->with(['department', 'position', 'user']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $departments = Department::where('workspace_id', $workspace->id)->get();

        return view('hr.employees.index', compact(
            'workspace',
            'employees',
            'departments'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Form tạo nhân viên
    |--------------------------------------------------------------------------
    */

    public function create(Workspace $workspace)
    {
        $departments = Department::where('workspace_id', $workspace->id)->get();

        $positions = Position::where('workspace_id', $workspace->id)->get();

        $managers = Employee::where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->get();

        return view('hr.employees.create', compact(
            'workspace',
            'departments',
            'positions',
            'managers'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Lưu nhân viên
    |--------------------------------------------------------------------------
    */

    public function store(StoreEmployeeRequest $request, Workspace $workspace)
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request
                ->file('avatar')
                ->store('avatars', 'public');
        }

        $this->hrService->createEmployee($data, $workspace->id);

        return redirect()
            ->route('hr.employees.index', $workspace->slug)
            ->with('success', 'Nhân viên đã được thêm!');
    }

    /*
    |--------------------------------------------------------------------------
    | Chi tiết nhân viên
    |--------------------------------------------------------------------------
    */

    public function show(Workspace $workspace, Employee $employee)
    {
        abort_if($employee->workspace_id !== $workspace->id, 404);

        $employee->load([
            'department',
            'position',
            'manager',
            'leaveRequests',
            'attendances',
            'payrolls'
        ]);

        $statsThisMonth = [
            'work_days' => $employee->attendances()
                ->whereMonth('date', now()->month)
                ->whereIn('status', ['present', 'late'])
                ->count(),

            'leave_days' => $employee->attendances()
                ->whereMonth('date', now()->month)
                ->where('status', 'leave')
                ->count(),

            'overtime' => $employee->attendances()
                ->whereMonth('date', now()->month)
                ->sum('overtime_hours'),
        ];

        return view('hr.employees.show', compact(
            'workspace',
            'employee',
            'statsThisMonth'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Form chỉnh sửa
    |--------------------------------------------------------------------------
    */

    public function edit(Workspace $workspace, Employee $employee)
    {
        abort_if($employee->workspace_id !== $workspace->id, 404);

        $departments = Department::where('workspace_id', $workspace->id)->get();

        $positions = Position::where('workspace_id', $workspace->id)->get();

        $managers = Employee::where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->where('id', '!=', $employee->id)
            ->get();

        return view('hr.employees.edit', compact(
            'workspace',
            'employee',
            'departments',
            'positions',
            'managers'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Cập nhật nhân viên
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Workspace $workspace, Employee $employee)
    {
        abort_if($employee->workspace_id !== $workspace->id, 404);

        $data = $request->validate([

            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',

            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',

            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',

            'hired_date' => 'nullable|date',
            'probation_end' => 'nullable|date',

            'base_salary' => 'nullable|numeric',
            'salary_type' => 'nullable|in:monthly,daily,hourly',

            'id_card' => 'nullable|string',
            'tax_code' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_name' => 'nullable|string',

            'skills' => 'nullable',
            'notes' => 'nullable|string',

            'avatar' => 'nullable|image|max:2048',

            'status' => 'required|in:active,probation,terminated',
        ]);

        if ($request->skills) {
            $data['skills'] = json_decode($request->skills, true);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request
                ->file('avatar')
                ->store('avatars', 'public');
        }

        $employee->update($data);

        return redirect()
            ->route('hr.employees.show', [$workspace->slug, $employee->id])
            ->with('success', 'Cập nhật nhân viên thành công!');
    }

    /*
    |--------------------------------------------------------------------------
    | Xoá nhân viên
    |--------------------------------------------------------------------------
    */

    public function destroy(Workspace $workspace, Employee $employee)
    {
        abort_if($employee->workspace_id !== $workspace->id, 404);

        $employee->update([
            'status' => 'terminated',
            'terminated_date' => today()
        ]);

        $employee->delete();

        return back()->with('success', 'Nhân viên đã được xoá.');
    }
}