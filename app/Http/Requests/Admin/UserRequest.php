<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user   = $this->route('user');
        $userId = $user?->id;

        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'telepon'  => ['nullable', 'string', 'max:30'],
            'alamat'   => ['nullable', 'string', 'max:255'],
            'role'     => ['required', 'string', Rule::in(['super_admin', 'admin', 'user'])],
            'aktif'    => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}
