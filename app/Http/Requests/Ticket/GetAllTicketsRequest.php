<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class GetAllTicketsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'string'],
            'assigned_to' => ['sometimes', 'uuid'],
            'sort_by' => ['sometimes', 'string', 'in:created_at,status,priority,accepted_at,completed_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
