<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InvitedToWorkspace; // 👈 Notification này
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function store(Request $request, Workspace $workspace)
    {
        $this->authorize('manage', $workspace);

        $data = $request->validate([
            'email' => 'required|email',
            'role'  => 'required|in:admin,manager,member,guest',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        // Thêm vào workspace_members
        $workspace->members()->syncWithoutDetaching([
            $user->id => [
                'role'      => $data['role'],
                'joined_at' => now(),
            ],
        ]);

        // 👉 GỬI THÔNG BÁO NGAY LẬP TỨC
        $user->notify(new \App\Notifications\InvitedToWorkspace($workspace));

        return back()->with('success', "Đã thêm {$user->name} vào workspace và gửi thông báo.");
    }

    public function destroy(Workspace $workspace, User $user)
    {
        $this->authorize('manage', $workspace);

        if ($workspace->owner_id == $user->id) {
            return back()->with('error', 'Không thể xoá chủ sở hữu workspace.');
        }

        $workspace->members()->detach($user->id);

        return back()->with('success', 'Đã xoá thành viên khỏi workspace.');
    }
}
