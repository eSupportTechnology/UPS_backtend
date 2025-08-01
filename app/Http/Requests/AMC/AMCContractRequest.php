<?php

namespace App\Http\Requests\AMC;

use Illuminate\Foundation\Http\FormRequest;

class AMCContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:users,id',
            'contract_type' => 'required|string|max:100',
            'purchase_date' => 'required|date',
            'warranty_end_date' => 'nullable|date',
            'contract_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',

            'maintenances' => 'nullable|array',
            'maintenances.*.scheduled_date' => 'required|date',
            'maintenances.*.note' => 'nullable|string',
            'maintenances.*.status' => 'nullable|in:pending,completed,missed',
        ];
    }
}
