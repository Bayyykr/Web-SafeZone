<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view("auth.login");
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->with('error', 'Login gagal.');
        }

        if (!in_array(Auth::user()?->role, ['admin', 'super_admin'])) {
            Auth::guard("web")->logout();
            return redirect()->route("login")->withErrors([
                "email" => "Akses ditolak. Halaman ini hanya untuk Administrator.",
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route("dashboard", absolute: false))->with('success', 'Anda berhasil masuk!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard("web")->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect("/");
    }
}
