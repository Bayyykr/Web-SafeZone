<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MapDataController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CctvController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\GeofenceController;
use App\Http\Controllers\Api\RouteSafetyController;
use App\Http\Controllers\Api\CorridorController;
use App\Http\Controllers\Api\SafeZoneController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/map/statistics", [MapDataController::class, "getStatistics"]);

Route::middleware(["auth:sanctum"])->group(function () {
    Route::get("/user", function (Request $request) {
        return new UserResource($request->user());
    });
    Route::post("/auth/logout", [AuthController::class, "logout"]);
    Route::get("/auth/me", [AuthController::class, "me"]);

    Route::get("/home", [HomeController::class, "home"]);
    Route::get("/cctvs", [CctvController::class, "index"]);
    Route::get("/reports", [ReportController::class, "index"]);
    Route::get("/reports/create-options", [HomeController::class, "reportOptions"]);
    Route::post("/reports", [ReportController::class, "store"]);
    Route::get("/sos", [SosController::class, "index"]);
    Route::post("/sos", [SosController::class, "store"]);
    Route::get("/news", [NewsController::class, "index"]);
    Route::get("/news/{news}", [NewsController::class, "show"]);
    Route::get("/profile", [ProfileController::class, "show"]);

    Route::prefix("profile")->group(function () {
        Route::get("/edit", [ProfileController::class, "edit"]);
        Route::put("/", [ProfileController::class, "update"]);
        Route::delete("/", [ProfileController::class, "destroy"]);
        Route::post("/fcm-token", [FcmTokenController::class, "update"]);
    });


    Route::post("/routes/safety", [RouteSafetyController::class, "calculate"]);

    Route::post("/corridors/start", [CorridorController::class, "start"]);
    Route::post("/corridors/end", [CorridorController::class, "end"]);
    Route::post("/corridors/check-deviation", [CorridorController::class, "checkDeviation"]);

    Route::post('/geofences/check', [GeofenceController::class, 'check']);
    Route::post('/safe-zones/check', [SafeZoneController::class, 'check']);

    Route::get("/notifications", [NotificationController::class, "index"]);
    Route::post("/notifications/{notification}/read", [NotificationController::class, "markAsRead"]);
});

Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/login", [AuthController::class, "login"]);
Route::post("/auth/forgot-password", [AuthController::class, "forgotPassword"]);
Route::post("/auth/reset-password", [AuthController::class, "resetPassword"]);
Route::post("/auth/google", [AuthController::class, "googleLogin"]);
