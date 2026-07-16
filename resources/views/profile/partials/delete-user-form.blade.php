<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Hapus Akun</h2>
        <p class="mt-1 text-sm text-gray-500">Setelah akun dihapus, seluruh data yang terkait dengan akun ini akan ikut terhapus secara permanen.</p>
    </header>

    <button class="btn-delete-text mt-5" type="button" data-modal-target="delete-account-modal">Hapus Akun</button>

    <div id="delete-account-modal" class="modal-backdrop" @if (!$errors->userDeletion->isNotEmpty()) hidden @endif>
        <div class="modal-card modal-card-sm">
            <div class="modal-header">
                <h2>Konfirmasi Hapus Akun</h2>
                <button type="button" data-modal-close="delete-account-modal">×</button>
            </div>
            <form class="modal-body space-y-4" method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <p class="text-sm text-gray-700">Masukkan password untuk menghapus akun secara permanen.</p>

                <div>
                    <label class="form-label" for="password">Password</label>
                    <input id="password" class="form-input" name="password" type="password" placeholder="Password">
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="modal-footer">
                    <button class="btn-secondary" type="button" data-modal-close="delete-account-modal">Batal</button>
                    <button class="btn-delete-text" type="submit">Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
</section>
