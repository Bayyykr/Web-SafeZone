<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Console\Command;

class SyncFirebaseUsers extends Command
{
    protected $signature = "firebase:sync-users";
    protected $description = "Sync existing database dummy users to Firebase Authentication";

    public function handle(): int
    {
        $firebaseService = app(FirebaseService::class);
        $users = User::all();

        $passwords = [
            "admin.kominfo@lumajang.go.id" => "KominfoLumajang2026!",
            "admin.lumajang@polri.go.id" => "PolriLumajang2026!",
            "admin.patroli@polri.go.id" => "PolriPatroli2026!",
            "admin.polsekkota@polri.go.id" => "PolsekKota2026!",
            "bayukristanto2005@gmail.com" => "masyarakat123",
            "budi.santoso@gmail.com" => "masyarakat123",
            "dewi.lestari@polri.go.id" => "PolriDewi2026!",
            "agus.prasetyo@gmail.com" => "masyarakat123",
            "siti.aminah@gmail.com" => "masyarakat123",
            "eko.wahyudi@polri.go.id" => "PolriEko2026!",
            "maya.putri@gmail.com" => "masyarakat123",
            "andi.saputra@gmail.com" => "masyarakat123",
            "fitri.handayani@polri.go.id" => "PolriFitri2026!",
            "hendra.wijaya@polri.go.id" => "PolriHendra2026!",
            "nia.kurniawati@gmail.com" => "masyarakat123",
            "yoga.firmansyah@gmail.com" => "masyarakat123",
        ];

        $this->info("Starting synchronization of " . $users->count() . " users to Firebase Auth...");

        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            $password = $passwords[$user->email] ?? "masyarakat123";
            $uid = $firebaseService->createUserInFirebaseAuth($user->email, $password, $user->name);

            if ($uid) {
                $user->update(["firebase_uid" => $uid]);
                $this->line("Synced: " . $user->email . " (UID: " . $uid . ")");
                $successCount++;
            } else {
                $this->error("Failed to sync: " . $user->email);
                $failedCount++;
            }
        }

        $this->info("Synchronization finished. Success: " . $successCount . ", Failed: " . $failedCount);

        return 0;
    }
}
