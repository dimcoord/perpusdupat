<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF8F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Presensi Mandiri - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|newsreader:400,500,600,700|lora:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-stone-800 bg-[#FAF8F5] flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none"
      x-data="{
        email: '',
        password: '',
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

        submitCheckIn() {
            if (!this.email.trim() || !this.password) {
                Alpine.store('toast').notify('Harap masukkan email dan kata sandi Anda.', 'warning');
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
                // Selalu kosongkan password setelah percobaan
                this.password = '';
                if (data.success) {
                    Alpine.store('toast').notify(data.message, 'success');

                    // Prepend to live visitor queue
                    this.recentVisits.unshift(data.data);
                    if (this.recentVisits.length > 15) {
                        this.recentVisits.pop();
                    }
                    this.totalToday = data.data.total_today;
                    this.email = '';

                    // Pinjam Buku: arahkan ke katalog buku setelah toast sempat terbaca
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 1200);
                        return;
                    }

                    this.$refs.emailInput.focus();
                } else {
                    Alpine.store('toast').notify(data.message || 'Email atau kata sandi salah.', 'error');
                    this.$refs.passwordInput.focus();
                }
            })
            .catch(() => {
                this.isLoading = false;
                this.password = '';
                Alpine.store('toast').notify('Gagal menghubungi server LAN perpustakaan.', 'error');
                this.$refs.emailInput.focus();
            });
        }
      }">

    <!-- Top Header (Bookhaven Editorial Palette) -->
    <header class="flex items-center justify-between border-b border-[#E7E3DA] pb-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#183527] border border-[#224A35] flex items-center justify-center font-bold text-[#E5C158] shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <h1 class="font-serif text-xl font-bold tracking-tight text-stone-900 flex items-center gap-2">
                    PERPUS DUPAT
                    <span class="text-xs font-sans font-semibold px-2.5 py-0.5 rounded-full bg-[#EAEFD9] text-[#2B5941] border border-[#C6D8B3]">
                        KIOSK SISWA
                    </span>
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">Stasiun Mandiri Presensi Pengunjung Perpustakaan (Client Node LAN)</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-serif font-bold text-[#183527] tracking-wider" x-text="currentTime"></div>
                <div class="text-[11px] text-stone-500 font-medium" x-text="currentDate"></div>
            </div>

            <!-- Portal Login Link -->
            <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-[#F5F2EB] text-stone-700 hover:text-stone-900 border border-[#E7E3DA] shadow-2xs hover:border-[#D6CEBF] text-xs font-semibold transition flex items-center gap-2">
                <span>Masuk Portal Siswa</span>
                <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="my-auto py-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start max-w-7xl w-full mx-auto">
        <!-- Left: Check-in Box -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-[#E7E3DA] shadow-xs space-y-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EAEFD9] border border-[#C6D8B3] text-[#2B5941] text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-[#2B5941] animate-pulse"></span>
                    Stasiun Presensi Cepat
                </div>
                <h2 class="font-serif text-2xl lg:text-3xl font-bold text-stone-900 tracking-tight">Presensi Kunjungan Anda</h2>
                <p class="text-xs text-stone-500 mt-1">Masukkan email dan kata sandi akun Anda, lalu tekan Enter.</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submitCheckIn()" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="kiosk-email" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-2">
                        Email
                    </label>
                    <input type="email"
                           id="kiosk-email"
                           name="email"
                           x-ref="emailInput"
                           x-model="email"
                           required
                           autocomplete="off"
                           placeholder="nama@sekolah.sch.id"
                           class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F5] border-2 border-[#D6CEBF] text-base text-[#183527] placeholder:text-stone-400 focus:border-[#183527] focus:ring-4 focus:ring-[#183527]/10 focus:outline-none transition shadow-inner">
                </div>

                <!-- Password -->
                <div>
                    <label for="kiosk-password" class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-2">
                        Kata Sandi
                    </label>
                    <input type="password"
                           id="kiosk-password"
                           name="password"
                           x-ref="passwordInput"
                           x-model="password"
                           required
                           autocomplete="off"
                           placeholder="••••••••"
                           class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F5] border-2 border-[#D6CEBF] text-base text-[#183527] placeholder:text-stone-400 focus:border-[#183527] focus:ring-4 focus:ring-[#183527]/10 focus:outline-none transition shadow-inner">
                </div>

                <!-- Purpose Selector -->
                <div>
                    <label class="block text-xs font-semibold text-stone-600 uppercase tracking-wider mb-2">
                        Tujuan Kunjungan
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                            $purposes = [
                                'Presensi' => 'Presensi',
                                'Pinjam Buku' => 'Pinjam Buku',
                            ];
                        @endphp
                        @foreach($purposes as $val => $label)
                            <button type="button"
                                    @click="setPurpose('{{ $val }}')"
                                    :class="purpose === '{{ $val }}' ? 'bg-[#183527] text-[#FAF8F5] border-[#183527] shadow-sm' : 'bg-[#FAF8F5] text-stone-600 border-[#E7E3DA] hover:bg-[#F5F2EB] hover:border-[#D6CEBF]'"
                                    class="py-3 px-3 rounded-xl border text-xs font-bold transition text-center focus:outline-none">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="w-full py-4 px-6 rounded-2xl bg-[#183527] hover:bg-[#234A37] active:scale-[0.99] disabled:opacity-70 text-[#FAF8F5] font-serif font-bold text-base shadow-sm hover:shadow transition flex items-center justify-center gap-3">
                    <span x-show="isLoading" class="w-5 h-5 border-2 border-[#E5C158] border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Mencatat Kehadiran...' : 'Konfirmasi Kehadiran (Check-In)'"></span>
                    <svg class="w-5 h-5 text-[#E5C158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <div class="pt-4 border-t border-[#F0ECE1] text-[11px] text-stone-500 flex items-center justify-between">
                <span>Gunakan akun yang sama dengan Portal Siswa.</span>
                <span class="text-stone-400">Subnet LAN Mode</span>
            </div>
        </div>

        <!-- Right: Real-time Visitor Stream -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 border border-[#E7E3DA] shadow-xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#F0ECE1]">
                <div>
                    <h3 class="font-serif text-base font-bold text-stone-900">Antrean Kunjungan Terkini</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Daftar kehadiran hari ini secara real-time</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-stone-400 uppercase font-semibold">Total Hari Ini</span>
                    <div class="font-serif text-2xl font-bold text-[#183527]" x-text="totalToday + ' Siswa'"></div>
                </div>
            </div>

            <!-- List of visitors -->
            <div class="divide-y divide-[#F0ECE1] max-h-[380px] overflow-y-auto pr-1">
                <template x-for="item in recentVisits" :key="item.id">
                    <div class="py-3 flex items-center justify-between hover:bg-[#FAF8F5] rounded-xl px-2 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#EAEFD9] text-[#2B5941] flex items-center justify-center font-bold text-xs uppercase border border-[#C6D8B3]">
                                <span x-text="item.name ? item.name.substring(0, 2) : 'S'"></span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-stone-900" x-text="item.name"></div>
                                <div class="text-[11px] text-stone-500">NIS: <span class="font-mono text-stone-600" x-text="item.nis"></span></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-medium border"
                                  :class="{
                                      'bg-[#FDF4E7] text-[#9A6A1E] border-[#F3DFC1]': item.purpose && item.purpose.toLowerCase().includes('pinjam'),
                                      'bg-[#EAEFD9] text-[#2B5941] border-[#C6D8B3]': item.purpose && item.purpose.toLowerCase().includes('presensi'),
                                      'bg-[#F0ECE1] text-stone-700 border-[#DDD5C7]': !item.purpose || (!item.purpose.toLowerCase().includes('pinjam') && !item.purpose.toLowerCase().includes('presensi'))
                                  }"
                                  x-text="item.purpose"></span>
                            <div class="text-[10px] text-stone-400 font-mono mt-0.5" x-text="item.time + ' WIB'"></div>
                        </div>
                    </div>
                </template>

                <template x-if="recentVisits.length === 0">
                    <div class="py-12 text-center text-xs text-stone-500">
                        Belum ada siswa yang presensi hari ini. Jadilah yang pertama!
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- Global Toast for Kiosk Notifications -->
    <div x-cloak x-show="$store.toast.show" x-transition class="fixed top-8 left-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4">
        <div class="rounded-2xl shadow-xl p-4 flex items-center justify-between text-white border"
             :class="{
                'bg-[#2B5941] border-[#1F4531]': $store.toast.type === 'success',
                'bg-[#B84233] border-[#9A3528]': $store.toast.type === 'error',
                'bg-[#9A6A1E] border-[#7F5413]': $store.toast.type === 'warning',
                'bg-[#183527] border-[#10241A]': $store.toast.type === 'info'
             }">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-bold" x-text="$store.toast.message"></div>
            </div>
            <button @click="$store.toast.close()" class="p-1 rounded-lg text-white/80 hover:text-white">&times;</button>
        </div>
    </div>

    <!-- Kiosk Footer -->
    <footer class="border-t border-[#E7E3DA] pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-2">
        <div>
            Stasiun Kiosk Mandiri &bull; Perpustakaan Digital SMA/SMK Negeri &bull; Jalur Subnet LAN
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-[#F0ECE1] text-stone-700 rounded border border-[#E7E3DA] text-[10px] font-mono">F11</kbd> untuk Tampilan Layar Penuh (Kiosk Mode)
        </div>
    </footer>
</body>
</html>
