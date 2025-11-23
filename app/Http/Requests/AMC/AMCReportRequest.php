<?php

namespace App\Http\Requests\AMC;

use Illuminate\Foundation\Http\FormRequest;

class AMCReportRequest extends FormRequest
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
            'branch_id' => 'nullable|exists:branches,id',
            'customer_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
            'contract_type' => 'nullable|string',
            'format' => 'nullable|in:excel,pdf,json',
        ];
    }
}
