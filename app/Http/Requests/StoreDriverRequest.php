<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin should be able to reach this endpoint based on routes/middleware, 
        // but we return true here since authorization is typically handled by middleware or gates.
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255|min:3',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20|regex:/^\+?[0-9\s\-\(\)]{7,20}$/',
            'password' => 'required|string|min:8|confirmed|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number format is invalid. Example: +94 71 123 4567',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'email.unique' => 'A driver with this email already exists.',
        ];
    }
}
