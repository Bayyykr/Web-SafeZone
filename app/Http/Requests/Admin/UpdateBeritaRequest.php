<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBeritaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lokasi_id' => ['nullable', 'exists:locations,id'],
            'judul' => ['required', 'string', 'max:255'],
            'isi_berita' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
