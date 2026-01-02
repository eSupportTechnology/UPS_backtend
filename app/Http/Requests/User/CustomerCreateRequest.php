<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CustomerCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'customer_type' => 'required|in:personal,company',

            // Branches for company customers (optional)
            'branches' => 'nullable|array',
            'branches.*.name' => 'string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_type.required' => 'Customer type is required',
            'customer_type.in' => 'Customer type must be either personal or company',
            'branches.*.name' => 'Each branch must have a valid name',
        ];
    }
}
