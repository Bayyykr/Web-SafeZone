<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Ubah Password</h2>
        <p class="mt-1 text-sm text-gray-500">Gunakan password yang kuat untuk menjaga keamanan akun.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <label class="form-label" for="update_password_current_password">Password Saat Ini</label>
            <input id="update_password_current_password" class="form-input" name="current_password" type="password" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label class="form-label" for="update_password_password">Password Baru</label>
            <input id="update_password_password" class="form-input" name="password" type="password" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label class="form-label" for="update_password_password_confirmation">Konfirmasi Password</label>
            <input id="update_password_password_confirmation" class="form-input" name="password_confirmation" type="password" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button class="btn-primary" type="submit">Simpan</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
