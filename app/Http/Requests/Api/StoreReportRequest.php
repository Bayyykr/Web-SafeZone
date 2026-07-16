<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "judul_laporan" => ["required", "string", "max:255"],
            "deskripsi" => ["required", "string"],
            "kategori_id" => ["required", "exists:categories,id"],
            "lokasi_id" => ["nullable", "exists:locations,id"],
            "latitude" => ["nullable", "numeric", "between:-90,90"],
            "longitude" => ["nullable", "numeric", "between:-180,180"],
            "foto_kejadian" => ["nullable", "image", "max:2048"],
        ];
    }
}
