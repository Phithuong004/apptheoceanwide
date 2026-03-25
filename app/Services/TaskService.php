<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepo
    ) {}

    public function create(array $data, User $reporter): Task
    {
        return DB::transaction(function () use ($data, $reporter) {
            // Tách ra trước khi tạo task
            $initialComment = $data['initial_comment'] ?? null;
            $attachments    = $data['attachments'] ?? [];
            unset($data['initial_comment'], $data['attachments']);

            $data['reporter_id']  = $reporter->id;
            $data['workspace_id'] = $reporter->workspaces()->first()->id;
            $data['position']     = $this->getNextPosition($data['project_id'], $data['status'] ?? 'backlog');

            $task = $this->taskRepo->create($data);

            // Auto-watch for reporter and assignee
            $watcherIds = [$reporter->id];
            if (!empty($data['assignee_id']) && $data['assignee_id'] !== $reporter->id) {
                $watcherIds[] = $data['assignee_id'];
            }
            $task->watchers()->syncWithoutDetaching($watcherIds);

            // Tạo comment ban đầu nếu có
            if ($initialComment) {
                $task->comments()->create([
                    'user_id' => $reporter->id,
                    'content' => $initialComment,
                ]);
            }

            // Lưu file đính kèm nếu có
            foreach ($attachments as $file) {
                $path = $file->store("attachments/tasks/{$task->id}", 'public');
                $task->attachments()->create([
                    'uploaded_by'   => $reporter->id,
                    'filename'      => $path,                          // tên file đã hash
                    'original_name' => $file->getClientOriginalName(), // tên gốc
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                    'disk'          => 'public',
                    'path'          => $path,
                ]);
            }


            event(new TaskCreated($task));

            if (!empty($data['assignee_id'])) {
                event(new TaskAssigned($task));
            }

            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        $oldAssignee = $task->assignee_id;

        // Xử lý collaborators
        $newCollabs        = $data['new_collaborators']   ?? [];
        $removeCollabs     = $data['remove_collaborators'] ?? [];
        $collabRoles       = $data['collaborator_roles']   ?? [];
        unset($data['new_collaborators'], $data['remove_collaborators'], $data['collaborator_roles']);

        $task = $this->taskRepo->update($task, $data);

        // Xóa collaborator bị remove
        if ($removeCollabs) {
            $task->collaborators()->whereIn('user_id', $removeCollabs)->delete();
        }

        // Cập nhật role collaborator cũ
        foreach ($collabRoles as $userId => $role) {
            $task->collaborators()->where('user_id', $userId)->update(['role' => $role]);
        }

        // Thêm collaborator mới
        foreach ($newCollabs as $collab) {
            $task->collaborators()->updateOrCreate(
                ['user_id' => $collab['user_id']],
                ['role'    => $collab['role']]
            );
        }

        event(new TaskUpdated($task));

        if (!empty($data['assignee_id']) && $data['assignee_id'] !== $oldAssignee) {
            event(new TaskAssigned($task->fresh()));
        }

        return $task;
    }


    public function moveTask(Task $task, string $status, int $position): void
    {
        $this->taskRepo->moveToStatus($task, $status, $position);
        broadcast(new TaskUpdated($task->fresh()))->toOthers();
    }

    public function logTime(Task $task, User $user, array $data): void
    {
        $task->timeLogs()->create([
            'user_id'     => $user->id,
            'hours'       => $data['hours'],
            'description' => $data['description'] ?? null,
            'logged_date' => $data['date'] ?? now()->toDateString(),
        ]);

        // Update actual_hours
        $total = $task->timeLogs()->sum('hours');
        $task->update(['actual_hours' => $total]);
    }

    public function addDependency(Task $task, int $dependsOnId, string $type): void
    {
        // Prevent circular dependency
        if ($this->wouldCreateCircle($task->id, $dependsOnId)) {
            throw new \InvalidArgumentException('Dependency would create circular reference.');
        }

        $task->dependencies()->create([
            'depends_on_id' => $dependsOnId,
            'type'          => $type,
        ]);
    }

    private function getNextPosition(int $projectId, string $status): int
    {
        return Task::where('project_id', $projectId)
            ->where('status', $status)
            ->max('position') + 1;
    }

    private function wouldCreateCircle(int $taskId, int $dependsOnId): bool
    {
        if ($taskId === $dependsOnId) return true;
        $dep = Task::find($dependsOnId);
        foreach ($dep?->dependencies ?? [] as $d) {
            if ($this->wouldCreateCircle($taskId, $d->depends_on_id)) return true;
        }
        return false;
    }
}
