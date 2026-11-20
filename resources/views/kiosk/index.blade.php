<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Presensi Mandiri - Perpus Dupat</title>
    <title>Kiosk Presensi Mandiri - Perpus Dupat (Anti-Titip Absen)</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-[#f8fafc] flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none"
      x-data="{
        nis: '',
        purpose: 'Membaca',
        password: '',
        showPassword: false,
        purpose: 'Membaca Buku',
        isLoading: false,
        totalToday: {{ $visitCountToday }},
        recentVisits: {{ json_encode($todayVisits->map(function($v) {
            return [
                'id' => $v->id,
                'name' => $v->user?->name ?? 'Tamu',
                'nis' => $v->user?->nis_nip ?? '-',
                'purpose' => $v->purpose,
                'time' => $v->created_at->format('H:i')
            ];
        })) }},
        currentTime: '',
        currentDate: '',

        // Success Modal State
        showSuccessModal: false,
        successStudent: null,
        resetCountdown: 4,
        countdownTimer: null,

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => {
                this.$refs.nisInput.focus();
            });
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        setPurpose(p) {
            this.purpose = p;
            this.$refs.nisInput.focus();
        },

        submitCheckIn() {
            if (!this.nis.trim()) {
                Alpine.store('toast').notify('Harap masukkan nomor NIS Anda.', 'warning');
                this.$refs.nisInput.focus();
                return;
            }

            if (!this.password) {
                Alpine.store('toast').notify('Harap masukkan kata sandi akun Anda untuk verifikasi identitas.', 'warning');
                this.$refs.passwordInput.focus();
                return;
            }

            this.isLoading = true;

            fetch('{{ route('kiosk.check-in') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nis: this.nis.trim(),
                    password: this.password,
                    purpose: this.purpose
                })
            })
            .then(res => res.json())
            .then(data => {
            .then(async res => {
                const data = await res.json();
                return { status: res.status, data };
            })
            .then(({ status, data }) => {
                this.isLoading = false;

                if (data.success) {
                    Alpine.store('toast').notify(data.message, 'success');
                    

                    // Prepend to live visitor queue
                    this.recentVisits.unshift(data.data);
                    if (this.recentVisits.length > 15) {
                        this.recentVisits.pop();
                    }
                    this.totalToday = data.data.total_today;

                    // Trigger Success Modal with auto-reset countdown
                    this.successStudent = data.data;
                    this.showSuccessModal = true;
                    this.resetCountdown = 4;

                    if (this.countdownTimer) clearInterval(this.countdownTimer);
                    this.countdownTimer = setInterval(() => {
                        this.resetCountdown--;
                        if (this.resetCountdown <= 0) {
                            this.closeSuccessModal();
                        }
                    }, 1000);

                    // Clear security credentials
                    this.nis = '';
                    this.password = '';
                } else {
                    Alpine.store('toast').notify(data.message || 'NIS tidak ditemukan.', 'error');
                    Alpine.store('toast').notify(data.message || 'Verifikasi gagal. Pastikan NIS dan kata sandi benar.', 'error');
                    if (status === 401) {
                        this.password = '';
                        this.$refs.passwordInput.focus();
                    } else {
                        this.$refs.nisInput.focus();
                    }
                }
                this.$refs.nisInput.focus();
            })
            .catch(() => {
                this.isLoading = false;
                Alpine.store('toast').notify('Gagal menghubungi server LAN perpustakaan.', 'error');
                this.$refs.nisInput.focus();
            });
        },

        closeSuccessModal() {
            if (this.countdownTimer) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
            this.showSuccessModal = false;
            this.successStudent = null;
            this.$nextTick(() => {
                this.$refs.nisInput.focus();
            });
        }
      }">

    <!-- Top Header with Scroll-Reveal -->
    <header class="flex items-center justify-between border-b border-[#e2e8f0] pb-4 scroll-reveal">
    <header class="flex items-center justify-between border-b border-[#e2e8f0] pb-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-sman24.jpg') }}" 
                 alt="Logo SMAN 24" 
                 class="w-10 h-10 rounded-full object-contain bg-white p-0.5 shadow-sm ring-1 ring-slate-200">
            <div>
                <h1 class="text-lg font-bold tracking-tight text-gray-900 flex items-center gap-2">
                    <span>PERPUS DUPAT</span>
                    <span class="text-xs font-semibold text-[#1a4f8b] tracking-wider uppercase">• Kiosk Mandiri</span>
                </h1>
                <p class="text-xs text-gray-500">Stasiun Presensi Siswa & Civitas SMAN 24 Bandung</p>
            </div>
        </div>

        <div class="flex items-center gap-5">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <div class="text-xl font-bold text-gray-900 tracking-tight font-sans" x-text="currentTime"></div>
                <div class="text-[11px] text-gray-500 font-medium" x-text="currentDate"></div>
            </div>

            <!-- Portal Login Link -->
            <a href="{{ route('login') }}" class="btn-press px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 border border-[#e2e8f0] shadow-2xs text-xs font-medium transition flex items-center gap-1.5 active:scale-[0.98]">
                <span>Masuk Portal</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="my-auto py-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start max-w-7xl w-full mx-auto">
        <!-- Left: Check-in Box with Scroll-Reveal & Card Hover -->
        <div class="scroll-reveal delay-1 lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-[#e2e8f0] space-y-5 shadow-xs card-hover">
        <!-- Left: Check-in Box with Anti-Titip Absen Authentication -->
        <div class="lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-[#e2e8f0] space-y-5 shadow-xs">
            <div>
                <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                    Stasiun Presensi Cepat
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-[#1a4f8b] text-[11px] font-semibold mb-2">
                    <svg class="w-3.5 h-3.5 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Sistem Terverifikasi • Anti-Titip Absen</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Kunjungan Anda</h2>
                <p class="text-xs text-gray-500 mt-1">Ketikkan Nomor Induk Siswa (NIS) atau pindai barcode kartu perpustakaan Anda.</p>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Mandiri Siswa</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Masukkan NIS dan kata sandi akun Anda untuk membuktikan kehadiran fisik langsung di perpustakaan.
                </p>
            </div>

            <!-- NIS Form -->
            <!-- Anti-Titip Absen Info Banner -->
            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5 text-xs text-amber-900">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="leading-relaxed">
                    <span class="font-semibold">Perlindungan Integritas:</span> Presensi tidak bisa diwakilkan oleh siswa lain. Siswa wajib memverifikasi akun perpustakaan masing-masing.
                </div>
            </div>

            <!-- Check-in & Authentication Form -->
            <form @submit.prevent="submitCheckIn()" class="space-y-4">
                <!-- NIS Input -->
                <div>
                    <label for="kiosk-nis" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Nomor Induk Siswa (NIS)
                    <label for="kiosk-nis" class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Nomor Induk Siswa (NIS)</span>
                        <span class="text-[11px] text-gray-400 font-normal">Ketik NIS atau Pindai Kartu</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text"
                                id="kiosk-nis"
                                x-ref="nisInput"
                                x-model="nis"
                                required
                                autocomplete="off"
                                placeholder="Contoh: 2024101"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-[#e2e8f0] text-lg font-mono text-gray-900 placeholder:text-gray-400 focus:border-[#1a4f8b] focus:ring-1 focus:ring-[#1a4f8b] outline-none transition">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-mono text-xs">
                            [Tekan Enter]
                               id="kiosk-nis"
                               x-ref="nisInput"
                               x-model="nis"
                               required
                               autocomplete="off"
                               placeholder="Contoh: 2024101"
                               class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#e2e8f0] text-base font-mono text-gray-900 placeholder:text-gray-400 focus:border-[#1a4f8b] focus:ring-2 focus:ring-[#1a4f8b]/20 outline-none transition">
                    </div>
                </div>

                <!-- Password Input for Anti-Titip Absen Verification -->
                <div>
                    <label for="kiosk-password" class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Kata Sandi Akun (Verifikasi Diri)</span>
                        <span class="text-[11px] text-gray-400 font-normal">Sandi Akun Siswa</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'"
                               id="kiosk-password"
                               x-ref="passwordInput"
                               x-model="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi akun"
                               class="w-full pl-11 pr-11 py-3 rounded-xl bg-white border border-[#e2e8f0] text-base text-gray-900 placeholder:text-gray-400 focus:border-[#1a4f8b] focus:ring-2 focus:ring-[#1a4f8b]/20 outline-none transition">
                        
                        <button type="button"
                                @click="showPassword = !showPassword"
                                tabindex="-1"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition focus:outline-none"
                                title="Tampilkan / Sembunyikan Sandi">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Purpose Selector -->
                <!-- Purpose Selector with Rich Activity Options -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Tujuan Kunjungan
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Aktivitas / Tujuan di Perpustakaan</span>
                        <span class="text-[11px] text-[#1a4f8b] font-medium" x-text="purpose"></span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @php
                            $purposes = [
                                'Membaca' => 'Membaca',
                                'Meminjam' => 'Pinjam / Balik',
                                'Tugas' => 'Tugas',
                                'Diskusi' => 'Diskusi',
                                [
                                    'id' => 'Membaca Buku',
                                    'title' => 'Membaca Buku',
                                    'desc' => 'Koleksi & Majalah',
                                    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
                                ],
                                [
                                    'id' => 'Pinjam / Kembali Buku',
                                    'title' => 'Pinjam / Kembali',
                                    'desc' => 'Sirkulasi Koleksi',
                                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'
                                ],
                                [
                                    'id' => 'Mengerjakan Tugas',
                                    'title' => 'Mengerjakan Tugas',
                                    'desc' => 'Belajar Mandiri',
                                    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
                                ],
                                [
                                    'id' => 'Diskusi Kelompok',
                                    'title' => 'Diskusi Kelompok',
                                    'desc' => 'Kerja Kelompok / Projek',
                                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
                                ],
                                [
                                    'id' => 'Riset Komputer / Internet',
                                    'title' => 'Riset Komputer',
                                    'desc' => 'Pencarian Daring',
                                    'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
                                ],
                                [
                                    'id' => 'Kunjungan Singkat',
                                    'title' => 'Kunjungan Singkat',
                                    'desc' => 'Konsultasi / Lainnya',
                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                                ],
                            ];
                        @endphp
                        @foreach($purposes as $val => $label)

                        @foreach($purposes as $p)
                            <button type="button"
                                    @click="setPurpose('{{ $val }}')"
                                    :class="purpose === '{{ $val }}' ? 'bg-[#1a4f8b] text-white shadow-xs' : 'bg-[#e2e8f0]/60 text-gray-700 hover:bg-[#e2e8f0]'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-medium transition text-center focus:outline-none hover:-translate-y-0.5 active:translate-y-0">
                                {{ $label }}
                                    @click="setPurpose('{{ $p['id'] }}')"
                                    :class="purpose === '{{ $p['id'] }}' 
                                        ? 'border-[#1a4f8b] bg-[#1a4f8b]/10 text-[#102a54] ring-2 ring-[#1a4f8b]/20 shadow-xs' 
                                        : 'border-[#e2e8f0] bg-white text-gray-700 hover:border-gray-300 hover:bg-slate-50'"
                                    class="p-2.5 rounded-xl border text-left transition flex flex-col justify-between group focus:outline-none">
                                <div class="flex items-center justify-between mb-1">
                                    <svg class="w-4 h-4 transition"
                                         :class="purpose === '{{ $p['id'] }}' ? 'text-[#1a4f8b]' : 'text-gray-400 group-hover:text-gray-600'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p['icon'] }}" />
                                    </svg>
                                    <span x-show="purpose === '{{ $p['id'] }}'" class="w-1.5 h-1.5 rounded-full bg-[#1a4f8b]"></span>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold leading-tight" x-text="'{{ $p['title'] }}'"></div>
                                    <div class="text-[10px] text-gray-500 mt-0.5" x-text="'{{ $p['desc'] }}'"></div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="btn-press btn-sheen w-full py-3 px-6 rounded-xl bg-gradient-to-r from-[#1a4f8b] to-[#2563eb] hover:from-[#153e75] hover:to-[#1d4ed8] active:scale-[0.99] text-white font-medium text-sm transition flex items-center justify-center gap-2 shadow-md shadow-blue-900/15">
                        class="btn-press btn-sheen w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-[#102a54] via-[#1a4f8b] to-[#2563eb] hover:from-[#0d2244] hover:to-[#1d4ed8] active:scale-[0.99] text-white font-medium text-sm transition flex items-center justify-center gap-2 shadow-md shadow-blue-900/20 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span x-show="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Mencatat Kehadiran...' : 'Konfirmasi Kehadiran (Check-In)'"></span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <span x-text="isLoading ? 'Memverifikasi Presensi...' : 'Konfirmasi Kehadiran & Aktivitas'"></span>
                    <svg x-show="!isLoading" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <div class="pt-3 border-t border-[#e2e8f0] text-[11px] text-gray-500">
                <span>Contoh NIS demo: <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">2024101</code>, <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">2024102</code></span>
            <div class="pt-3 border-t border-[#e2e8f0] flex items-center justify-between text-[11px] text-gray-500">
                <span>Demo Siswa: <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">2024101</code> / sandi: <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">password</code></span>
                <span class="text-emerald-700 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    LAN Terenkripsi
                </span>
            </div>
        </div>

        <!-- Right: Real-time Visitor Stream with Scroll-Reveal & Card Hover -->
        <div class="scroll-reveal delay-2 lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-[#e2e8f0] space-y-4 shadow-xs card-hover">
        <!-- Right: Real-time Visitor Stream with Verified Badges -->
        <div class="lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-[#e2e8f0] space-y-4 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-[#e2e8f0]">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Antrean Kunjungan Terkini</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar kehadiran hari ini secara real-time</p>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span>Antrean Presensi Terverifikasi</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Sistem Aktif"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar kehadiran siswa hari ini secara real-time</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-gray-400 uppercase font-semibold">Total Hari Ini</span>
                    <span class="text-[10px] text-gray-400 uppercase font-semibold">Total Pengunjung</span>
                    <div class="text-xl font-bold text-[#1a4f8b] font-sans" x-text="totalToday + ' Siswa'"></div>
                </div>
            </div>

            <!-- List of visitors -->
            <div class="divide-y divide-[#e2e8f0]/60 max-h-[380px] overflow-y-auto pr-1">
            <div class="divide-y divide-[#e2e8f0]/60 max-h-[440px] overflow-y-auto pr-1">
                <template x-for="item in recentVisits" :key="item.id">
                    <div class="py-2.5 flex items-center justify-between hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 rounded-lg px-2 transition-all duration-150">
                    <div class="py-2.5 flex items-center justify-between hover:bg-[#1a4f8b]/5 rounded-lg px-2.5 transition-all duration-150">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#e2e8f0]/60 text-[#1a4f8b] flex items-center justify-center font-semibold text-xs uppercase border border-[#e2e8f0] shadow-2xs">
                            <div class="w-9 h-9 rounded-full bg-blue-50 text-[#1a4f8b] flex items-center justify-center font-bold text-xs uppercase border border-blue-200 shadow-2xs">
                                <span x-text="item.name ? item.name.substring(0, 2) : 'S'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900" x-text="item.name"></div>
                                <div class="text-[11px] text-gray-400 font-mono">NIS: <span class="text-gray-600" x-text="item.nis"></span></div>
                                <div class="text-xs font-semibold text-gray-900 flex items-center gap-1.5">
                                    <span x-text="item.name"></span>
                                    <span class="inline-flex items-center text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200" title="Terverifikasi kata sandi">
                                        ✓ Sah
                                    </span>
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono">NIS: <span class="text-gray-600 font-medium" x-text="item.nis"></span></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-[#e2e8f0]/70 text-gray-700 border border-[#e2e8f0]"
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-800 border border-slate-200"
                                  x-text="item.purpose"></span>
                            <div class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="item.time + ' WIB'"></div>
                        </div>
                    </div>
                </template>

                <template x-if="recentVisits.length === 0">
                    <div class="py-12 text-center text-xs text-gray-400">
                        Belum ada siswa yang presensi hari ini. Jadilah yang pertama!
                    <div class="py-16 text-center text-xs text-gray-400 space-y-2">
                        <svg class="w-8 h-8 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p>Belum ada siswa yang presensi hari ini. Silakan lakukan presensi pertama!</p>
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- Success Modal Pop-up with Countdown Auto Reset -->
    <div x-cloak
         x-show="showSuccessModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200"
         @keydown.escape.window="closeSuccessModal()">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-emerald-100 text-center relative animate-in fade-in zoom-in-95 duration-200">
            <!-- Green Verification Check Icon -->
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-emerald-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-2">
                <span>Presensi Berhasil Terverifikasi</span>
            </div>

            <h3 class="text-xl font-bold text-gray-900 tracking-tight" x-text="successStudent ? 'Selamat Datang, ' + successStudent.name + '!' : 'Selamat Datang!'"></h3>
            <p class="text-xs text-gray-500 mt-1">Kehadiran fisik Anda di perpustakaan telah sah dicatat dalam log kunjungan.</p>

            <!-- Visitor Details Card -->
            <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left space-y-2 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="text-gray-500">Nomor Induk Siswa (NIS)</span>
                    <span class="font-mono font-semibold text-gray-900" x-text="successStudent?.nis"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="text-gray-500">Aktivitas Dipilih</span>
                    <span class="font-semibold text-[#1a4f8b]" x-text="successStudent?.purpose"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Waktu Presensi</span>
                    <span class="font-mono font-medium text-gray-700" x-text="(successStudent?.time || '') + ' WIB'"></span>
                </div>
            </div>

            <!-- Auto Close Countdown Banner -->
            <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                <button type="button"
                        @click="closeSuccessModal()"
                        class="w-full py-3 px-4 rounded-xl bg-[#102a54] hover:bg-[#1a4f8b] text-white font-medium text-xs transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Selesai / Siswa Selanjutnya</span>
                    <span class="text-[11px] opacity-75 font-mono" x-text="'(' + resetCountdown + 's)'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Global Toast for Kiosk Notifications -->
    <div x-cloak x-show="$store.toast.show" x-transition class="fixed top-8 left-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4">
        <div class="rounded-xl shadow-xl p-4 flex items-center justify-between border"
             :class="{
                'bg-emerald-50 text-emerald-800 border-emerald-200': $store.toast.type === 'success',
                'bg-rose-50 text-rose-800 border-rose-200': $store.toast.type === 'error',
                'bg-amber-50 text-amber-800 border-amber-200': $store.toast.type === 'warning',
                'bg-[#1a4f8b] text-white border-[#153e75]': $store.toast.type === 'info'
             }">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs font-semibold" x-text="$store.toast.message"></div>
            </div>
            <button @click="$store.toast.close()" class="p-1 rounded text-white/80 hover:text-white">&times;</button>
        </div>
    </div>

    <!-- Kiosk Footer -->
    <footer class="border-t border-[#e2e8f0] pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
        <div>
            Stasiun Kiosk Mandiri &bull; Perpustakaan Digital SMA Negeri 24 &bull; Jalur Subnet LAN
            Stasiun Kiosk Mandiri Anti-Titip Absen &bull; Perpustakaan Digital SMA Negeri 24 Bandung &bull; Jalur Subnet LAN
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-[#e2e8f0]/60 text-gray-700 rounded border border-[#e2e8f0] text-[10px] font-mono">F11</kbd> untuk Layar Penuh
        </div>
    </footer>
</body>
</html>
