<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class ConvertToInsideJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'outside_ticket_id' => 'required|uuid|exists:tickets,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'ups_serial_number' => 'nullable|string|max:255',
            'ups_model' => 'nullable|string|max:255',
            'ups_brand' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ];
    }
}
