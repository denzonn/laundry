<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(User::ROLE)],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'no_hp' => 'no HP'];
    }
}
