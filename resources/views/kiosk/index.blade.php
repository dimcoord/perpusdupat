<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Presensi Mandiri - Perpus Dupat | SMA Negeri 24 Bandung</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none"
      x-data="{
        email: '',
        password: '',
        showPassword: false,
        purpose: 'Presensi',
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

        // Success Confirmation Modal
        showSuccessModal: false,
        successStudent: null,
        resetCountdown: 4,
        countdownTimer: null,

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => {
                this.$refs.emailInput.focus();
            });
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        setPurpose(p) {
            this.purpose = p;
            this.$refs.emailInput.focus();
        },

        closeSuccessModal() {
            if (this.countdownTimer) clearInterval(this.countdownTimer);
            this.showSuccessModal = false;
            this.successStudent = null;
            this.email = '';
            this.password = '';
            this.$nextTick(() => {
                this.$refs.emailInput.focus();
            });
        },

        submitCheckIn() {
            if (!this.email.trim() || !this.password) {
                Alpine.store('toast').notify('Harap masukkan email dan kata sandi akun Anda.', 'warning');
                (this.email.trim() ? this.$refs.passwordInput : this.$refs.emailInput).focus();
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
                    email: this.email.trim(),
                    password: this.password,
                    purpose: this.purpose
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isLoading = false;
                this.password = '';

                if (data.success) {
                    // Update antrean kunjungan
                    this.recentVisits.unshift(data.data);
                    if (this.recentVisits.length > 15) {
                        this.recentVisits.pop();
                    }
                    this.totalToday = data.data.total_today;

                    // Jika Pinjam Buku, arahkan langsung ke halaman katalog
                    if (data.redirect) {
                        Alpine.store('toast').notify(data.message + ' Mengarahkan ke katalog buku...', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                        return;
                    }

                    // Jika hanya Presensi, tampilkan modal konfirmasi dengan auto-reset
                    Alpine.store('toast').notify(data.message, 'success');
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
                } else {
                    Alpine.store('toast').notify(data.message || 'Email atau kata sandi salah.', 'error');
                    this.$refs.passwordInput.focus();
                }
            })
            .catch(() => {
                this.isLoading = false;
                this.password = '';
                Alpine.store('toast').notify('Gagal menghubungi server perpustakaan.', 'error');
                this.$refs.emailInput.focus();
            });
        }
      }">

    <!-- Top Header Navigation -->
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center p-1 shadow-sm overflow-hidden shrink-0">
                <img src="/images/logo-sman24.jpg" alt="Logo SMAN 24" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-none">PERPUS DUPAT</h1>
                <p class="text-xs text-slate-500 mt-1">Stasiun Presensi Kehadiran Siswa &bull; SMA Negeri 24 Bandung</p>
            </div>
        </div>

        <!-- Live Clock & Date Badge -->
        <div class="flex items-center gap-3 self-end sm:self-auto bg-white px-4 py-2.5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-right">
                <div class="text-base font-bold text-slate-900 font-mono tracking-tight" x-text="currentTime + ' WIB'"></div>
                <div class="text-[11px] text-slate-500 font-medium" x-text="currentDate"></div>
            </div>
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
        </div>
    </header>

    <!-- Main Interactive Grid -->
    <main class="my-auto py-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start max-w-7xl w-full mx-auto">
        <!-- Left: Form Check-in -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Presensi Mandiri Siswa</h2>
                <p class="text-xs text-slate-500 mt-1">Masukkan email & kata sandi akun untuk mencatat kehadiran secara sah.</p>
            </div>

            <form @submit.prevent="submitCheckIn()" class="space-y-4">
                <!-- Email Input -->
                <div>
                    <label for="kiosk-email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email Akun Siswa
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input type="email"
                               id="kiosk-email"
                               name="email"
                               x-ref="emailInput"
                               x-model="email"
                               required
                               placeholder="nama@siswa.perpusdupat.sch.id"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition bg-slate-50/70 hover:bg-white focus:bg-white">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="kiosk-password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi Akun
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'"
                               id="kiosk-password"
                               name="password"
                               x-ref="passwordInput"
                               x-model="password"
                               required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-[#1a4f8b]/20 focus:border-[#1a4f8b] outline-none transition bg-slate-50/70 hover:bg-white focus:bg-white">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Purpose Selector (2 Pilihan) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Tujuan Kunjungan
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button"
                                @click="setPurpose('Presensi')"
                                :class="purpose === 'Presensi' ? 'border-[#1a4f8b] bg-blue-50/80 ring-2 ring-[#1a4f8b]/20 text-[#1a4f8b]' : 'border-slate-200 bg-slate-50/70 text-slate-700 hover:bg-white'"
                                class="p-3 rounded-xl border text-left transition flex items-center justify-between group active:scale-[0.98]">
                            <div>
                                <div class="text-xs font-bold leading-tight">Presensi Kehadiran</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Baca di perpustakaan</div>
                            </div>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                 :class="purpose === 'Presensi' ? 'border-[#1a4f8b] bg-[#1a4f8b] text-white' : 'border-slate-300'">
                                <svg x-show="purpose === 'Presensi'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </button>

                        <button type="button"
                                @click="setPurpose('Pinjam Buku')"
                                :class="purpose === 'Pinjam Buku' ? 'border-[#1a4f8b] bg-blue-50/80 ring-2 ring-[#1a4f8b]/20 text-[#1a4f8b]' : 'border-slate-200 bg-slate-50/70 text-slate-700 hover:bg-white'"
                                class="p-3 rounded-xl border text-left transition flex items-center justify-between group active:scale-[0.98]">
                            <div>
                                <div class="text-xs font-bold leading-tight">Pinjam Buku</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Buka katalog langsung</div>
                            </div>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                 :class="purpose === 'Pinjam Buku' ? 'border-[#1a4f8b] bg-[#1a4f8b] text-white' : 'border-slate-300'">
                                <svg x-show="purpose === 'Pinjam Buku'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="w-full py-3.5 px-6 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] active:scale-[0.99] disabled:opacity-70 text-white font-semibold text-sm shadow-md shadow-blue-900/15 transition flex items-center justify-center gap-2.5">
                    <span x-show="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Memverifikasi...' : 'Konfirmasi Kehadiran Sekarang'"></span>
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Gunakan akun yang sama dengan Portal Siswa.
            </div>
        </div>

        <!-- Right: Real-time Visitor Stream -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Antrean Kunjungan Terkini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar kehadiran siswa hari ini secara real-time</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total Hari Ini</span>
                    <div class="text-xl font-bold text-[#1a4f8b]" x-text="totalToday + ' Siswa'"></div>
                </div>
            </div>

            <!-- List of visitors -->
            <div class="divide-y divide-slate-100 max-h-[380px] overflow-y-auto pr-1">
                <template x-for="item in recentVisits" :key="item.id">
                    <div class="py-3 flex items-center justify-between hover:bg-slate-50 rounded-xl px-2 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1a4f8b] font-bold text-xs uppercase flex items-center justify-center border border-blue-100 shrink-0">
                                <span x-text="item.name ? item.name.substring(0, 2) : 'S'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-400 font-mono">NIS: <span x-text="item.nis"></span></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border"
                                  :class="item.purpose && item.purpose.toLowerCase().includes('pinjam') ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
                                  x-text="item.purpose"></span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="item.time + ' WIB'"></div>
                        </div>
                    </div>
                </template>

                <template x-if="recentVisits.length === 0">
                    <div class="py-12 text-center text-xs text-slate-400">
                        Belum ada siswa yang presensi hari ini. Jadilah yang pertama!
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- Success Confirmation Modal -->
    <div x-cloak x-show="showSuccessModal"
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

            <h3 class="text-xl font-bold text-slate-900 tracking-tight" x-text="successStudent ? 'Selamat Datang, ' + successStudent.name + '!' : 'Selamat Datang!'"></h3>
            <p class="text-xs text-slate-500 mt-1">Kehadiran fisik Anda di perpustakaan telah sah dicatat dalam log kunjungan.</p>

            <!-- Visitor Details Card -->
            <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left space-y-2 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500">Nomor Induk Siswa (NIS)</span>
                    <span class="font-mono font-semibold text-slate-900" x-text="successStudent?.nis"></span>
                </div>
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500">Aktivitas Dipilih</span>
                    <span class="font-semibold text-[#1a4f8b]" x-text="successStudent?.purpose"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Waktu Presensi</span>
                    <span class="font-mono font-medium text-slate-700" x-text="(successStudent?.time || '') + ' WIB'"></span>
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
    <div x-cloak x-show="$store.toast && $store.toast.show" x-transition class="fixed top-8 left-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4">
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
    <footer class="border-t border-slate-200 pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
        <div>
            Stasiun Kiosk Mandiri &bull; Perpustakaan Digital SMA Negeri 24 Bandung
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-slate-200 text-slate-700 rounded border border-slate-300 text-[10px] font-mono">F11</kbd> untuk Layar Penuh
        </div>
    </footer>
</body>
</html>
