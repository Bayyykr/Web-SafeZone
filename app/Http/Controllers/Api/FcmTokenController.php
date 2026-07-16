<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function update(\App\Http\Requests\Api\UpdateFcmTokenRequest $request): JsonResponse
    {

        $request->user()->update([
            "fcm_token" => $request->fcm_token,
        ]);

        return response()->json([
            "message" => "FCM token updated successfully",
        ]);
    }
}
