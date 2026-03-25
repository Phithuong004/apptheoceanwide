<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization xử lý bằng $this->authorize() trong controller
    }

    public function rules(): array
    {
        return [
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'priority'         => ['sometimes', 'required', 'in:low,medium,high,urgent,critical'],
            'type'             => ['sometimes', 'required', 'in:task,bug,story,feature,epic,improvement,sub_task'],
            'status'           => ['sometimes', 'in:backlog,todo,in_progress,in_review,done,blocked'],
            'due_date'         => ['nullable', 'date'],
            'start_date'       => ['nullable', 'date'],
            'story_points'     => ['nullable', 'integer', 'min:0', 'max:100'],
            'estimated_hours'  => ['nullable', 'numeric', 'min:0'],

            // ✅ single assignee (radio button)
            'assignee_id'      => ['nullable', 'exists:users,id'],

            // ✅ multi assignee (checkbox - nếu dùng)
            'assignees'        => ['nullable', 'array'],
            'assignees.*'      => ['exists:users,id'],

            // ✅ collaborators
            'new_collaborators'           => ['nullable', 'array'],
            'new_collaborators.*.user_id' => ['required', 'exists:users,id'],
            'new_collaborators.*.role'    => ['required', 'in:supporter,reviewer,observer'],
            'remove_collaborators'        => ['nullable', 'array'],
            'remove_collaborators.*'      => ['exists:users,id'],
            'collaborator_roles'          => ['nullable', 'array'],
            'collaborator_roles.*'        => ['in:supporter,reviewer,observer'],
        ];
    }


    public function messages(): array
    {
        return [
            'title.required'    => 'Tên task không được để trống.',
            'priority.in'       => 'Priority không hợp lệ.',
            'type.in'           => 'Type không hợp lệ.',
            'due_date.date'     => 'Deadline không đúng định dạng.',
            'start_date.date'   => 'Ngày bắt đầu không đúng định dạng.',
            'story_points.integer' => 'Story points phải là số nguyên.',
            'assignees.*.exists'   => 'Thành viên không tồn tại.',
        ];
    }
}
