<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'technician_type' => 'required|in:inside,outside',
            'employment_type' => 'required_if:technician_type,inside|nullable|in:part_time,full_time',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Technician name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'This email is already registered',
            'technician_type.required' => 'Technician type (inside/outside) is required',
            'employment_type.required_if' => 'Employment type is required for inside technicians',
            'profile_image.image' => 'Profile image must be a valid image file',
            'profile_image.max' => 'Profile image must not exceed 2MB',
        ];
    }
}
