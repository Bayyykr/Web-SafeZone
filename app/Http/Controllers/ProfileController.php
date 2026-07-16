<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view("profile.edit", [
            "user" => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile("profile_photo")) {
            if ($user->profile_photo) {
                Storage::disk("public")->delete($user->profile_photo);
            }

            $data["profile_photo"] = $request
                ->file("profile_photo")
                ->store("profile-photos", "public");
        } else {
            unset($data["profile_photo"]);
        }

        $user->fill($data);

        if ($user->isDirty("email")) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route("profile.edit")->with(
            "status",
            "profile-updated",
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag("userDeletion", [
            "password" => ["required", "current_password"],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->profile_photo) {
            Storage::disk("public")->delete($user->profile_photo);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to("/");
    }
}
