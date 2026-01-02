<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $technicianId = $this->route('id');

        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $technicianId,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'technician_type' => 'nullable|in:inside,outside',
            'employment_type' => 'nullable|in:part_time,full_time',
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered',
            'profile_image.image' => 'Profile image must be a valid image file',
            'profile_image.max' => 'Profile image must not exceed 2MB',
            'password.min' => 'Password must be at least 6 characters',
        ];
    }
}
