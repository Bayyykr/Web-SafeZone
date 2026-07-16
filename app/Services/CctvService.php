<?php

namespace App\Services;

use App\Models\Cctv;

class CctvService
{
    public function store(array $data): Cctv
    {
        return Cctv::create($data);
    }

    public function update(Cctv $cctv, array $data): Cctv
    {
        $cctv->update($data);
        return $cctv;
    }

    public function destroy(Cctv $cctv): bool
    {
        return $cctv->delete();
    }
}
