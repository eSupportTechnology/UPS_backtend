<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class ApproveQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid|exists:tickets,id',
            'approved' => 'required|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
