<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return $user->whereHas('projects', fn($q) => $q->where('id', $projectId))->exists()
        || $user->hasRole(['admin','super_admin']);
});

Broadcast::channel('workspace.{workspaceId}', function ($user, $workspaceId) {
    return $user->workspaces()->where('workspace_id', $workspaceId)->exists();
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
