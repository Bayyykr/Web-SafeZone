<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(\App\Http\Requests\Api\LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $firebaseService = app(FirebaseService::class);
        $firebaseUid = $firebaseService->getFirebaseUidByEmail($request->email);

        if (!$firebaseUid && $user->firebase_uid) {
            $firebaseUid = $user->firebase_uid;
        }

        if (!$firebaseUid) {
            $firebaseUid = $firebaseService->createUserInFirebaseAuth($user->email, $request->password, $user->name);
        }

        if (!$firebaseUid && !$firebaseService->getCredentials()) {
            $firebaseUid = 'local_uid_' . $user->id;
        }

        if (!$firebaseUid) {
            return response()->json([
                'message' => 'Account is not registered in Firebase',
            ], 401);
        }

        if ($user->firebase_uid !== $firebaseUid) {
            $user->update(['firebase_uid' => $firebaseUid]);
        }

        if ($request->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // Set token expiration based on remember_me
        $expiresAt = $request->remember_me ? null : now()->addDays(1);
        $token = $user->createToken('api_token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user), 
        ]);
    }
    public function register(\App\Http\Requests\Api\RegisterRequest $request, \App\Actions\Auth\RegisterUserAction $registerAction)
    {
        try {
            $user = $registerAction->execute($request->validated());

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(new UserResource($request->user()));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                // Update in Firebase Auth
                app(FirebaseService::class)->updateUserPasswordInFirebaseAuth($user->email, $password);
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'google_token' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $response = \Illuminate\Support\Facades\Http::get("https://oauth2.googleapis.com/tokeninfo", [
            'id_token' => $request->google_token
        ]);

        if (!$response->successful()) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $payload = $response->json();
        $email = $payload['email'] ?? null;
        if (!$email) {
            return response()->json(['message' => 'Email not found in token'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $randomPassword = \Illuminate\Support\Str::random(16);
            $firebaseUid = app(FirebaseService::class)->createUserInFirebaseAuth(
                $email,
                $randomPassword,
                $payload['name'] ?? explode('@', $email)[0]
            );

            if (!$firebaseUid) {
                return response()->json(['message' => 'Failed to create account in Firebase'], 500);
            }

            $user = User::create([
                'name' => $payload['name'] ?? explode('@', $email)[0],
                'username' => explode('@', $email)[0] . '_' . \Illuminate\Support\Str::random(4),
                'email' => $email,
                'password' => Hash::make($randomPassword),
                'firebase_uid' => $firebaseUid,
                'fcm_token' => $request->fcm_token,
                'role' => 'user',
                'telepon' => '',
            ]);
        } else {
            $firebaseUid = app(FirebaseService::class)->getFirebaseUidByEmail($email);
            if ($firebaseUid && $user->firebase_uid !== $firebaseUid) {
                $user->update(['firebase_uid' => $firebaseUid]);
            }
            if ($request->fcm_token) {
                $user->update(['fcm_token' => $request->fcm_token]);
            }
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => new UserResource($user),
        ]);
    }
}
