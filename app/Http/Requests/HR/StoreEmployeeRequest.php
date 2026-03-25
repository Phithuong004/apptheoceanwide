<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:employees,email',
            'phone'          => 'nullable|string|max:20',
            'gender'         => 'nullable|in:male,female,other',
            'birth_date'     => 'nullable|date',
            'address'        => 'nullable|string',
            'id_card'        => 'nullable|string|max:20',
            'tax_code'       => 'nullable|string|max:20',
            'bank_account'   => 'nullable|string|max:50',
            'bank_name'      => 'nullable|string|max:100',
            'department_id'  => 'nullable|exists:departments,id',
            'position_id'    => 'nullable|exists:positions,id',
            'manager_id'     => 'nullable|exists:employees,id',
            'base_salary'    => 'nullable|numeric|min:0',
            'salary_type'    => 'nullable|in:monthly,hourly,contract',
            'hired_date'     => 'required|date',
            'probation_end'  => 'nullable|date',
            'status'         => 'nullable|in:active,probation,inactive,terminated',
            'skills'         => 'nullable|string',
            'notes'          => 'nullable|string',
            'avatar'         => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'hired_date.required' => 'Vui lòng nhập ngày vào làm.',
        ];
    }
}
