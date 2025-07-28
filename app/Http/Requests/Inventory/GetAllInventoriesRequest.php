<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class GetAllInventoriesRequest extends FormRequest
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
            'category' => ['sometimes', 'string', 'max:255'],
            'brand' => ['sometimes', 'string', 'max:255'],
            'created_by' => ['sometimes', 'string', 'max:255'],
            'min_quantity' => ['sometimes', 'integer', 'min:0'],
            'max_quantity' => ['sometimes', 'integer', 'min:0'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'purchase_date_from' => ['sometimes', 'date'],
            'purchase_date_to' => ['sometimes', 'date', 'after_or_equal:purchase_date_from'],
            'sort_by' => ['sometimes', 'string', 'in:id,product_name,brand,category,quantity,unit_price,purchase_date,created_at'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Cannot show more than 100 records per page.',
            'min_quantity.min' => 'Minimum quantity must be 0 or greater.',
            'max_quantity.min' => 'Maximum quantity must be 0 or greater.',
            'min_price.min' => 'Minimum price must be 0 or greater.',
            'max_price.min' => 'Maximum price must be 0 or greater.',
            'purchase_date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'sort_by.in' => 'Invalid sort field.',
            'sort_direction.in' => 'Sort direction must be asc or desc.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $numericFields = ['min_quantity', 'max_quantity', 'min_price', 'max_price'];
        $data = [];

        foreach ($numericFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $data[$field] = null;
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
