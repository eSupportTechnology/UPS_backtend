<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'customer_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:company_branches,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'address' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'gramsewa_division' => 'nullable|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'file|image|max:5120',
        ];
    }
}
