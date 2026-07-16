<?php

namespace App\Actions\Profile;

use App\Models\User;

class UpdateProfileAction
{
    public function execute(User $user, array $data, $profilePhoto = null): User
    {
        if ($profilePhoto) {
            $data["profile_photo"] = $profilePhoto->store("profile_photos", "public");
        }

        $user->update($data);

        return $user;
    }
}
