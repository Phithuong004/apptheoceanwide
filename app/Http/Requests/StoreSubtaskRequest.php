<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubtaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'status' => ['nullable', Rule::in(['todo', 'in_progress', 'done'])],
        ];
    }
}
