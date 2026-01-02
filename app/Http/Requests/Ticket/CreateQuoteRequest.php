<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid|exists:tickets,id',
            'quoted_by' => 'required|uuid|exists:users,id',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_type' => 'required|in:part,labor,other',
            'line_items.*.inventory_id' => 'nullable|uuid|exists:shop_inventories,id',
            'line_items.*.description' => 'required|string|max:500',
            'line_items.*.quantity' => 'required|integer|min:1',
            'line_items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
