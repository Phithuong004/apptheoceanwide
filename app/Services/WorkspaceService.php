<?php
namespace App\Services;

use App\Mail\InviteTeamMemberMail;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Task;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function create(array $data, User $owner): Workspace
    {
        $workspace = Workspace::create([
            ...$data,
            'owner_id' => $owner->id,
            'slug'     => Str::slug($data['name']),
        ]);

        // Add owner as workspace member
        $workspace->members()->attach($owner->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        return $workspace;
    }

    public function inviteMember(Workspace $workspace, string $email, string $role): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::updateOrCreate(
            ['workspace_id' => $workspace->id, 'email' => $email],
            [
                'token'      => Str::random(64),
                'role'       => $role,
                'invited_by' => auth()->id(),
                'expires_at' => now()->addDays(7),
            ]
        );

        Mail::to($email)->queue(new InviteTeamMemberMail($invitation, $workspace));

        return $invitation;
    }

    public function acceptInvitation(WorkspaceInvitation $invitation, User $user): void
    {
        $invitation->workspace->members()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role, 'joined_at' => now()],
        ]);
        $invitation->update(['accepted_at' => now()]);
    }

    public function getStats(Workspace $workspace): array
    {
        return [
            'total_members'  => $workspace->members()->count(),
            'total_projects' => $workspace->projects()->count(),
            'active_tasks'   => Task::whereIn('project_id', $workspace->projects()->pluck('id'))
                                    ->whereIn('status', ['todo', 'in_progress'])->count(),
            'completed_tasks'=> Task::whereIn('project_id', $workspace->projects()->pluck('id'))
                                    ->where('status', 'done')->count(),
        ];
    }
}
