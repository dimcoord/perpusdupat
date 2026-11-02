<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kiosk Mandiri & Katalog Buku - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none bg-slate-100/60"
      x-data="{
        nis: '',
        purpose: 'Membaca',
        isLoading: false,
        totalToday: {{ $visitCountToday }},
        lastCheckIn: null,
        allBooks: {{ json_encode($availableBooks) }},
        searchQuery: '',
        selectedCategory: '',
        selectedBook: null,
        showBookModal: false,
        currentTime: '',
        currentDate: '',

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => {
                this.$refs.nisInput?.focus();
            });
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        setPurpose(p) {
            this.purpose = p;
            this.$refs.nisInput?.focus();
        },

        get filteredBooks() {
            let q = this.searchQuery.toLowerCase().trim();
            return this.allBooks.filter(b => {
                const matchesCat = !this.selectedCategory || b.category_code === this.selectedCategory;
                if (!matchesCat) return false;
                if (!q) return true;
                return (b.title && b.title.toLowerCase().includes(q))
                    || (b.author && b.author.toLowerCase().includes(q))
                    || (b.rack_location && b.rack_location.toLowerCase().includes(q))
                    || (b.isbn && b.isbn.toLowerCase().includes(q))
                    || (b.category_name && b.category_name.toLowerCase().includes(q));
            });
        },

        openBookModal(book) {
            this.selectedBook = book;
            this.showBookModal = true;
        },

        submitCheckIn() {
            if (!this.nis.trim()) {
                Alpine.store('toast').notify('Harap masukkan nomor NIS Anda.', 'warning');
                this.$refs.nisInput?.focus();
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
                    this.lastCheckIn = {
                        name: data.data.name,
                        nis: data.data.nis,
                        time: data.data.time,
                        purpose: data.data.purpose
                    };
                    this.totalToday = data.data.total_today;
                    this.nis = '';
                } else {
                    Alpine.store('toast').notify(data.message || 'NIS tidak ditemukan.', 'error');
                }
                this.$refs.nisInput?.focus();
            })
            .catch(() => {
                this.isLoading = false;
                Alpine.store('toast').notify('Gagal menghubungi server LAN perpustakaan.', 'error');
                this.$refs.nisInput?.focus();
            });
        }
      }">

    <!-- Top Header -->
    <header class="flex items-center justify-between border-b border-slate-200/90 pb-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                    PERPUS DUPAT
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/80">
                        KIOSK MANDIRI
                    </span>
                </h1>
                <p class="text-xs text-slate-500">Stasiun Presensi Cepat &amp; Katalog Buku Siap Pinjam (LAN Station)</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Clock -->
            <div class="text-right hidden sm:block">
                <div class="text-2xl font-mono font-bold text-slate-900 tracking-wider" x-text="currentTime"></div>
                <div class="text-[11px] text-slate-500 font-medium" x-text="currentDate"></div>
            </div>

            <!-- Portal Login Link -->
            <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-300 shadow-xs text-xs font-semibold transition flex items-center gap-2">
                <span>Masuk Portal Siswa</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="my-auto py-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start max-w-7xl w-full mx-auto">
        <!-- Left: Check-in Box (col-span-5) -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl shadow-slate-200/40 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Presensi Kehadiran</h2>
                    <p class="text-xs text-slate-500 mt-1">Scan kartu atau ketik Nomor Induk Siswa (NIS), lalu Enter.</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Hari Ini</span>
                    <span class="text-base font-bold font-mono text-emerald-600" x-text="totalToday + ' Siswa'"></span>
                </div>
            </div>

            <!-- NIS Form -->
            <form @submit.prevent="submitCheckIn()" class="space-y-5">
                <div>
                    <label for="kiosk-nis" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
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
                               class="w-full px-5 py-4 rounded-2xl bg-slate-50/80 border-2 border-slate-300 text-xl font-mono text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/15 focus:outline-none transition shadow-inner">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-xs">
                            [Enter]
                        </div>
                    </div>
                </div>

                <!-- Purpose Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
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
                                    :class="purpose === '{{ $val }}' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100 hover:text-slate-900'"
                                    class="py-2.5 px-2 rounded-xl border text-xs font-bold transition text-center focus:outline-none">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        :disabled="isLoading"
                        class="w-full py-4 px-6 rounded-2xl bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white font-bold text-base shadow-lg shadow-blue-600/20 transition flex items-center justify-center gap-3">
                    <span x-show="isLoading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isLoading ? 'Mencatat Kehadiran...' : 'Konfirmasi Kehadiran (Check-In)'"></span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Last Check-in Notification Banner -->
            <template x-if="lastCheckIn">
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs flex items-center gap-3 transition">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-emerald-900 font-bold" x-text="'Presensi Berhasil: ' + lastCheckIn.name"></div>
                        <div class="text-[11px] text-emerald-700 font-medium" x-text="'NIS: ' + lastCheckIn.nis + ' &bull; ' + lastCheckIn.time + ' WIB (' + lastCheckIn.purpose + ')'"></div>
                    </div>
                </div>
            </template>

            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Demo NIS: <code class="text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-mono">2024101</code>, <code class="text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-mono">2024102</code></span>
                <span class="text-slate-400">Subnet LAN Mode</span>
            </div>
        </div>

        <!-- Right: Catalog of Books Available to Borrow (col-span-7) -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xl shadow-slate-200/40 space-y-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">Katalog Buku Siap Dipinjam</h3>
                    <p class="text-xs text-slate-500">Pilih judul buku untuk melihat nomor rak dan ketersediaan stok</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[11px] text-slate-400 uppercase font-semibold block">Tersedia</span>
                    <span class="text-base font-bold font-mono text-emerald-600" x-text="filteredBooks.length + ' Judul'"></span>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="space-y-2.5">
                <!-- Search input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari judul buku, penulis, lokasi rak, atau ISBN..."
                           class="w-full pl-10 pr-9 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                    <button type="button"
                            x-show="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-base">
                        &times;
                    </button>
                </div>

                <!-- Category Filter Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px] scrollbar-thin">
                    <button type="button"
                            @click="selectedCategory = ''"
                            :class="selectedCategory === '' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 border border-slate-200/60'"
                            class="px-3 py-1 rounded-lg shrink-0 transition">
                        Semua Kategori
                    </button>
                    @foreach($categories as $cat)
                        <button type="button"
                                @click="selectedCategory = '{{ $cat->code }}'"
                                :class="selectedCategory === '{{ $cat->code }}' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80 border border-slate-200/60'"
                                class="px-2.5 py-1 rounded-lg shrink-0 transition">
                            {{ $cat->name }} ({{ $cat->code }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Scrollable Book List -->
            <div class="divide-y divide-slate-100 max-h-[390px] overflow-y-auto pr-1">
                <template x-for="book in filteredBooks" :key="book.id">
                    <div @click="openBookModal(book)"
                         class="py-3 px-2 rounded-2xl hover:bg-slate-50/80 border border-transparent hover:border-slate-200/80 transition cursor-pointer flex items-center justify-between gap-3 group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Book Cover -->
                            <template x-if="book.cover_url">
                                <img :src="book.cover_url" :alt="book.title" class="w-11 h-15 rounded-lg object-cover border border-slate-200 shadow-2xs shrink-0">
                            </template>
                            <template x-if="!book.cover_url">
                                <div class="w-11 h-15 rounded-lg bg-blue-50 border border-blue-200 flex flex-col items-center justify-center text-blue-700 shrink-0 shadow-2xs p-1 text-center">
                                    <span class="font-bold text-[8px] uppercase tracking-wider" x-text="book.category_code"></span>
                                    <span class="text-[7px] text-blue-500 font-mono mt-0.5">BUKU</span>
                                </div>
                            </template>

                            <!-- Book Information -->
                            <div class="min-w-0 space-y-1">
                                <div class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition truncate" x-text="book.title"></div>
                                <div class="text-[11px] text-slate-500 truncate">
                                    <span x-text="book.author"></span> &bull; <span x-text="book.publication_year"></span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100" x-text="book.category_name"></span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        <span x-text="'Rak: ' + book.rack_location"></span>
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="'Tersedia: ' + book.available_stock + ' eks'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Arrow / Detail Action -->
                        <div class="shrink-0 text-right">
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 group-hover:bg-blue-600 text-slate-700 group-hover:text-white text-[11px] font-semibold transition inline-flex items-center gap-1 shadow-2xs">
                                Info Rak
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="filteredBooks.length === 0">
                    <div class="py-12 text-center text-xs text-slate-500 space-y-2">
                        <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <p>Tidak ada buku yang cocok dengan kata kunci atau filter kategori.</p>
                        <button type="button" @click="searchQuery = ''; selectedCategory = ''" class="text-blue-600 font-semibold hover:underline">
                            Reset Filter Pencarian
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </main>

    <!-- Book Detail Modal (Alpine.js) -->
    <div x-cloak x-show="showBookModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showBookModal = false" class="bg-white border border-slate-200/90 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl text-slate-800 space-y-5">
            <template x-if="selectedBook">
                <div>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-4">
                            <template x-if="selectedBook.cover_url">
                                <img :src="selectedBook.cover_url" :alt="selectedBook.title" class="w-16 h-22 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                            </template>
                            <template x-if="!selectedBook.cover_url">
                                <div class="w-16 h-22 rounded-xl bg-blue-50 border border-blue-200 flex flex-col items-center justify-center text-blue-700 shrink-0 font-bold text-xs uppercase p-2 text-center">
                                    <span x-text="selectedBook.category_code"></span>
                                    <span class="text-[9px] text-blue-500 font-mono mt-1">BUKU</span>
                                </div>
                            </template>
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200" x-text="selectedBook.category_name"></span>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-1 leading-snug" x-text="selectedBook.title"></h3>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="'Penulis: ' + selectedBook.author"></p>
                            </div>
                        </div>
                        <button @click="showBookModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none p-1">&times;</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs my-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <div>
                            <span class="text-slate-500 block text-[11px]">Lokasi Rak Fisik:</span>
                            <span class="font-bold text-amber-800 font-mono text-sm" x-text="selectedBook.rack_location"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Stok Tersedia:</span>
                            <span class="font-bold text-emerald-700 font-mono text-sm" x-text="selectedBook.available_stock + ' dari ' + selectedBook.total_stock + ' Eks'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nomor ISBN:</span>
                            <span class="font-mono text-slate-700" x-text="selectedBook.isbn"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Penerbit &amp; Tahun:</span>
                            <span class="text-slate-700" x-text="selectedBook.publisher + ' (' + selectedBook.publication_year + ')'"></span>
                        </div>
                    </div>

                    <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-4 text-xs text-slate-700 space-y-1">
                        <div class="font-bold text-blue-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Petunjuk Peminjaman:
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            1. Silakan ambil buku fisik di rak <strong class="text-amber-800 font-semibold" x-text="selectedBook.rack_location"></strong>.<br>
                            2. Bawa buku ke meja <strong>Pustakawan</strong> untuk dicatat peminjamannya, atau ajukan secara mandiri melalui <strong>Portal Siswa</strong>.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showBookModal = false" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Global Toast for Kiosk Notifications -->
    <div x-cloak x-show="$store.toast.show" x-transition class="fixed top-8 left-1/2 -translate-x-1/2 z-50 max-w-md w-full px-4">
        <div class="rounded-2xl shadow-xl p-4 flex items-center justify-between text-white border"
             :class="{
                'bg-emerald-600 border-emerald-500': $store.toast.type === 'success',
                'bg-rose-600 border-rose-500': $store.toast.type === 'error',
                'bg-amber-600 border-amber-500': $store.toast.type === 'warning',
                'bg-blue-600 border-blue-500': $store.toast.type === 'info'
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
    <footer class="border-t border-slate-200/80 pt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
        <div>
            Stasiun Kiosk Mandiri &bull; Perpustakaan Digital SMA/SMK Negeri &bull; Jalur Subnet LAN
        </div>
        <div>
            Tekan <kbd class="px-1.5 py-0.5 bg-white text-slate-700 rounded border border-slate-300 shadow-2xs text-[10px] font-mono">F11</kbd> untuk Tampilan Layar Penuh (Kiosk Mode)
        </div>
    </footer>
</body>
</html>
