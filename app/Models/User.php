<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[
    Fillable([
        "name",
        "username",
        "email",
        "telepon",
        "alamat",
        "role",
        "aktif",
        "profile_photo",
        "password",
        "fcm_token",
        "firebase_uid",
    ]),
]
#[Hidden(["password", "remember_token"])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "aktif" => "boolean",
            "password" => "hashed",
        ];
    }

    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class);
    }

    public function konfirmasiLaporans(): HasMany
    {
        return $this->hasMany(KonfirmasiLaporan::class, "petugas_id");
    }

    public function beritas(): HasMany
    {
        return $this->hasMany(Berita::class);
    }


    public function corridorSessions(): HasMany
    {
        return $this->hasMany(CorridorSession::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
