<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKonfirmasiLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['dikonfirmasi', 'ditolak', 'selesai']),
            ],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
