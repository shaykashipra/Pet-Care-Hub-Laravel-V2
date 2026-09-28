<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'pet_name'  => ['nullable', 'string', 'max:255'],
            'contact'   => ['required', 'string', 'max:20'],
            'date'      => ['required', 'date', 'after_or_equal:today'],
            'time'      => ['required', 'date_format:H:i'],
            'symptoms'  => ['required', 'string', 'min:5'],
            'category'  => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'Appointment date must be today or a future date.',
            'doctor_id.exists'    => 'Selected doctor does not exist.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
