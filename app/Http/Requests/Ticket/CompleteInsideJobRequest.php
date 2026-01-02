<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInsideJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid|exists:tickets,id',
            'repair_notes' => 'nullable|string',
            'actual_parts_used' => 'nullable|array',
        ];
    }
}
