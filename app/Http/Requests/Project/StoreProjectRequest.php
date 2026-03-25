<?php
namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string','max:2000'],
            'color'       => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'type'        => ['required','in:scrum,kanban,waterfall'],
            'visibility'  => ['required','in:private,team,public'],
            'start_date'  => ['nullable','date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:today'],
            'budget'      => ['nullable','numeric','min:0'],
            'currency'    => ['nullable','string','size:3'],
            'client_id'   => ['nullable','exists:clients,id'],
            'template_id' => ['nullable','exists:project_templates,id'],
        ];
    }
}
