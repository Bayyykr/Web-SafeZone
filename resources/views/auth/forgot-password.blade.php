<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Lupa Kata Sandi - SafeZone</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
    </head>
    <body class="h-full min-h-screen antialiased bg-white text-neutral-900 overflow-hidden select-none">

        <div class="h-screen w-screen grid grid-cols-1 lg:grid-cols-12 bg-white overflow-hidden">
            
            <!-- Left panel with illustration -->
            <div class="hidden lg:flex lg:col-span-5 relative h-full w-full flex-col justify-between p-12" style="background-image: url('/icons/login_illustration.png'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-neutral-950/70 backdrop-blur-[0.5px]"></div>
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-2">
                        <span class="text-base font-extrabold tracking-widest text-white uppercase">Safe<span class="text-[#6b93ff]">Zone</span></span>
                    </a>
                </div>

                <div class="relative z-10 space-y-4 max-w-sm my-auto text-left">
                    <span class="text-xs font-bold uppercase tracking-widest text-[#6b93ff]">Pemulihan Akun</span>
                    <h2 class="text-3xl font-extrabold leading-tight text-white filter drop-shadow-sm">
                        Atur Ulang Kata Sandi Anda
                    </h2>
                    <p class="text-sm text-white/80 leading-relaxed filter drop-shadow-sm">
                        Kami akan mengirimkan email berisi tautan verifikasi pemulihan untuk mereset kata sandi akun SafeZone Anda secara aman.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-white/40">
                    &copy; {{ date('Y') }} SafeZone. Hak Cipta Dilindungi.
                </div>
            </div>

            <!-- Right panel with form -->
            <div class="col-span-12 lg:col-span-7 flex flex-col justify-between py-6 px-8 sm:px-12 md:px-16 h-full overflow-hidden bg-white relative">
                
                <div class="flex items-center justify-between relative z-10 w-full pt-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-400 hover:text-neutral-900 transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Login
                    </a>

                    <a href="/" class="flex lg:hidden items-center gap-2">
                        <span class="text-base font-extrabold tracking-widest text-neutral-900 uppercase">Safe<span class="text-[#2952e3]">Zone</span></span>
                    </a>
                </div>

                <div class="max-w-md w-full mx-auto my-auto space-y-4 relative z-10">
                    
                    <div class="text-center mb-4">
                        <h1 class="text-2xl md:text-3xl font-[950] text-neutral-900 tracking-tight mb-1.5 uppercase">Lupa Kata Sandi?</h1>
                        <p class="text-xs text-neutral-400">Masukkan alamat email terdaftar Anda di bawah ini</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 p-3 rounded-xl mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                        @csrf

                        <div class="space-y-1">
                            <label for="email" class="text-xs font-bold text-neutral-500 tracking-wide uppercase">Email</label>
                            <input id="email" 
                                   class="admin-form-input" 
                                   type="email" 
                                   name="email" 
                                   placeholder="nama@email.com" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus />
                            <x-input-error :messages="$errors->get('email')" class="text-xs text-rose-600 font-semibold mt-0.5" />
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#2952e3] text-white font-bold py-2.5 px-4 rounded-xl hover:bg-[#1e3fc7] transition duration-200 shadow-md shadow-blue-600/10 text-sm tracking-wide">
                                Kirim Link Reset Password
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-center text-xs text-neutral-400">
                        Belum punya akun? 
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-bold text-[#2952e3] hover:underline">Daftar sekarang</a>
                        @endif
                    </div>

                </div>

                <div class="text-center text-xs text-neutral-400 block lg:hidden pt-4">
                    &copy; {{ date('Y') }} SafeZone. Hak Cipta Dilindungi.
                </div>

            </div>

        </div>

    </body>
</html>
