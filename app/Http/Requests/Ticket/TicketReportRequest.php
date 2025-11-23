<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,assigned,accepted,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'gramsewa_division' => 'nullable|string',
            'search' => 'nullable|string',
            'format' => 'nullable|in:excel,pdf,json',
        ];
    }
}
