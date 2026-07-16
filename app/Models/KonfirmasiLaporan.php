<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['laporan_id', 'petugas_id', 'status', 'catatan', 'dikonfirmasi_pada'])]
class KonfirmasiLaporan extends Model
{
    protected function casts(): array
    {
        return ['dikonfirmasi_pada' => 'datetime'];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
