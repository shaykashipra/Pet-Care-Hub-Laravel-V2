<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fname'    => ['required', 'string', 'min:2', 'max:100'],
            'lname'    => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:11'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'        => 'This email address is already registered.',
            'password.confirmed'  => 'Password confirmation does not match.',
        ];
    }

    /**
     * Return JSON error response instead of redirect on validation failure.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
