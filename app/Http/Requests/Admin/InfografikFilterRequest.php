<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InfografikFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "bulan" => ["nullable", "string", "regex:/^\d{4}-\d{2}$/"],
            "tanggal_mulai" => ["nullable", "date"],
            "tanggal_selesai" => ["nullable", "date"],
        ];
    }
}
