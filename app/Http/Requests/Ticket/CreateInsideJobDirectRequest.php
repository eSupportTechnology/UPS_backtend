<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class CreateInsideJobDirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ups_serial_number' => 'required|string|max:255',
            'ups_model' => 'required|string|max:255',
            'ups_brand' => 'required|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|string|exists:users,id',
            'customer_id' => 'nullable|string|exists:users,id',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'gramsewa_division' => 'nullable|string|max:255',
        ];
    }
}
