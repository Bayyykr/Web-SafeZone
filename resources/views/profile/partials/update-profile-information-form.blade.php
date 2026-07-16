<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Informasi Profil</h2>
        <p class="mt-1 text-sm text-gray-500">Perbarui data akun dan foto profil Anda.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label class="form-label" for="profile_photo">Foto Profil</label>
            <div class="mt-2 flex items-center gap-4">
                @if ($user->profile_photo)
                    <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-200" src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto profil {{ $user->name }}">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-xl font-semibold text-indigo-700 ring-2 ring-gray-200">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <input id="profile_photo" class="form-input" name="profile_photo" type="file" accept="image/*">
            </div>
            <p class="mt-1 text-xs text-gray-500">Format gambar JPG, PNG, atau WebP. Maksimal 2MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <label class="form-label" for="name">Nama</label>
            <input id="name" class="form-input" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label class="form-label" for="email">Email</label>
            <input id="email" class="form-input" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        Email Anda belum diverifikasi.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">Link verifikasi baru telah dikirim ke email Anda.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button class="btn-primary" type="submit">Simpan</button>

            @if (session('status') === 'profile-updated')
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
