<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'company'     => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'website'     => 'nullable|url|regex:/^https?:\/\/.*/',  // ← sửa: chấp nhận http/https
            'address'     => 'nullable|string',
            'country'     => 'nullable|string|max:100',
            'status'      => 'required|in:active,inactive,prospect',
            'currency'    => 'required|string|max:3',
            'notes'       => 'nullable|string',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required'    => 'Tên khách hàng không được để trống.',
            'name.max'         => 'Tên khách hàng không quá 255 ký tự.',
            'email.email'      => 'Email không đúng định dạng.',
            'status.required'  => 'Vui lòng chọn trạng thái.',
            'status.in'        => 'Trạng thái không hợp lệ.',
            'avatar.image'     => 'File phải là hình ảnh.',
            'avatar.max'       => 'Ảnh không quá 2MB.',
        ];
    }
}
