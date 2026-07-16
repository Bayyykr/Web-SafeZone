@php $user = $item; @endphp

<div class="user-form-grid">
    <div>
        <label class="form-label" for="field-name-{{ $passwordRequired ? 'create' : $user?->id }}">
            Nama Lengkap <span class="form-required">*</span>
        </label>
        <input
            class="form-input"
            id="field-name-{{ $passwordRequired ? 'create' : $user?->id }}"
            name="name"
            value="{{ $user?->name }}"
            placeholder="Contoh: Budi Santoso"
            required
            minlength="2"
            maxlength="255"
            data-label="Nama Lengkap">
        <span class="form-error-msg" data-for="name"></span>
    </div>

    <div>
        <label class="form-label" for="field-username-{{ $passwordRequired ? 'create' : $user?->id }}">
            Username
        </label>
        <input
            class="form-input"
            id="field-username-{{ $passwordRequired ? 'create' : $user?->id }}"
            name="username"
            value="{{ $user?->username }}"
            placeholder="Contoh: budi.santoso"
            maxlength="100"
            pattern="[a-zA-Z0-9._\-]+"
            title="Hanya boleh huruf, angka, titik, underscore, atau strip"
            data-label="Username">
        <span class="form-error-msg" data-for="username"></span>
    </div>

    <div>
        <label class="form-label" for="field-email-{{ $passwordRequired ? 'create' : $user?->id }}">
            Email <span class="form-required">*</span>
        </label>
        <input
            class="form-input"
            id="field-email-{{ $passwordRequired ? 'create' : $user?->id }}"
            type="email"
            name="email"
            value="{{ $user?->email }}"
            placeholder="nama@email.com"
            required
            maxlength="255"
            data-label="Email">
        <span class="form-error-msg" data-for="email"></span>
    </div>

    <div>
        <label class="form-label" for="field-telepon-{{ $passwordRequired ? 'create' : $user?->id }}">
            No. Telepon
        </label>
        <input
            class="form-input"
            id="field-telepon-{{ $passwordRequired ? 'create' : $user?->id }}"
            type="tel"
            name="telepon"
            value="{{ $user?->telepon }}"
            placeholder="08xxxxxxxxxx"
            pattern="[0-9]{8,15}"
            maxlength="15"
            inputmode="numeric"
            title="Hanya boleh angka, 8–15 digit"
            data-label="No. Telepon"
            data-numeric>
        <span class="form-error-msg" data-for="telepon"></span>
    </div>

    <div @if(auth()->user()->role !== 'super_admin') style="display: none;" @endif>
        <label class="form-label" for="field-role-{{ $passwordRequired ? 'create' : $user?->id }}">
            Role <span class="form-required">*</span>
        </label>
        <select
            class="form-select"
            id="field-role-{{ $passwordRequired ? 'create' : $user?->id }}"
            name="role"
            required
            data-label="Role">
            @if (auth()->user()->role === 'super_admin')
                @foreach (['admin' => 'Admin', 'user' => 'User'] as $value => $label)
                    <option value="{{ $value }}" @selected(($user?->role ?? 'user') === $value)>{{ $label }}</option>
                @endforeach
            @else
                <option value="user" selected>User</option>
            @endif
        </select>
        <span class="form-error-msg" data-for="role"></span>
    </div>

    <div>
        <label class="form-label" for="field-password-{{ $passwordRequired ? 'create' : $user?->id }}">
            Password
            @if ($passwordRequired)
                <span class="form-required">*</span>
            @else
                <span class="form-optional">(kosongkan jika tidak diubah)</span>
            @endif
        </label>
        <div class="form-password-wrap">
            <input
                class="form-input"
                id="field-password-{{ $passwordRequired ? 'create' : $user?->id }}"
                type="password"
                name="password"
                @if ($passwordRequired) required @endif
                minlength="8"
                maxlength="100"
                autocomplete="new-password"
                placeholder="{{ $passwordRequired ? 'Min. 8 karakter' : 'Kosongkan jika tidak diubah' }}"
                data-label="Password">
            <button type="button" class="form-password-toggle" data-toggle-password aria-label="Tampilkan password">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
        </div>
        <span class="form-error-msg" data-for="password"></span>
    </div>

    <div class="user-form-full">
        <label class="form-label" for="field-alamat-{{ $passwordRequired ? 'create' : $user?->id }}">
            Alamat
        </label>
        <textarea
            class="form-input"
            id="field-alamat-{{ $passwordRequired ? 'create' : $user?->id }}"
            name="alamat"
            placeholder="Alamat lengkap user"
            maxlength="255"
            style="min-height:76px;resize:vertical"
            data-label="Alamat">{{ $user?->alamat }}</textarea>
        <span class="form-error-msg" data-for="alamat"></span>
    </div>
</div>

<div style="margin-top:14px">
    <label class="user-checkbox-label">
        <input type="checkbox" name="aktif" value="1" @checked($user?->aktif ?? true)>
        <span>User aktif</span>
    </label>
</div>
