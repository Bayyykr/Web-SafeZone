<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PolsekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lokasi_id' => ['nullable', 'exists:locations,id'],
            'nama'      => ['required', 'string', 'max:255'],
            'alamat'    => ['nullable', 'string', 'max:255'],
            'telepon'   => ['nullable', 'string', 'max:30'],
        ];
    }
}
