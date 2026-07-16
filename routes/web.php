<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CctvController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KonfirmasiLaporanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PolsekController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return view("welcome");
})->name("landing");

Route::middleware(["auth", "verified", "prevent-back-history"])->group(function () {
    Route::get("/dashboard", function () {
        if (!in_array(Auth::user()?->role, ["admin", "super_admin"])) {
            Auth::guard("web")->logout();
            return redirect()->route("login")->withErrors([
                "email" => "Akses ditolak. Halaman ini hanya untuk Administrator.",
            ]);
        }
        return app(DashboardController::class)->__invoke();
    })->name("dashboard");

    Route::prefix("admin")
        ->name("admin.")
        ->group(function () {
            Route::resource("users", AdminUserController::class)->only([
                "index",
                "store",
                "update",
                "destroy",
            ]);
            Route::resource("polseks", PolsekController::class)->only([
                "index",
                "store",
                "update",
                "destroy",
            ]);
            Route::resource("categories", CategoryController::class)->only([
                "index",
                "store",
                "update",
                "destroy",
            ]);
            Route::resource("cctvs", CctvController::class);
            Route::resource("locations", LocationController::class)->only([
                "index",
                "store",
                "update",
                "destroy",
            ]);

            Route::get("konfirmasi-laporan/export", [
                KonfirmasiLaporanController::class,
                "export",
            ])->name("konfirmasi-laporan.export");
            Route::get("konfirmasi-laporan", [
                KonfirmasiLaporanController::class,
                "index",
            ])->name("konfirmasi-laporan.index");
            Route::patch("konfirmasi-laporan/{laporan}", [
                KonfirmasiLaporanController::class,
                "update",
            ])->name("konfirmasi-laporan.update");

            Route::get("laporan/infografik", [
                LaporanController::class,
                "infografik",
            ])->name("laporan.infografik");
            Route::get("laporan/riwayat", [
                LaporanController::class,
                "riwayat",
            ])->name("laporan.riwayat");
            Route::get("laporan/darurat/status", [
                LaporanController::class,
                "daruratStatus",
            ])->name("laporan.darurat.status");
            Route::get("laporan/darurat", [
                LaporanController::class,
                "darurat",
            ])->name("laporan.darurat");
            Route::post("laporan/darurat", [
                LaporanController::class,
                "storeDarurat",
            ])->name("laporan.darurat.store");
            Route::patch("laporan/darurat/{emergencyReport}/dispatch", [
                LaporanController::class,
                "dispatchDarurat",
            ])->name("laporan.darurat.dispatch");
            Route::patch("laporan/darurat/{emergencyReport}/complete", [
                LaporanController::class,
                "completeDarurat",
            ])->name("laporan.darurat.complete");

            Route::patch("berita/{berita}/publish", [
                BeritaController::class,
                "publish",
            ])->name("berita.publish");
            Route::patch("berita/{berita}/draft", [
                BeritaController::class,
                "draft",
            ])->name("berita.draft");
            Route::resource("berita", BeritaController::class)->only([
                "index",
                "store",
                "update",
                "destroy",
            ]);
        });

    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );
});

require __DIR__ . "/auth.php";
