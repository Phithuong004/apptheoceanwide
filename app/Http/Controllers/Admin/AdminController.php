<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin|admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_users'      => User::count(),
            'active_users'     => User::where('status','active')->count(),
            'total_workspaces' => Workspace::count(),
            'recent_logins'    => AuditLog::where('event','login')
                                          ->with('user')
                                          ->latest()->take(10)->get(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function users(Request $request)
    {
        $users = User::with('roles')
                     ->when($request->search, fn($q, $s) => $q->where('name','like',"%$s%")
                                                               ->orWhere('email','like',"%$s%"))
                     ->latest()->paginate(30);
        return view('admin.users', compact('users'));
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);
        return view('admin.audit-logs', compact('logs'));
    }
}
