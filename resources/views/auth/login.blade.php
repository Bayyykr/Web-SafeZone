<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login SafeZone</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
    </head>
    <body class="h-full min-h-screen antialiased bg-white text-neutral-900 overflow-hidden select-none">

        <div class="h-screen w-screen grid grid-cols-1 lg:grid-cols-12 bg-white overflow-hidden">
            
            <div class="hidden lg:flex lg:col-span-5 relative h-full w-full flex-col justify-between p-12" style="background-image: url('/icons/login_illustration.png'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-neutral-950/70 backdrop-blur-[0.5px]"></div>
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-2">
                        <span class="text-base font-extrabold tracking-widest text-white uppercase">Safe<span class="text-[#6b93ff]">Zone</span></span>
                    </a>
                </div>

                <div class="relative z-10 space-y-4 max-w-sm my-auto text-left">
                    <span class="text-xs font-bold uppercase tracking-widest text-[#6b93ff]">Portal Keamanan Internal</span>
                    <h2 class="text-3xl font-extrabold leading-tight text-white filter drop-shadow-sm">
                        Mari Bersama Mengamankan Wilayah
                    </h2>
                    <p class="text-sm text-white/80 leading-relaxed filter drop-shadow-sm">
                        Masuk sebagai operator atau administrator untuk memproses laporan masyarakat dan memantau kondisi keamanan wilayah secara berkala dan real-time.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-white/40">
                    &copy; {{ date('Y') }} SafeZone. Hak Cipta Dilindungi.
                </div>
            </div>

            <div class="col-span-12 lg:col-span-7 flex flex-col justify-between py-6 px-8 sm:px-12 md:px-16 h-full overflow-hidden bg-white relative">
                
                <div class="flex items-center justify-between relative z-10 w-full pt-2">
                    <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-neutral-400 hover:text-neutral-900 transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Home
                    </a>

                    <a href="/" class="flex lg:hidden items-center gap-2">
                        <span class="text-base font-extrabold tracking-widest text-neutral-900 uppercase">Safe<span class="text-[#2952e3]">Zone</span></span>
                    </a>
                </div>

                <div class="max-w-md w-full mx-auto my-auto space-y-4 relative z-10">
                    
                    <div class="text-center mb-4">
                        <h1 class="text-2xl md:text-3xl font-[950] text-neutral-900 tracking-tight mb-1.5 uppercase">Welcome Back</h1>
                        <p class="text-xs text-neutral-400">Enter your email and password to access your account</p>
                    </div>

                    @if (session('status'))
                        <div class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 p-3 rounded-xl mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-3.5">
                        @csrf

                        <div class="space-y-1">
                            <label for="email" class="text-xs font-bold text-neutral-500 tracking-wide uppercase">Email</label>
                            <input id="email" 
                                   class="admin-form-input" 
                                   type="text" 
                                   name="email" 
                                   placeholder="Enter your email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="text-xs text-rose-600 font-semibold mt-0.5" />
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="text-xs font-bold text-neutral-500 tracking-wide uppercase">Password</label>
                            <div class="relative">
                                <input id="password" 
                                       class="admin-form-input pr-12" 
                                       type="password" 
                                       name="password" 
                                       placeholder="Enter your password" 
                                       required 
                                       autocomplete="current-password" />
                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-4 flex items-center text-neutral-400 hover:text-neutral-600 transition">
                                    <svg class="w-5 h-5" id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="text-xs text-rose-600 font-semibold mt-0.5" />
                        </div>

                        <div class="flex items-center justify-between text-xs text-neutral-400 pt-0.5 pb-1">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded border-neutral-300 text-[#2952e3] focus:ring-[#2952e3]/20 focus:ring-offset-0">
                                <span class="ml-2 font-medium">Remember me</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a class="font-bold text-[#2952e3] hover:underline" href="{{ route('password.request') }}">
                                    Forgot Password
                                </a>
                            @endif
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full bg-[#2952e3] text-white font-bold py-2.5 px-4 rounded-xl hover:bg-[#1e3fc7] transition duration-200 shadow-md shadow-blue-600/10 text-sm tracking-wide">
                                Sign In
                            </button>
                        </div>

                        <div>
                            <button type="button" class="w-full bg-white border border-neutral-200 text-neutral-600 font-bold py-2.5 px-4 rounded-xl hover:bg-neutral-50 transition duration-200 flex items-center justify-center gap-2 text-sm shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Sign in with Google
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-center text-xs text-neutral-400">
                        Don't have an account? 
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="font-bold text-[#2952e3] hover:underline">Sign up</a>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const passwordInput = document.getElementById('password');
                const toggleButton = document.getElementById('toggle-password');
                const eyeIcon = document.getElementById('eye-icon');

                toggleButton.addEventListener('click', () => {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    if (isPassword) {
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        `;
                    } else {
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        `;
                    }
                });
            });
        </script>
    </body>
</html>
