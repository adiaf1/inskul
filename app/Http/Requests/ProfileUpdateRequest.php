<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => User::normalizeUsername((string) $this->input('username')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:40', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan. Silakan gunakan username lain.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan underscore.',
        ];
    }
}
