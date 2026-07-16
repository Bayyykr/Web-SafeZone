<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[
    Fillable([
        "user_id",
        "kategori_id",
        "lokasi_id",
        "polsek_id",
        "judul_laporan",
        "deskripsi",
        "latitude",
        "longitude",
        "foto_kejadian",
        "status",
    ]),
]
class Laporan extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Category::class, "kategori_id");
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Location::class, "lokasi_id");
    }

    public function polsek(): BelongsTo
    {
        return $this->belongsTo(Polsek::class);
    }

    public function konfirmasi(): HasOne
    {
        return $this->hasOne("App\\Models\\KonfirmasiLaporan");
    }

    public function scopeFilterKonfirmasi($query, array $filters)
    {
        return $query->with([
            "user",
            "kategori",
            "lokasi",
            "polsek",
            "konfirmasi.petugas",
        ])
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where("judul_laporan", "like", "%{$search}%")
                    ->orWhere("deskripsi", "like", "%{$search}%")
                    ->orWhereHas("user", function ($query) use ($search) {
                        $query->where("name", "like", "%{$search}%")
                            ->orWhere("email", "like", "%{$search}%")
                            ->orWhere("telepon", "like", "%{$search}%");
                    })
                    ->orWhereHas("kategori", function ($query) use ($search) {
                        $query->where("nama_kategori", "like", "%{$search}%");
                    })
                    ->orWhereHas("lokasi", function ($query) use ($search) {
                        $query->where("nama_lokasi", "like", "%{$search}%");
                    });
            });
        })
        ->when($filters['status'] ?? null, function ($query, $status) {
            $query->where("status", $status);
        });
    }

    public function scopeFilterHistory($query, array $filters)
    {
        return $query->with([
            "user",
            "kategori",
            "lokasi",
            "polsek",
            "konfirmasi.petugas",
        ])
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where("judul_laporan", "like", "%{$search}%")
                    ->orWhere("deskripsi", "like", "%{$search}%")
                    ->orWhereHas("user", function ($query) use ($search) {
                        $query->where("name", "like", "%{$search}%")
                            ->orWhere("email", "like", "%{$search}%")
                            ->orWhere("telepon", "like", "%{$search}%");
                    })
                    ->orWhereHas("kategori", function ($query) use ($search) {
                        $query->where("nama_kategori", "like", "%{$search}%");
                    })
                    ->orWhereHas("lokasi", function ($query) use ($search) {
                        $query->where("nama_lokasi", "like", "%{$search}%");
                    })
                    ->orWhereHas("polsek", function ($query) use ($search) {
                        $query->where("nama", "like", "%{$search}%");
                    });
            });
        })
        ->when($filters['status'] ?? null, function ($query, $status) {
            $query->where("status", $status);
        })
        ->when($filters['jenis'] ?? null, function ($query, $jenis) {
            $query->whereHas("kategori", function ($query) use ($jenis) {
                $query->where("jenis", $jenis);
            });
        })
        ->when($filters['kategori_id'] ?? null, function ($query, $kategoriId) {
            $query->where("kategori_id", $kategoriId);
        })
        ->when($filters['tanggal_mulai'] ?? null, function ($query, $date) {
            $query->whereDate("created_at", ">=", $date);
        })
        ->when($filters['tanggal_selesai'] ?? null, function ($query, $date) {
            $query->whereDate("created_at", "<=", $date);
        });
    }
}
