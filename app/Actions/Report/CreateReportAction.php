<?php

namespace App\Actions\Report;

use App\Models\Laporan;
use App\Models\User;

class CreateReportAction
{
    public function execute(User $user, array $data, $file = null): Laporan
    {
        $filePath = null;
        if ($file) {
            $filePath = $file->store("laporan", "public");
        }

        $data["user_id"] = $user->id;
        $data["status"] = "pending";
        $data["foto_kejadian"] = $filePath;

        return Laporan::create($data);
    }
}
