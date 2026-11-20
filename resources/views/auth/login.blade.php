<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Perpus Dupat | SMA Negeri 24 Bandung</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen w-screen overflow-hidden font-sans text-slate-900 bg-slate-100 antialiased flex items-center justify-center p-3 sm:p-6 relative select-none"
      x-data="{
          mounted: false,
          showPassword: false,
          helpOpen: false
      }"
      x-init="setTimeout(() => mounted = true, 50)">

    <!-- Animated Ambient Background Glow (Dynamic Drifting Orbs) -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl animate-orb-1"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#102a54]/25 blur-3xl animate-orb-2"></div>
        <div class="absolute top-1/2 left-1/3 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-blue-400/10 blur-3xl animate-pulse-soft"></div>
        
        <!-- Subtle Animated Dot Grid -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="grid-dots" width="24" height="24" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1" fill="#102a54" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid-dots)" />
        </svg>
    </div>

    <!-- Main Container Card (Viewport-fit, smooth entrance, no scroll) -->
    <div class="w-full max-w-4xl lg:max-w-5xl bg-white rounded-3xl border border-slate-200/90 shadow-[0_20px_60px_-15px_rgba(16,42,84,0.2)] overflow-hidden grid grid-cols-1 lg:grid-cols-12 max-h-[94vh] transition-all duration-700 ease-out transform"
         :class="mounted ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-[0.98]'">
        
        <!-- LEFT COLUMN: Brand Hero Banner (SMAN 24 Royal Blue & Navy Identity) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#102a54] via-[#1a4f8b] to-[#2563eb] text-white p-7 sm:p-9 lg:p-10 flex flex-col justify-between relative overflow-hidden group">
            
            <!-- Concentric Decorative Geometry (Continuous Slow Ambient Breathing Animation) -->
            <div class="absolute inset-0 pointer-events-none opacity-30 animate-geometry">
                <svg class="w-full h-full object-cover" viewBox="0 0 450 650" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="70" y="50" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.35" transform="rotate(-7 70 50)" />
                    <rect x="90" y="70" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.25" transform="rotate(-3 90 70)" />
                    <rect x="110" y="90" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.18" transform="rotate(1 110 90)" />
                    <rect x="130" y="110" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.12" transform="rotate(5 130 110)" />
                </svg>
            </div>

            <!-- Top Logo SMAN 24 with Floating Animation -->
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="animate-float-logo">
                        <img src="{{ asset('images/logo-sman24.jpg') }}" 
                             alt="Logo SMAN 24 Bandung" 
                             class="w-11 h-11 rounded-full object-contain bg-white p-0.5 shadow-lg shadow-black/25 ring-2 ring-white/30 transition-transform duration-300 hover:scale-105">
                    </div>
                    <div>
                        <span class="block text-xs font-bold tracking-wider uppercase text-blue-200">SMAN 24 BANDUNG</span>
                        <span class="text-[10px] text-blue-100/75 font-medium">Perpustakaan Digital</span>
                    </div>
                </div>
            </div>

            <!-- Content Section with Animated Shimmer Text -->
            <div class="relative z-10 my-auto py-4">
                <!-- Large Greeting -->
                <h1 class="text-3xl sm:text-4xl lg:text-[42px] font-extrabold tracking-tight text-white leading-tight">
                    Hello<br>
                    <span class="animate-shimmer-gold inline-block">Sahabat Dupat!</span>
                    <span class="animate-wave inline-block ml-1">👋</span>
                </h1>

                <!-- Descriptive Subtitle for Library -->
                <p class="text-blue-50/90 text-xs sm:text-sm leading-relaxed mt-4 font-normal max-w-sm transition-opacity duration-300 hover:opacity-100">
                    Jelajahi jendela literasi digital SMA Negeri 24 Bandung. Cari katalog buku, pantau pinjaman, dan nikmati layanan perpustakaan dengan cepat dan nyaman.
                </p>
            </div>

            <!-- Clean Bottom Footer -->
            <div class="relative z-10 pt-4 border-t border-white/15 text-[11px] text-blue-100/70 flex items-center justify-between">
                <span>&copy; {{ date('Y') }} Perpus Dupat. All rights reserved.</span>
            </div>
        </div>

        <!-- RIGHT COLUMN: Authentication Form -->
        <div class="lg:col-span-7 p-6 sm:p-8 lg:p-9 flex flex-col justify-between bg-white overflow-hidden">
            <div>
                <!-- Brand Header -->
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-[#1a4f8b] text-white flex items-center justify-center shadow-xs transition-transform duration-300 hover:rotate-6 hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-slate-900 tracking-tight leading-none">Perpus Dupat</span>
                        <span class="text-[10px] text-slate-500 font-medium">SMA Negeri 24 Bandung</span>
                    </div>
                </div>

                <!-- Form Heading -->
                <div class="mb-4">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang Kembali!</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Silakan masukkan kredensial perpustakaan Anda untuk masuk.
                    </p>
                </div>

                <!-- Session Flash Message -->
                @if(session('success'))
                    <div class="mb-3.5 p-2.5 rounded-xl bg-emerald-50 text-emerald-800 text-xs border border-emerald-200 flex items-center gap-2 animate-float">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-3.5">
                    @csrf

                    <!-- Identifier Input (NIS / NIP / Email) with Micro-Interaction -->
                    <div>
                        <label for="login" class="block text-xs font-semibold text-slate-700 mb-1">
                            NIS / NIP / Email
                        </label>
                        <div class="relative input-interactive rounded-xl group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   x-ref="loginInput"
                                   value="{{ old('login') }}"
                                   required
                                   autofocus
                                   placeholder="Contoh: 2024101 atau admin"
                                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('login') border-rose-400 ring-2 ring-rose-100 @enderror">
                        </div>
                        @error('login')
                            <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Input with Animated Show/Hide Toggle -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-xs font-semibold text-slate-700">
                                Kata Sandi
                            </label>
                            <button type="button" 
                                    @click="helpOpen = !helpOpen"
                                    class="text-[11px] font-medium text-[#1a4f8b] hover:text-[#102a54] hover:underline transition-colors">
                                Butuh bantuan?
                            </button>
                        </div>
                        <div class="relative input-interactive rounded-xl group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   id="password"
                                   x-ref="passwordInput"
                                   required
                                   placeholder="••••••••"
                                   class="w-full pl-9 pr-9 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('password') border-rose-400 ring-2 ring-rose-100 @enderror">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 active:scale-90 transition-all duration-150">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Help Collapsible Note with Spring Transition -->
                    <div x-show="helpOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         x-cloak
                         class="p-2.5 bg-blue-50/80 border border-blue-200/70 rounded-xl text-[11px] text-blue-950 leading-snug">
                        Siswa masuk menggunakan <strong>NIS</strong>. Jika lupa kata sandi atau belum memiliki akun, silakan hubungi petugas perpustakaan di ruang baca.
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer select-none group">
                            <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded text-[#1a4f8b] border-slate-300 focus:ring-[#1a4f8b] transition-transform duration-150 group-hover:scale-110">
                            <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors">Ingat sesi saya</span>
                        </label>
                    </div>

                    <!-- Primary Submit Button with Light Sheen Sweep & Arrow Translation -->
                    <button type="submit" class="btn-press btn-sheen w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#1a4f8b] to-[#2563eb] hover:from-[#153e75] hover:to-[#1d4ed8] text-white font-medium text-xs sm:text-sm shadow-md shadow-blue-900/20 transition-all flex items-center justify-center gap-2 group active:scale-[0.98]">
                        <span>Masuk Sekarang</span>
                        <svg class="w-4 h-4 transition-transform duration-300 ease-out group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Bottom Kiosk Station Shortcut Link with Hover Slide Effect -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400 text-[11px]">Hanya mengisi presensi?</span>
                <a href="{{ route('kiosk.index') }}" class="font-medium text-[#1a4f8b] hover:text-[#102a54] hover:underline inline-flex items-center gap-1.5 transition-all text-xs group">
                    <span>Buka Kios Presensi Mandiri</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

    <!-- Toast Notification with Spring Pop -->
    <div x-data
         x-show="$store.toast && $store.toast.show"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-90"
         x-cloak
         class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs shadow-2xl border border-slate-800">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-ping"></span>
        <span x-text="$store.toast ? $store.toast.message : ''"></span>
    </div>

</body>
</html>
