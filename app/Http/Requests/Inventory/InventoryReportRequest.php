<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryReportRequest extends FormRequest
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
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'search' => 'nullable|string',
            'low_stock' => 'nullable|boolean',
            'stock_threshold' => 'nullable|integer|min:0',
            'format' => 'nullable|in:excel,pdf,json',
        ];
    }
}
