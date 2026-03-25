<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'status'            => 'nullable|string',
            'type'              => 'nullable|string',
            'visibility'        => 'nullable|string',
            'color'             => 'nullable|string|max:7',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date',
            'budget'            => 'nullable|numeric|min:0',
            'currency'          => 'nullable|string|max:3',
            'member_roles'      => 'nullable|array',
            'member_roles.*'    => 'string',
            'remove_members'    => 'nullable|array',
            'remove_members.*'  => 'integer',
            'new_members'       => 'nullable|array',
            'new_members.*.email' => 'email|exists:users,email',
            'new_members.*.role'  => 'string',
        ];
    }
}
