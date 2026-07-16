<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Exception;

class RegisterUserAction
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function execute(array $data): User
    {
        $firebaseUid = $this->firebaseService->createUserInFirebaseAuth(
            $data['email'], 
            $data['password'], 
            $data['name']
        );

        if (!$firebaseUid) {
            throw new Exception("Gagal mendaftarkan akun di sistem kami (Firebase Error).");
        }

        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'telepon' => $data['telepon'],
            'role' => 'user',
            'password' => Hash::make($data['password']),
            'fcm_token' => $data['fcm_token'] ?? null,
            'firebase_uid' => $firebaseUid,
        ]);
    }
}
