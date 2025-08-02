<?php

namespace App\Http\Requests\AMC;

use Illuminate\Foundation\Http\FormRequest;

class GetAllContractsRequest extends FormRequest
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
            'branch_id' => ['sometimes', 'uuid'],
            'customer_id' => ['sometimes'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', 'in:purchase_date,warranty_end_date,contract_amount,is_active,created_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Cannot show more than 100 records per page.',
            'sort_by.in' => 'Invalid sort field.',
            'sort_direction.in' => 'Sort direction must be asc or desc.',
        ];
    }
}
