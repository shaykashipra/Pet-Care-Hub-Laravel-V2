<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_name'    => ['required', 'string', 'max:255'],
            'animal_type' => ['required', 'string', 'in:dog,cat,bird,rabbit,other'],
            'age'         => ['required', 'string', 'max:50'],
            'gender'      => ['required', 'in:male,female'],
            'color'       => ['required', 'string', 'max:100'],
            'coat_length' => ['required', 'string', 'in:short,medium,long'],
            'description' => ['required', 'string', 'min:10'],
            'pet_image'   => ['nullable', 'image', 'max:2048'], // max 2MB
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
