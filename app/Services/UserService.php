<?php

namespace App\Services;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function store(array $data, bool $aktif): User
    {
        $plainPassword = $data['password'];
        $data['password'] = Hash::make($plainPassword);
        $data['aktif'] = $aktif;

        $user = User::create($data);

        app(FirebaseService::class)->createUserInFirebaseAuth($user->email, $plainPassword, $user->name);

        return $user;
    }

    public function update(User $user, array $data, bool $aktif): User
    {
        $data['aktif'] = $aktif;

        if (!empty($data['password'])) {
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            app(FirebaseService::class)->updateUserPasswordInFirebaseAuth($user->email, $plainPassword);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user;
    }

    public function destroy(User $user, User $currentUser): bool
    {
        if ($user->is($currentUser)) {
            return false;
        }

        return $user->delete();
    }
}
