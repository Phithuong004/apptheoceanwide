<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function __construct(private WorkspaceService $workspaceService) {}

    public function index()
    {
        $workspaces = Auth::user()->workspaces()
            ->with('owner')
            ->withCount('members')  // thêm dòng này
            ->get();

        return view('workspace.index', compact('workspaces'));
    }


    public function create()
    {
        return view('workspace.create');
    }

    public function store(StoreWorkspaceRequest $request)
    {
        $workspace = $this->workspaceService->create($request->validated(), Auth::user());
        session(['current_workspace_id' => $workspace->id]);
        return redirect()->route('dashboard', $workspace->slug)
            ->with('success', 'Workspace đã được tạo!');
    }

    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);
        $members  = $workspace->members()->with('employee')->get();
        $projects = $workspace->projects()->active()->with('members')->get();
        $stats    = $this->workspaceService->getStats($workspace);

        return view('workspace.show', compact('workspace', 'members', 'projects', 'stats'));
    }

    public function invite(Request $request, Workspace $workspace)
    {
        $this->authorize('manage', $workspace);
        $request->validate([
            'email' => 'required|email',
            'role'  => 'required|in:admin,manager,member,guest',
        ]);

        $this->workspaceService->inviteMember($workspace, $request->email, $request->role);

        return back()->with('success', "Đã gửi lời mời tới {$request->email}");
    }

    public function acceptInvitation(string $token)
    {
        $invitation = WorkspaceInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $this->workspaceService->acceptInvitation($invitation, Auth::user());

        return redirect()->route('dashboard', $invitation->workspace->slug)
            ->with('success', 'Bạn đã tham gia workspace!');
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('manage', $workspace);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:7',
        ]);

        $workspace->update($request->only('name', 'description', 'color'));

        return back()->with('success', 'Workspace đã được cập nhật!');
    }
}
