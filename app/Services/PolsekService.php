<?php

namespace App\Services;

use App\Models\Polsek;

class PolsekService
{
    public function store(array $data): Polsek
    {
        return Polsek::create($data);
    }

    public function update(Polsek $polsek, array $data): Polsek
    {
        $polsek->update($data);
        return $polsek;
    }

    public function destroy(Polsek $polsek): bool
    {
        return $polsek->delete();
    }
}
