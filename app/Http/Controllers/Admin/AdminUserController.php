<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): View
    {
        $currentUser = auth()->user();

        $items = User::query()
            ->when($currentUser->role === 'super_admin', function ($query) {
                return $query->where('role', '!=', 'super_admin');
            })
            ->when($currentUser->role === 'admin', function ($query) {
                return $query->where('role', 'user');
            })
            ->when(
                $request->search,
                fn($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%");
                }),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => $currentUser->role === 'super_admin'
                ? User::where('role', '!=', 'super_admin')->count()
                : User::where('role', 'user')->count(),
            'active' => $currentUser->role === 'super_admin'
                ? User::where('role', '!=', 'super_admin')->where('aktif', true)->count()
                : User::where('role', 'user')->where('aktif', true)->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.master.users.index', compact('items', 'stats'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (auth()->user()->role === 'admin') {
            $data['role'] = 'user';
        }
        $this->userService->store($data, $request->boolean('aktif'));

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (auth()->user()->role === 'admin') {
            $data['role'] = 'user';
        }
        $this->userService->update($user, $data, $request->boolean('aktif'));

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $deleted = $this->userService->destroy($user, auth()->user());

        if (!$deleted) {
            return redirect()->route('admin.users.index')->with('error', 'User yang sedang login tidak dapat dihapus.');
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
