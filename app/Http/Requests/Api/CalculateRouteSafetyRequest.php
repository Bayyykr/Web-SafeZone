<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CalculateRouteSafetyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "origin_latitude" => "required|numeric",
            "origin_longitude" => "required|numeric",
            "destination_latitude" => "required|numeric",
            "destination_longitude" => "required|numeric",
        ];
    }
}
