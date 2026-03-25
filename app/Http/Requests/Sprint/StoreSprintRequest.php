<?php
namespace App\Http\Requests\Sprint;

use Illuminate\Foundation\Http\FormRequest;

class StoreSprintRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['required','string','max:100'],
            'goal'       => ['nullable','string','max:1000'],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date','after:start_date'],
            'capacity'   => ['nullable','integer','min:0'],
        ];
    }
}
