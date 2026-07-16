<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "user_id",
        "route_points",
        "status",
    ]),
]
class CorridorSession extends Model
{
    protected function casts(): array
    {
        return [
            "route_points" => "array",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
