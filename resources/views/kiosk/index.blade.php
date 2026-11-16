<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Presensi Mandiri - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-[#f8fafc] flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none"
      x-data="{
        nis: '',
        purpose: 'Membaca',
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
                    purpose: this.purpose
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isLoading = false;
                if (data.success) {
                    Alpine.store('toast').notify(data.message, 'success');
                    
                    // Prepend to live visitor queue
                    this.recentVisits.unshift(data.data);
                    if (this.recentVisits.length > 15) {
                        this.recentVisits.pop();
                    }
                    this.totalToday = data.data.total_today;
                    this.nis = '';
                } else {
                    Alpine.store('toast').notify(data.message || 'NIS tidak ditemukan.', 'error');
                }
                this.$refs.nisInput.focus();
            })
            .catch(() => {
                this.isLoading = false;
                Alpine.store('toast').notify('Gagal menghubungi server LAN perpustakaan.', 'error');
                this.$refs.nisInput.focus();
            });
        }
      }">

    <!-- Top Header with Scroll-Reveal -->
    <header class="flex items-center justify-between border-b border-[#e2e8f0] pb-4 scroll-reveal">
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
            <div>
                <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                    Stasiun Presensi Cepat
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi Kunjungan Anda</h2>
                <p class="text-xs text-gray-500 mt-1">Ketikkan Nomor Induk Siswa (NIS) atau pindai barcode kartu perpustakaan Anda.</p>
            </div>

            <!-- NIS Form -->
            <form @submit.prevent="submitCheckIn()" class="space-y-4">
                <div>
                    <label for="kiosk-nis" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Nomor Induk Siswa (NIS)
                    </label>
                    <div class="relative">
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
                        </div>
                    </div>
                </div>

                <!-- Purpose Selector -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Tujuan Kunjungan
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @php
                            $purposes = [
                                'Membaca' => 'Membaca',
                                'Meminjam' => 'Pinjam / Balik',
                                'Tugas' => 'Tugas',
                                'Diskusi' => 'Diskusi',
                            ];
                        @endphp
                        @foreach($purposes as $val => $label)
                            <button type="button"
                                    @click="setPurpose('{{ $val }}')"
                                    :class="purpose === '{{ $val }}' ? 'bg-[#1a4f8b] text-white shadow-xs' : 'bg-[#e2e8f0]/60 text-gray-700 hover:bg-[#e2e8f0]'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-medium transition text-center focus:outline-none hover:-translate-y-0.5 active:translate-y-0">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="btn-press btn-sheen w-full py-3 px-6 rounded-xl bg-gradient-to-r from-[#1a4f8b] to-[#2563eb] hover:from-[#153e75] hover:to-[#1d4ed8] active:scale-[0.99] text-white font-medium text-sm transition flex items-center justify-center gap-2 shadow-md shadow-blue-900/15">
                    <span x-show="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Mencatat Kehadiran...' : 'Konfirmasi Kehadiran (Check-In)'"></span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <div class="pt-3 border-t border-[#e2e8f0] text-[11px] text-gray-500">
                <span>Contoh NIS demo: <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">2024101</code>, <code class="bg-[#e2e8f0]/60 text-gray-700 font-mono px-1.5 py-0.5 rounded">2024102</code></span>
            </div>
        </div>

        <!-- Right: Real-time Visitor Stream with Scroll-Reveal & Card Hover -->
        <div class="scroll-reveal delay-2 lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-[#e2e8f0] space-y-4 shadow-xs card-hover">
            <div class="flex items-center justify-between pb-3 border-b border-[#e2e8f0]">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Antrean Kunjungan Terkini</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar kehadiran hari ini secara real-time</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-gray-400 uppercase font-semibold">Total Hari Ini</span>
                    <div class="text-xl font-bold text-[#1a4f8b] font-sans" x-text="totalToday + ' Siswa'"></div>
                </div>
            </div>

            <!-- List of visitors -->
            <div class="divide-y divide-[#e2e8f0]/60 max-h-[380px] overflow-y-auto pr-1">
                <template x-for="item in recentVisits" :key="item.id">
                    <div class="py-2.5 flex items-center justify-between hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 rounded-lg px-2 transition-all duration-150">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#e2e8f0]/60 text-[#1a4f8b] flex items-center justify-center font-semibold text-xs uppercase border border-[#e2e8f0] shadow-2xs">
                                <span x-text="item.name ? item.name.substring(0, 2) : 'S'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900" x-text="item.name"></div>
                                <div class="text-[11px] text-gray-400 font-mono">NIS: <span class="text-gray-600" x-text="item.nis"></span></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-[#e2e8f0]/70 text-gray-700 border border-[#e2e8f0]"
                                  x-text="item.purpose"></span>
                            <div class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="item.time + ' WIB'"></div>
                        </div>
                    </div>
                </template>

                <template x-if="recentVisits.length === 0">
                    <div class="py-12 text-center text-xs text-gray-400">
                        Belum ada siswa yang presensi hari ini. Jadilah yang pertama!
                    </div>
                </template>
            </div>
        </div>
    </main>

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
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-[#e2e8f0]/60 text-gray-700 rounded border border-[#e2e8f0] text-[10px] font-mono">F11</kbd> untuk Layar Penuh
        </div>
    </footer>
</body>
</html>
