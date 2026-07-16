<x-admin-layout>
    <x-slot name="header">Pengaturan Profil</x-slot>

    @push('styles')
        @vite('resources/css/admin/pages/user.css')
    @endpush

    @php
        $toastType = session('status') === 'profile-updated' || session('status') === 'password-updated' ? 'success' : ($errors->any() ? 'error' : null);
        $toastMessage = session('status') === 'profile-updated' ? 'Informasi profil berhasil diperbarui.' : (session('status') === 'password-updated' ? 'Password berhasil diperbarui.' : ($errors->any() ? 'Gagal menyimpan perubahan. Silakan periksa kembali inputan Anda.' : null));
    @endphp

    @if ($toastType && $toastMessage)
        <div class="toast-notification {{ $toastType }}" data-toast>
            <div class="toast-icon">{{ $toastType === 'success' ? '✓' : '!' }}</div>
            <div>
                <p class="toast-title">{{ $toastType === 'success' ? 'Berhasil' : 'Gagal' }}</p>
                <p class="toast-message">{{ $toastMessage }}</p>
            </div>
            <button type="button" data-toast-close aria-label="Tutup notifikasi">×</button>
        </div>
    @endif

    <div class="user-page">
        <div class="user-hero">
            <div class="user-hero-left">
                <div class="user-hero-icon" style="background: var(--accent-light); color: var(--accent);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Pengaturan Profil</div>
                    <div class="user-hero-subtitle">Kelola informasi akun, foto profil, dan keamanan password Anda</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="user-table-card p-6">
                <header class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-sm font-bold text-slate-800">Informasi Profil</h2>
                    <p class="text-xs text-slate-400 mt-1">Perbarui data akun dan foto profil Anda.</p>
                </header>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('patch')

                    <div>
                        <label class="form-label" for="profile_photo">Foto Profil</label>
                        <div class="mt-2 flex items-center gap-4">
                            @if ($user->profile_photo)
                                <img class="h-16 w-16 rounded-full object-cover border border-slate-200 ring-2 ring-slate-100" src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto profil {{ $user->name }}" style="flex-shrink: 0;">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-600 border border-slate-200 ring-2 ring-slate-100" style="flex-shrink: 0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <input id="profile_photo" class="form-input" name="profile_photo" type="file" accept="image/*" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); padding: 6px 12px; background: transparent;">
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Format gambar JPG, PNG, atau WebP. Maksimal 2MB.</p>
                        <x-input-error class="mt-2 text-xs text-red-500" :messages="$errors->get('profile_photo')" />
                    </div>

                    <div>
                        <label class="form-label" for="name">Nama Lengkap</label>
                        <input id="name" class="form-input" name="name" type="text" value="{{ old('name', $user->name) }}" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <x-input-error class="mt-2 text-xs text-red-500" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <label class="form-label" for="email">Alamat Email</label>
                        <input id="email" class="form-input" name="email" type="email" value="{{ old('email', $user->email) }}" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <x-input-error class="mt-2 text-xs text-red-500" :messages="$errors->get('email')" />
                    </div>

                    <div class="pt-2">
                        <button class="btn-primary w-full sm:w-auto" type="submit" style="height: 40px; padding: 0 24px;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <div class="user-table-card p-6">
                <header class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-sm font-bold text-slate-800">Ubah Password</h2>
                    <p class="text-xs text-slate-400 mt-1">Gunakan password yang kuat untuk menjaga keamanan akun Anda.</p>
                </header>

                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label class="form-label" for="update_password_current_password">Password Saat Ini</label>
                        <input id="update_password_current_password" class="form-input" name="current_password" type="password" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs text-red-500" />
                    </div>

                    <div>
                        <label class="form-label" for="update_password_password">Password Baru</label>
                        <input id="update_password_password" class="form-input" name="password" type="password" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs text-red-500" />
                    </div>

                    <div>
                        <label class="form-label" for="update_password_password_confirmation">Konfirmasi Password Baru</label>
                        <input id="update_password_password_confirmation" class="form-input" name="password_confirmation" type="password" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs text-red-500" />
                    </div>

                    <div class="pt-2">
                        <button class="btn-primary w-full sm:w-auto" type="submit" style="height: 40px; padding: 0 24px;">Simpan Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-toast-close]')) event.target.closest('[data-toast]')?.remove();
        });
        document.querySelectorAll('[data-toast]').forEach((toast) => setTimeout(() => toast.remove(), 4500));
    </script>
</x-admin-layout>
