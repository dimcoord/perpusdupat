<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun Siswa - Perpus Dupat | SMA Negeri 24 Bandung</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen w-screen overflow-x-hidden font-sans text-slate-900 bg-slate-100 antialiased flex items-center justify-center p-3 sm:p-6 relative select-none"
      x-data="{
          mounted: false,
          showPassword: false,
          showConfirmPassword: false
      }"
      x-init="setTimeout(() => mounted = true, 50)">

    <!-- Animated Ambient Background Glow -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl animate-orb-1"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#102a54]/25 blur-3xl animate-orb-2"></div>
        <div class="absolute top-1/2 left-1/3 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-blue-400/10 blur-3xl animate-pulse-soft"></div>
        
        <svg class="absolute inset-0 w-full h-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="grid-dots" width="24" height="24" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1" fill="#102a54" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid-dots)" />
        </svg>
    </div>

    <!-- Main Container Card -->
    <div class="w-full max-w-4xl lg:max-w-5xl bg-white rounded-3xl border border-slate-200/90 shadow-[0_20px_60px_-15px_rgba(16,42,84,0.2)] overflow-hidden grid grid-cols-1 lg:grid-cols-12 max-h-[94vh] transition-all duration-700 ease-out transform my-auto"
         :class="mounted ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-[0.98]'">
        
        <!-- LEFT COLUMN: Brand Hero Banner -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#102a54] via-[#1a4f8b] to-[#2563eb] text-white p-7 sm:p-9 lg:p-10 flex flex-col justify-between relative overflow-hidden group">
            
            <!-- Concentric Decorative Geometry -->
            <div class="absolute inset-0 pointer-events-none opacity-30 animate-geometry">
                <svg class="w-full h-full object-cover" viewBox="0 0 450 650" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="70" y="50" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.35" transform="rotate(-7 70 50)" />
                    <rect x="90" y="70" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.25" transform="rotate(-3 90 70)" />
                    <rect x="110" y="90" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.18" transform="rotate(1 110 90)" />
                    <rect x="130" y="110" width="360" height="420" rx="42" stroke="white" stroke-width="1.2" stroke-opacity="0.12" transform="rotate(5 130 110)" />
                </svg>
            </div>

            <!-- Top Logo SMAN 24 -->
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

            <!-- Content Section -->
            <div class="relative z-10 my-auto py-4">
                <h1 class="text-3xl sm:text-4xl lg:text-[40px] font-extrabold tracking-tight text-white leading-tight">
                    Mulai Langkah<br>
                    <span class="animate-shimmer-gold inline-block">Literasimu!</span>
                    <span class="inline-block ml-1">📚</span>
                </h1>

                <p class="text-blue-50/90 text-xs sm:text-sm leading-relaxed mt-4 font-normal max-w-sm">
                    Daftarkan akun perpustakaan digitalmu untuk kemudahan peminjaman mandiri, pantau jadwal buku, dan akses katalog lengkap SMAN 24 Bandung.
                </p>

                <div class="mt-6 flex items-center gap-3 text-xs text-blue-100/80">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/15">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Khusus Siswa SMAN 24
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/15">
                        Bebas Denda
                    </span>
                </div>
            </div>

            <!-- Clean Bottom Footer -->
            <div class="relative z-10 pt-4 border-t border-white/15 text-[11px] text-blue-100/70 flex items-center justify-between">
                <span>&copy; {{ date('Y') }} Perpus Dupat. All rights reserved.</span>
            </div>
        </div>

        <!-- RIGHT COLUMN: Registration Form -->
        <div class="lg:col-span-7 p-6 sm:p-8 lg:p-9 flex flex-col justify-between bg-white overflow-y-auto max-h-[94vh]">
            <div>
                <!-- Form Heading -->
                <div class="mb-4">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Akun Siswa Baru</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Lengkapi data di bawah ini untuk membuat akun perpustakaan.
                    </p>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                            Nama Lengkap Siswa <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative input-interactive rounded-xl group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   placeholder="Contoh: Ahmad Pratama"
                                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('name') border-rose-400 ring-2 ring-rose-100 @enderror">
                        </div>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Grid NIS & No. HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- NIS -->
                        <div>
                            <label for="nis_nip" class="block text-xs font-semibold text-slate-700 mb-1">
                                NIS (Nomor Induk Siswa) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative input-interactive rounded-xl group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <input type="text"
                                       name="nis_nip"
                                       id="nis_nip"
                                       value="{{ old('nis_nip') }}"
                                       required
                                       placeholder="Contoh: 2024101"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 font-mono placeholder:font-sans placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('nis_nip') border-rose-400 ring-2 ring-rose-100 @enderror">
                            </div>
                            @error('nis_nip')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No. HP / WhatsApp -->
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">
                                No. HP / WhatsApp <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative input-interactive rounded-xl group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="tel"
                                       name="phone"
                                       id="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="Contoh: 081234567890"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('phone') border-rose-400 ring-2 ring-rose-100 @enderror">
                            </div>
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alamat Email Siswa <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative input-interactive rounded-xl group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="nama@email.com"
                                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white @error('email') border-rose-400 ring-2 ring-rose-100 @enderror">
                        </div>
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Grid Password & Konfirmasi Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                                Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative input-interactive rounded-xl group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password"
                                       id="password"
                                       required
                                       placeholder="Minimal 8 karakter"
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
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Ulangi Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative input-interactive rounded-xl group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1a4f8b] group-focus-within:scale-110 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <input :type="showConfirmPassword ? 'text' : 'password'"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       required
                                       placeholder="Ulangi kata sandi"
                                       class="w-full pl-9 pr-9 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition-all duration-200 bg-slate-50/60 hover:bg-white focus:bg-white">
                                <button type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        aria-label="Tampilkan atau sembunyikan kata sandi"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 active:scale-90 transition-all duration-150">
                                    <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="btn-press btn-sheen w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#1a4f8b] to-[#2563eb] hover:from-[#153e75] hover:to-[#1d4ed8] text-white font-medium text-xs sm:text-sm shadow-md shadow-blue-900/20 transition-all flex items-center justify-center gap-2 group active:scale-[0.98]">
                            <span>Daftar Sekarang</span>
                            <svg class="w-4 h-4 transition-transform duration-300 ease-out group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center pt-2 text-xs text-slate-500">
                        Sudah memiliki akun perpustakaan? 
                        <a href="{{ route('login') }}" class="font-semibold text-[#1a4f8b] hover:text-[#102a54] hover:underline transition">Masuk di sini &rarr;</a>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
