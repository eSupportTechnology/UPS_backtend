<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class GetAllBranchesRequest extends FormRequest
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
            'country' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', 'in:name,branch_code,country,city,is_active,created_at'],
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
