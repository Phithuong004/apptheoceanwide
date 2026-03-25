<?php
namespace App\Http\Controllers\Task;

use App\Events\CommentPosted;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Workspace;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Workspace $workspace, $project, Task $task)
    {
        $request->validate([
            'content'       => 'required|string|max:5000',
            'parent_id'     => 'nullable|exists:task_comments,id',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $comment = $task->comments()->create([
            'user_id'   => auth()->id(),
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment-attachments', 'public');
                $comment->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        event(new CommentPosted($comment->load('user')));

        return response()->json([
            'comment' => $comment->load(['user', 'replies.user', 'attachments'])
        ]);
    }

    // Route: PUT /tasks/{task}/comments/{comment}
    // Thứ tự: workspace, project, task, comment — khớp với route
    public function update(Request $request, Workspace $workspace, $project, Task $task, TaskComment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate(['content' => 'required|string|max:5000']);

        $comment->update([
            'content'   => $request->content,
            'is_edited' => true,
        ]);

        return response()->json(['comment' => $comment->fresh()]);
    }

    // Route: DELETE /tasks/{task}/comments/{comment}
    // Thứ tự: workspace, project, task, comment — khớp với route
    public function destroy(Workspace $workspace, $project, Task $task, TaskComment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(['success' => true]);
    }
}
