<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        'user_id',
        'nearest_polsek_id',
        'kode_darurat',
        'status',
        'latitude',
        'longitude',
        'alamat_terdeteksi',
        'jarak_polsek_km',
        'waktu_sos',
        'waktu_dispatch',
        'waktu_selesai',
        'petugas_penanganan',
        'catatan_petugas',
        'telemetri',
    ]),
]
class EmergencyReport extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'jarak_polsek_km' => 'float',
            'waktu_sos' => 'datetime',
            'waktu_dispatch' => 'datetime',
            'waktu_selesai' => 'datetime',
            'telemetri' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nearestPolsek(): BelongsTo
    {
        return $this->belongsTo(Polsek::class, 'nearest_polsek_id');
    }

    public function getResponseTimeMinutesAttribute(): ?int
    {
        if (! $this->waktu_sos || ! $this->waktu_dispatch) {
            return null;
        }

        return (int) $this->waktu_sos->diffInMinutes($this->waktu_dispatch);
    }

    public function scopeFilterEmergency($query, array $filters)
    {
        return $query->with(["user", "nearestPolsek.lokasi"])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where("kode_darurat", "like", "%{$search}%")
                        ->orWhere("alamat_terdeteksi", "like", "%{$search}%")
                        ->orWhereHas("user", function ($query) use ($search) {
                            $query->where("name", "like", "%{$search}%")
                                ->orWhere("telepon", "like", "%{$search}%");
                        })
                        ->orWhereHas("nearestPolsek", function ($query) use ($search) {
                            $query->where("nama", "like", "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where("status", $status);
            });
    }
}
