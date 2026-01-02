<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class InspectInsideJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid|exists:tickets,id',
            'inspection_notes' => 'required|string',
            'inspected_by' => 'required|uuid|exists:users,id',
        ];
    }
}
