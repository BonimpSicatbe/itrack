<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'extensionname' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'teaching_started_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'firstname.required' => 'The first name field is required.',
            'firstname.string' => 'The first name must be a string.',
            'firstname.max' => 'The first name may not be greater than 255 characters.',
            'lastname.required' => 'The last name field is required.',
            'lastname.string' => 'The last name must be a string.',
            'lastname.max' => 'The last name may not be greater than 255 characters.',
            'middlename.string' => 'The middle name must be a string.',
            'middlename.max' => 'The middle name may not be greater than 255 characters.',
            'extensionname.string' => 'The suffix must be a string.',
            'extensionname.max' => 'The suffix may not be greater than 255 characters.',
            'position.string' => 'The position must be a string.',
            'position.max' => 'The position may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'email.lowercase' => 'The email must be lowercase.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'teaching_started_at.date' => 'The teaching start date must be a valid date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'teaching_started_at' => 'teaching started at',
        ];
    }
}