<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view("auth.register");
    }

    public function store(RegisterRequest $request, \App\Actions\Auth\RegisterUserAction $registerAction): RedirectResponse
    {
        try {
            $data = $request->validated();
            $user = $registerAction->execute($data);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route("dashboard", absolute: false))->with('success', 'Pendaftaran berhasil!');
    }
}
