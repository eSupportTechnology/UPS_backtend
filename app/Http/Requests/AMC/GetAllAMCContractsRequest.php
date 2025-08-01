<?php

namespace App\Http\Requests\AMC;

use Illuminate\Foundation\Http\FormRequest;

class GetAllAMCContractsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'contract_type' => ['sometimes', 'string', 'max:100'],
            'branch_id' => ['sometimes'],
            'customer_id' => ['sometimes'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', 'in:purchase_date,contract_type,is_active,created_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
