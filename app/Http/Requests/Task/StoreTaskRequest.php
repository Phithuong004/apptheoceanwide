<?php
namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:500'],
            'description'      => ['nullable', 'string'],
            'status'           => ['nullable', 'in:backlog,todo,in_progress,in_review,done,blocked'],
            'priority'         => ['nullable', 'in:low,medium,high,urgent,critical'],
            'type'             => ['nullable', 'in:epic,story,task,bug,sub_task,feature,improvement'],
            'assignee_id'      => ['nullable', 'exists:users,id'],
            'sprint_id'        => ['nullable', 'exists:sprints,id'],
            'parent_id'        => ['nullable', 'exists:tasks,id'],
            'story_points'     => ['nullable', 'integer', 'min:0', 'max:100'],
            'estimated_hours'  => ['nullable', 'numeric', 'min:0'],
            'start_date'       => ['nullable', 'date'],
            'due_date'         => ['nullable', 'date'],
            'labels'           => ['nullable', 'array'],
            'labels.*'         => ['exists:task_labels,id'],
            'tags'             => ['nullable', 'array'],

            'initial_comment'  => ['nullable', 'string', 'max:2000'],
            'attachments'      => ['nullable', 'array'],
            'attachments.*'    => ['file', 'max:10240'], // max 10MB/file
        ];
    }
}
