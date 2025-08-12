<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class GetTicketsByAssignedToRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'in:created_at,status,priority,accepted_at,completed_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
