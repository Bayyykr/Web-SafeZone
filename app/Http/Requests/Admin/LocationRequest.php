<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'polygon_geojson' => ['nullable', 'json'],
            'status_kerawanan' => [
                'required',
                Rule::in(['Aman', 'Rawan', 'Sangat Rawan']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'polygon_geojson.json' => 'Polygon GeoJSON harus berupa format JSON yang valid.',
        ];
    }

    protected function passedValidation(): void
    {
        $geojson = $this->input('polygon_geojson');
        $normalized = $this->normalizeGeojson($geojson);

        $this->merge([
            'polygon_geojson' => $normalized,
        ]);
    }

    private function normalizeGeojson(?string $geojson): ?string
    {
        if ($geojson === null || trim($geojson) === '') {
            return null;
        }

        $decoded = json_decode($geojson, true);

        if (!is_array($decoded) || !$this->isSupportedGeojson($decoded)) {
            throw ValidationException::withMessages([
                'polygon_geojson' => 'Polygon GeoJSON harus berisi objek GeoJSON valid dengan type Polygon, MultiPolygon, Feature, atau FeatureCollection.',
            ]);
        }

        return json_encode(
            $decoded,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function isSupportedGeojson(array $geojson): bool
    {
        $type = $geojson['type'] ?? null;

        if ($type === 'Polygon' || $type === 'MultiPolygon') {
            return !empty($geojson['coordinates']) && is_array($geojson['coordinates']);
        }

        if ($type === 'Feature') {
            return isset($geojson['geometry']) &&
                is_array($geojson['geometry']) &&
                $this->isSupportedGeojson($geojson['geometry']);
        }

        if ($type === 'FeatureCollection') {
            if (empty($geojson['features']) || !is_array($geojson['features'])) {
                return false;
            }

            foreach ($geojson['features'] as $feature) {
                if (!is_array($feature) || !$this->isSupportedGeojson($feature)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
