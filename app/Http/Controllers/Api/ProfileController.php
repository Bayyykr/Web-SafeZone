<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(new UserResource(Auth::user()));
    }

    public function edit(): JsonResponse
    {
        return response()->json(new UserResource(Auth::user()));
    }

    public function update(UpdateProfileRequest $request, \App\Actions\Profile\UpdateProfileAction $updateAction): JsonResponse
    {
        $user = $updateAction->execute(
            Auth::user(),
            $request->validated(),
            $request->file("profile_photo")
        );

        return response()->json([
            "message" => "Profile updated successfully",
            "user" => new UserResource($user),
        ]);
    }

    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $user->delete();

        return response()->json(["message" => "Account deleted successfully"]);
    }
}
