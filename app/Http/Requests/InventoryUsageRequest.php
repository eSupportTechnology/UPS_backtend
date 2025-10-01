<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryUsageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
        public function rules(): array
    {
        return [
            'reference_id'   => 'required|uuid',
            'usage_type'     => 'required|in:maintenance,contract',
            'usages'         => 'required|array|min:1',
            'usages.*.inventory_id' => 'required|uuid|exists:shop_inventories,id',
            'usages.*.quantity'     => 'required|integer|min:1',
            'usage_date'     => 'required|date',
            'notes'          => 'nullable|string'
        ];
    }
}
