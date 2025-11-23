<?php

namespace App\Http\Requests\AMCMaintenance;

use Illuminate\Foundation\Http\FormRequest;

class GetMaintenancesByAssignedToRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'in:scheduled_date,completed_date,status,created_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,completed,cancelled'],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
