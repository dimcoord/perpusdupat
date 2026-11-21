@extends('layouts.app')

@section('content')
<div class="space-y-8 pb-8" x-data="{
    detailModal: false,
    selectedBook: null,
    confirmedTerms: false,
    isSubmittingLoan: false,

    openBookDetail(book) {
        if (!book) return;
        this.selectedBook = book;
        this.confirmedTerms = false;
        this.detailModal = true;
    },

    submitLoanFromDetail() {
        if (!this.selectedBook) return;
        this.isSubmittingLoan = true;

        fetch('{{ route('loans.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Alpine-Request': 'true'
            },
            body: JSON.stringify({
                book_id: this.selectedBook.id
            })
        })
        .then(res => res.json())
        .then(data => {
            this.isSubmittingLoan = false;
            if (data.success) {
                if (window.Alpine && Alpine.store('toast')) {
                    Alpine.store('toast').notify(data.message, 'success');
                } else {
                    alert(data.message);
                }
                this.detailModal = false;
                setTimeout(() => window.location.reload(), 1200);
            } else {
                if (window.Alpine && Alpine.store('toast')) {
                    Alpine.store('toast').notify(data.message || 'Gagal mengajukan pinjaman', 'error');
                } else {
                    alert(data.message || 'Gagal mengajukan pinjaman');
                }
            }
        })
        .catch(() => {
            this.isSubmittingLoan = false;
            if (window.Alpine && Alpine.store('toast')) {
                Alpine.store('toast').notify('Terjadi kesalahan koneksi', 'error');
            } else {
                alert('Terjadi kesalahan saat memproses pinjaman.');
            }
        });
    }
}">

    <!-- 1. HERO SECTION: Bookstore & Library Showcase (SMAN 24 Identity, Clean without AI/LAN Badges) -->
    <section class="relative rounded-3xl bg-gradient-to-br from-[#0c2347] via-[#153e75] to-[#1e5692] text-white overflow-hidden shadow-xl border border-blue-900/40 scroll-reveal">
        <!-- Decorative Ambient Geometric Wave & Backlight -->
        <div class="absolute inset-0 pointer-events-none opacity-25">
            <svg class="w-full h-full object-cover" viewBox="0 0 900 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="800" cy="80" r="220" fill="url(#heroGlow)" fill-opacity="0.3" />
                <path d="M-50 280C120 180 280 340 450 260C620 180 780 320 950 240" stroke="white" stroke-width="1.5" stroke-dasharray="4 8" stroke-opacity="0.25" />
                <path d="M-50 320C150 220 320 370 500 290C680 210 820 350 950 280" stroke="white" stroke-width="1.2" stroke-opacity="0.15" />
                <defs>
                    <radialGradient id="heroGlow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(800 80) rotate(90) scale(220)">
                        <stop stop-color="#60a5fa" />
                        <stop offset="1" stop-color="#1d4ed8" stop-opacity="0" />
                    </radialGradient>
                </defs>
            </svg>
        </div>

        <div class="relative z-10 p-5 sm:p-8 lg:p-10 grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-8 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-3.5 sm:space-y-5">
                <!-- Big Headline -->
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Inspirasi, Cerita & <span class="text-amber-300 block sm:inline">Literasi Tanpa Batas.</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-blue-100/90 text-xs sm:text-sm lg:text-base leading-relaxed max-w-xl font-normal">
                    Eksplorasi ribuan koleksi buku fisik & digital kurikulum merdeka, sastra fiksi, sains, hingga ensiklopedia untuk menunjang prestasimu di SMAN 24 Bandung.
                </p>

                <!-- Search Bar Directly in Hero (Mobile-friendly) -->
                <form action="{{ route('books.index') }}" method="GET" class="relative max-w-lg pt-0.5">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 sm:pl-3.5 flex items-center pointer-events-none text-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               placeholder="Cari judul buku, pengarang, atau topik..."
                               class="w-full pl-9 sm:pl-10 pr-20 sm:pr-24 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-white/95 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 placeholder:text-[11px] sm:placeholder:text-sm focus:bg-white focus:ring-2 focus:ring-amber-400 focus:outline-none transition-all shadow-md">
                        <button type="submit" 
                                class="btn-sheen absolute right-1.5 top-1.5 bottom-1.5 px-3.5 sm:px-4 rounded-lg sm:rounded-xl bg-[#102a54] hover:bg-[#1a4f8b] text-white text-xs font-bold transition-all flex items-center gap-1 shadow-sm active:scale-95">
                            <span>Cari</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Quick Category Tags (Horizontal scroll on mobile, wrap on desktop) -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px] text-blue-200" style="-ms-overflow-style:none;scrollbar-width:none;">
                    <span class="text-blue-300/80 font-medium shrink-0 text-[10px] sm:text-[11px]">Topik:</span>
                    <a href="{{ route('books.index', ['search' => 'Fiksi']) }}" class="shrink-0 px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition border border-white/10 text-white text-[10px] sm:text-xs">Sastra Fiksi</a>
                    <a href="{{ route('books.index', ['search' => 'Sains']) }}" class="shrink-0 px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition border border-white/10 text-white text-[10px] sm:text-xs">Sains & Matematika</a>
                    <a href="{{ route('books.index', ['search' => 'Sejarah']) }}" class="shrink-0 px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition border border-white/10 text-white text-[10px] sm:text-xs">Sejarah</a>
                    <a href="{{ route('books.index', ['search' => 'Komputer']) }}" class="shrink-0 px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition border border-white/10 text-white text-[10px] sm:text-xs">Teknologi</a>
                </div>

                <!-- Feature Badges with Clean Vector Icons (No Emojis) -->
                <div class="pt-3.5 sm:pt-4 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-xs text-blue-100">
                    <div class="flex items-center gap-2 p-2 sm:p-0 rounded-xl bg-white/5 sm:bg-transparent">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0 text-amber-300">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block font-semibold text-white text-[10px] sm:text-[11px] truncate">1.200+ Judul</span>
                            <span class="block text-[9px] sm:text-[10px] text-blue-200/70 truncate">Kurikulum & Fiksi</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-2 sm:p-0 rounded-xl bg-white/5 sm:bg-transparent">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0 text-amber-300">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block font-semibold text-white text-[10px] sm:text-[11px] truncate">Layanan Cepat</span>
                            <span class="block text-[9px] sm:text-[10px] text-blue-200/70 truncate">Sirkulasi Digital</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-2 sm:p-0 rounded-xl bg-white/5 sm:bg-transparent">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0 text-amber-300">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block font-semibold text-white text-[10px] sm:text-[11px] truncate">Bebas Denda</span>
                            <span class="block text-[9px] sm:text-[10px] text-blue-200/70 truncate">Kembalikan Tepat</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-2 sm:p-0 rounded-xl bg-white/5 sm:bg-transparent">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0 text-amber-300">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <span class="block font-semibold text-white text-[10px] sm:text-[11px] truncate">Presensi Kios</span>
                            <span class="block text-[9px] sm:text-[10px] text-blue-200/70 truncate">Masukkan NIS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual: 3D Book Showcase Card -->
            <div class="lg:col-span-5 flex justify-center lg:justify-end mt-1 lg:mt-0">
                <div class="relative w-full max-w-sm sm:max-w-md lg:max-w-sm bg-gradient-to-b from-white/15 to-white/5 backdrop-blur-xl border border-white/20 p-3.5 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl shadow-xl transition-all duration-300 hover:shadow-blue-500/10">
                    
                    <!-- Spotlight Label with Clean Star Icon -->
                    <div class="flex items-center justify-between mb-2.5 sm:mb-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-amber-400 text-slate-950 font-bold text-[9px] sm:text-[10px] tracking-wide uppercase shadow-sm">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>Rekomendasi Pekan Ini</span>
                        </span>
                        <span class="text-[10px] text-blue-200 font-mono">SMAN 24</span>
                    </div>

                    @php
                        $spotlightBook = $bestReadingBooks->first();
                    @endphp

                    @if($spotlightBook)
                        <!-- Book Visual Stack with Realistic Spine Shadow -->
                        <div @click="openBookDetail({{ json_encode($spotlightBook) }})" class="flex items-center gap-3 sm:gap-4 my-1.5 sm:my-2 cursor-pointer group">
                            <div class="relative shrink-0">
                                @if($spotlightBook->cover_url)
                                    <img src="{{ $spotlightBook->cover_url }}" 
                                         alt="{{ $spotlightBook->title }}" 
                                         class="w-16 sm:w-24 lg:w-28 h-24 sm:h-34 lg:h-40 rounded-xl object-cover shadow-[0_12px_24px_-6px_rgba(0,0,0,0.5)] border border-white/20 group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-16 sm:w-24 lg:w-28 h-24 sm:h-34 lg:h-40 rounded-xl bg-gradient-to-br from-blue-700 to-indigo-900 flex flex-col justify-between p-2 sm:p-2.5 shadow-[0_12px_24px_-6px_rgba(0,0,0,0.5)] border border-white/20 group-hover:scale-105 transition-transform duration-300">
                                        <div class="text-[8px] sm:text-[9px] text-amber-300 font-semibold uppercase tracking-wider">{{ $spotlightBook->category?->name ?? 'Literasi' }}</div>
                                        <div class="text-[10px] sm:text-xs font-bold text-white line-clamp-3 leading-snug">{{ $spotlightBook->title }}</div>
                                        <div class="text-[8px] sm:text-[9px] text-blue-200 truncate">{{ $spotlightBook->author }}</div>
                                    </div>
                                @endif
                                <!-- Spine 3D shadow effect -->
                                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-r from-black/40 to-transparent rounded-l-xl pointer-events-none"></div>
                            </div>

                            <div class="space-y-1 sm:space-y-1.5 min-w-0 flex-1">
                                <span class="inline-block text-[9px] sm:text-[10px] font-semibold text-blue-200 uppercase tracking-wider bg-white/10 px-2 py-0.5 rounded truncate max-w-full">
                                    {{ $spotlightBook->category?->name ?? 'Koleksi Umum' }}
                                </span>
                                <h3 class="text-xs sm:text-base font-bold text-white leading-snug line-clamp-2 group-hover:text-amber-300 transition-colors">
                                    {{ $spotlightBook->title }}
                                </h3>
                                <p class="text-[11px] sm:text-xs text-blue-100/75 truncate">Oleh: {{ $spotlightBook->author ?? 'Penulis' }}</p>
                                <div class="flex items-center gap-2 pt-0.5 text-xs">
                                    <span class="inline-flex items-center gap-1.5 text-emerald-300 font-semibold text-[10px] sm:text-[11px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Tersedia {{ $spotlightBook->available_stock }} eks
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Link -->
                        <div class="mt-2.5 sm:mt-4 pt-2.5 sm:pt-4 border-t border-white/15 flex items-center justify-between">
                            <span class="text-[10px] sm:text-[11px] text-blue-200 font-mono">Rak: {{ $spotlightBook->rack_location ?? '01' }}</span>
                            <button type="button" 
                                    @click="openBookDetail({{ json_encode($spotlightBook) }})" 
                                    class="btn-sheen inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-[11px] sm:text-xs shadow-md transition-all active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Lihat Buku</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- 2. EXPLORE BY COLLECTION / CATEGORIES (Clean Vector Icon Disks, No Emojis) -->
    <section class="space-y-4 scroll-reveal delay-1">
        <!-- Section Divider & Title -->
        <div class="text-center relative py-1 sm:py-2">
            <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-[11px] sm:text-xs font-bold tracking-wider uppercase text-slate-700">
                <svg class="w-3.5 h-3.5 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Eksplorasi Kategori Koleksi</span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-1">Pilih kategori literasi untuk menemukan buku yang kamu butuhkan</p>
        </div>

        <!-- 4 Columns on Mobile (2 rows of 4) for super clean app look, 8 on Desktop -->
        <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-8 gap-2 sm:gap-3">
            @foreach($categories as $cat)
                <a href="{{ route('books.index', ['category_id' => $cat->id]) }}" 
                   class="group flex flex-col items-center p-2 sm:p-3.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 hover:border-[#1a4f8b] hover:shadow-md transition-all duration-200 hover:-translate-y-1 text-center">
                    <!-- Circular Icon Disk with Vector SVGs -->
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50/80 group-hover:bg-[#1a4f8b] border border-blue-100 group-hover:border-[#1a4f8b] flex items-center justify-center text-[#1a4f8b] group-hover:text-white mb-1.5 sm:mb-2.5 shadow-2xs group-hover:scale-105 transition-all duration-200">
                        @if(str_starts_with($cat->code, '0'))
                            <!-- Computer & General -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '1'))
                            <!-- Philosophy / Psychology -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '2'))
                            <!-- Religion / Ethics -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '3'))
                            <!-- Social Sciences -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '4'))
                            <!-- Language / Literature -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '5'))
                            <!-- Pure Science -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '6'))
                            <!-- Applied Technology -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <circle cx="12" cy="12" r="3" stroke-width="1.8" />
                            </svg>
                        @elseif(str_starts_with($cat->code, '7'))
                            <!-- Art & Recreation -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21a4 4 0 01-4-4 5 5 0 015-5 4 4 0 014 4 5 5 0 01-5 5zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        @else
                            <!-- History & Geography -->
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-[10px] sm:text-xs font-semibold text-slate-800 group-hover:text-[#1a4f8b] transition-colors line-clamp-1 leading-tight sm:leading-snug">
                        {{ $cat->name }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] text-slate-400 font-mono mt-0.5">
                        {{ $cat->books_count }} judul
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. BEST READING & BUKU TERPOPULER -->
    <section class="space-y-4 scroll-reveal delay-2">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Buku Pilihan & Best Reading</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-[#1a4f8b] text-xs font-semibold border border-blue-200">Koleksi Terpopuler</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Rekomendasi buku paling diminati oleh siswa SMA Negeri 24 Bandung</p>
            </div>
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#1a4f8b] hover:underline transition">
                <span>Lihat Semua Katalog</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <!-- Responsive Book Grid (2 columns on mobile, 3 on tablet, 6 on desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-4">
            @forelse($bestReadingBooks as $book)
                <div @click="openBookDetail({{ json_encode($book) }})" 
                     class="group bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg hover:border-[#1a4f8b]/40 transition-all duration-200 flex flex-col justify-between hover:-translate-y-1 cursor-pointer">
                    <div>
                        <!-- Book Cover with Book Spine Feel -->
                        <div class="relative aspect-[3/4] bg-slate-100 overflow-hidden">
                            @if($book->cover_url)
                                <img src="{{ $book->cover_url }}" 
                                     alt="{{ $book->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 via-[#1a4f8b] to-slate-900 p-2.5 sm:p-3 flex flex-col justify-between text-white">
                                    <span class="text-[8px] sm:text-[9px] font-mono text-amber-300 uppercase tracking-wider">{{ $book->category?->code ?? 'DUPAT' }}</span>
                                    <div class="font-bold text-[11px] sm:text-xs line-clamp-3 leading-snug">{{ $book->title }}</div>
                                    <div class="text-[8px] sm:text-[9px] text-slate-300 truncate">{{ $book->author }}</div>
                                </div>
                            @endif

                            <!-- Spine gradient on cover -->
                            <div class="absolute inset-y-0 left-0 w-2.5 bg-gradient-to-r from-black/40 to-transparent pointer-events-none"></div>

                            <!-- Availability Badge -->
                            <div class="absolute top-2 right-2">
                                @if($book->available_stock > 0)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-600/90 backdrop-blur-xs text-white text-[9px] sm:text-[10px] font-semibold shadow-xs">
                                        {{ $book->available_stock }} Eks
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-rose-600/90 backdrop-blur-xs text-white text-[9px] sm:text-[10px] font-semibold shadow-xs">
                                        Habis
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Book Meta Info -->
                        <div class="p-2.5 sm:p-3">
                            <span class="text-[9px] sm:text-[10px] text-[#1a4f8b] font-semibold block uppercase tracking-wider line-clamp-1">
                                {{ $book->category?->name ?? 'Umum' }}
                            </span>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-[#1a4f8b] transition-colors line-clamp-2 leading-tight sm:leading-snug mt-0.5 sm:mt-1">
                                {{ $book->title }}
                            </h3>
                            <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 sm:mt-1 line-clamp-1">
                                {{ $book->author ?? 'Anonim' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="p-2.5 sm:p-3 pt-0 flex items-center gap-1 sm:gap-1.5" @click.stop>
                        <button type="button" 
                                @click="$store.cart.add({{ json_encode($book) }})" 
                                :class="$store.cart.has({{ $book->id }}) ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-50 hover:bg-blue-50 text-slate-700 border-slate-200'"
                                class="btn-press p-1.5 rounded-lg sm:rounded-xl border font-medium text-xs transition shadow-2xs shrink-0" 
                                title="Tambah ke Keranjang Pinjam">
                            <template x-if="!$store.cart.has({{ $book->id }})">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </template>
                            <template x-if="$store.cart.has({{ $book->id }})">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </button>
                        <button type="button" 
                                @click="openBookDetail({{ json_encode($book) }})" 
                                class="flex-1 py-1.5 px-2 sm:px-2.5 rounded-lg sm:rounded-xl bg-slate-50 hover:bg-[#1a4f8b] text-slate-700 hover:text-white border border-slate-200 hover:border-[#1a4f8b] font-medium text-xs transition-all flex items-center justify-center gap-1 text-center active:scale-95 shadow-2xs truncate">
                            <span>Detail</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-slate-200">
                    <p class="text-sm text-slate-500">Belum ada buku dalam katalog rekomendasi.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- 4. ACTIVE STUDENT STATUS & ACTIVE BORROWINGS (Clean Professional Icons, No Emojis) -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 scroll-reveal delay-3">
        
        <!-- Left: Quick Stats Summary Cards -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Student Profile Greeting Card -->
            <div class="bg-gradient-to-br from-[#102a54] to-[#1a4f8b] text-white p-5 rounded-2xl shadow-sm border border-blue-900/30">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white font-bold text-base shadow-inner">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <span class="text-[11px] text-blue-200 block font-medium">Sahabat Dupat</span>
                        <h3 class="font-bold text-sm text-white leading-tight truncate">{{ auth()->user()->name }}</h3>
                        <span class="text-[10px] text-blue-200/80 font-mono">NIS: {{ auth()->user()->nis_nip }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/15 grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 rounded-xl bg-white/10">
                        <span class="block text-lg font-bold text-amber-300 leading-none">{{ $stats['active_loans'] }}</span>
                        <span class="text-[10px] text-blue-100 mt-1 block">Dipinjam</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white/10">
                        <span class="block text-lg font-bold text-emerald-300 leading-none">{{ $stats['completed_loans'] }}</span>
                        <span class="text-[10px] text-blue-100 mt-1 block">Selesai</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white/10">
                        <span class="block text-lg font-bold text-sky-300 leading-none">{{ $stats['total_visits'] }}</span>
                        <span class="text-[10px] text-blue-100 mt-1 block">Kunjungan</span>
                    </div>
                </div>
            </div>

            <!-- Library Information / Policies -->
            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 space-y-3 shadow-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.746-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <span>Ketentuan & Informasi Layanan</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-mono">Pedoman</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50/50 transition">
                        <span class="font-semibold text-slate-900 block text-[11px]">Bebas Denda Buku</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kembalikan buku sebelum tanggal jatuh tempo agar teman lain dapat membaca.</p>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-50/50 transition">
                        <span class="font-semibold text-slate-900 block text-[11px]">Jam Layanan Perpustakaan</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ruang baca terbuka setiap hari sekolah pukul 07.30 s.d. 15.30 WIB.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Active Loans List with Due Date Alerts -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">Pinjaman Aktif Anda</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar buku yang sedang Anda bawa dari perpustakaan</p>
                </div>
                <a href="{{ route('loans.index') }}" class="text-xs font-semibold text-[#1a4f8b] hover:underline flex items-center gap-1">
                    <span>Semua Pinjaman</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($myActiveLoans as $loan)
                    @php
                        $isOverdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                    @endphp
                    <div @if($loan->book) @click="openBookDetail({{ json_encode($loan->book) }})" @endif 
                         class="p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 hover:bg-slate-50/70 transition-colors cursor-pointer group">
                        <div class="flex items-start gap-3 sm:gap-3.5 min-w-0">
                            @if($loan->book?->cover_url)
                                <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->title }}" class="w-11 sm:w-12 h-14 sm:h-16 rounded-xl object-cover border border-slate-200 shrink-0 shadow-xs">
                            @else
                                <div class="w-11 sm:w-12 h-14 sm:h-16 rounded-xl bg-[#102a54] text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 sm:w-6 h-5 sm:h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <span class="text-[10px] font-semibold text-[#1a4f8b] uppercase tracking-wide">
                                    Rak: {{ $loan->book?->rack_location ?? '01' }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug truncate">
                                    {{ $loan->book?->title ?? '-' }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5 truncate">
                                    {{ $loan->book?->author ?? 'Penulis' }} &bull; ISBN: {{ $loan->book?->isbn }}
                                </p>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-1.5 border-t sm:border-t-0 pt-2.5 sm:pt-0 border-slate-100">
                            @if($loan->status === 'menunggu_persetujuan')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Menunggu Persetujuan</span>
                                </span>
                            @elseif($isOverdue)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Terlambat (Tempo: {{ \Carbon\Carbon::parse($loan->due_date)->format('d M') }})</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Dipinjam (Tempo: {{ \Carbon\Carbon::parse($loan->due_date)->format('d M') }})</span>
                                </span>
                            @endif
                            <span class="text-[10px] text-slate-400 font-mono">Tgl Pinjam: {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1a4f8b] flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">Tidak ada buku yang sedang dipinjam saat ini.</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Ingin membaca buku seru? Pilih dari rekomendasi buku di atas dan ajukan pinjaman sekarang.</p>
                        <a href="{{ route('books.index') }}" class="btn-press btn-sheen inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs mt-4 shadow-sm transition active:scale-95">
                            <span>Jelajah Katalog Buku</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    </section>

    <!-- 5. MODAL DETAIL INFORMASI BUKU (Kompak & Tidak Full Layar) -->
    <div x-cloak 
         x-show="detailModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="detailModal = false"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6">
        
        <div @click.outside="detailModal = false" 
             x-show="detailModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-3 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-3 sm:scale-95"
             class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200/90 overflow-hidden flex flex-col max-h-[88vh] sm:max-h-[82vh]">
            
            <template x-if="selectedBook">
                <div class="flex flex-col h-full overflow-hidden">
                    <!-- Modal Header (Compact) -->
                    <div class="px-3.5 sm:px-4 py-2.5 sm:py-3 bg-gradient-to-r from-[#102a54] to-[#1a4f8b] text-white flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span class="text-xs font-semibold tracking-wide text-blue-100 uppercase">Detail Informasi Buku</span>
                        </div>
                        <button type="button" 
                                @click="detailModal = false" 
                                class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition active:scale-95 text-base leading-none"
                                title="Tutup (Esc)">
                            &times;
                        </button>
                    </div>

                    <!-- Modal Body (Compact Scrollable) -->
                    <div class="p-3.5 sm:p-5 overflow-y-auto space-y-3 sm:space-y-4 flex-1 text-slate-800">
                        
                        <!-- Top Split: Cover and Book Identity -->
                        <div class="flex gap-3 sm:gap-4 items-start">
                            <!-- Compact Book Cover -->
                            <div class="relative shrink-0 w-20 sm:w-26 aspect-[3/4.2] rounded-xl overflow-hidden shadow-md border border-slate-200 bg-slate-100">
                                <template x-if="selectedBook.cover_url">
                                    <img :src="selectedBook.cover_url" 
                                         :alt="selectedBook.title" 
                                         class="w-full h-full object-cover">
                                </template>
                                <template x-if="!selectedBook.cover_url">
                                    <div class="w-full h-full bg-gradient-to-br from-[#102a54] via-[#1a4f8b] to-[#153e75] p-2.5 flex flex-col justify-between text-white">
                                        <div class="text-[9px] font-mono text-amber-300 font-bold uppercase tracking-wider" x-text="selectedBook.category ? selectedBook.category.code : 'DUPAT'"></div>
                                        <div class="font-bold text-[11px] line-clamp-3 leading-tight" x-text="selectedBook.title"></div>
                                        <div class="text-[8px] text-blue-300/80 font-mono">SMAN 24</div>
                                    </div>
                                </template>
                                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-r from-black/35 to-transparent pointer-events-none"></div>
                            </div>

                            <!-- Title, Category, Author, Publisher, Year -->
                            <div class="space-y-1.5 flex-1 min-w-0 text-left">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-[#1a4f8b] font-bold text-[10px] uppercase tracking-wide truncate max-w-[170px]"
                                          x-text="selectedBook.category ? (selectedBook.category.code + ' • ' + selectedBook.category.name) : 'Koleksi Umum'">
                                    </span>
                                    
                                    <template x-if="selectedBook.available_stock > 0">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Tersedia (<span x-text="selectedBook.available_stock"></span>)</span>
                                        </span>
                                    </template>
                                    <template x-if="selectedBook.available_stock <= 0">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 font-bold text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Habis</span>
                                        </span>
                                    </template>
                                </div>

                                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug line-clamp-2" x-text="selectedBook.title"></h2>

                                <div class="space-y-1 text-xs text-slate-600 pt-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#1a4f8b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate">Penulis: <strong class="text-slate-900 font-semibold" x-text="selectedBook.author || 'Anonim'"></strong></span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#1a4f8b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="truncate">Penerbit: <strong class="text-slate-900 font-semibold" x-text="selectedBook.publisher || '-'"></strong></span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#1a4f8b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Tahun: <strong class="text-slate-900 font-semibold" x-text="selectedBook.publication_year || '-'"></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4-Box Technical Data Grid (Compact) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-slate-100">
                            <!-- 1. ISBN -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-[9px] text-slate-500 font-semibold block uppercase tracking-wider">ISBN</span>
                                <div class="font-mono font-bold text-xs text-slate-900 mt-0.5 truncate" x-text="selectedBook.isbn"></div>
                            </div>

                            <!-- 2. Lokasi Rak -->
                            <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-100">
                                <span class="text-[9px] text-[#1a4f8b] font-semibold block uppercase tracking-wider">Lokasi Rak</span>
                                <div class="font-bold text-xs text-slate-900 mt-0.5 font-mono truncate" x-text="selectedBook.rack_location || '-'"></div>
                            </div>

                            <!-- 3. Stok Buku -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-[9px] text-slate-500 font-semibold block uppercase tracking-wider">Stok</span>
                                <div class="font-bold text-xs text-slate-900 mt-0.5">
                                    <span class="text-emerald-700" x-text="selectedBook.available_stock"></span>/<span x-text="selectedBook.total_stock"></span> eks
                                </div>
                            </div>

                            <!-- 4. Klasifikasi DDC -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-[9px] text-slate-500 font-semibold block uppercase tracking-wider">Kode DDC</span>
                                <div class="font-mono font-bold text-xs text-[#1a4f8b] mt-0.5 truncate" x-text="selectedBook.category ? selectedBook.category.code : '-'"></div>
                            </div>
                        </div>

                        <!-- Slot Sinopsis / Deskripsi (Otomatis tampil jika tersedia) -->
                        <template x-if="selectedBook.sinopsis || selectedBook.synopsis || selectedBook.description">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                                <span class="text-[10px] text-slate-500 font-semibold block uppercase tracking-wider">Sinopsis / Ringkasan Buku</span>
                                <p class="text-xs text-slate-600 leading-relaxed max-h-28 overflow-y-auto whitespace-pre-line" x-text="selectedBook.sinopsis || selectedBook.synopsis || selectedBook.description"></p>
                            </div>
                        </template>

                        <!-- Lending Policy (Compact Alert) -->
                        <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/80 space-y-1 text-xs">
                            <div class="flex items-center gap-1.5 text-amber-900 font-bold text-[11px]">
                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Ketentuan Peminjaman SMAN 24</span>
                            </div>
                            <div class="text-[10px] text-amber-950/80 leading-relaxed">
                                Batas ambil buku fisik <strong>maksimal 2 hari sekolah</strong> di loket. Masa pinjam standar <strong>7 hari</strong> bebas denda.
                            </div>
                        </div>

                        <!-- Checkbox Confirmation for Loan -->
                        <template x-if="selectedBook.available_stock > 0">
                            <label class="flex items-start gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer select-none hover:bg-blue-50/50 transition">
                                <input type="checkbox" x-model="confirmedTerms" class="mt-0.5 w-3.5 h-3.5 rounded text-[#1a4f8b] border-slate-300 focus:ring-[#1a4f8b]">
                                <span class="text-slate-700 text-[10px] leading-relaxed">
                                    Saya bersedia mengambil buku fisik di loket sirkulasi tepat waktu.
                                </span>
                            </label>
                        </template>
                    </div>

                    <!-- Modal Actions Footer (Compact) -->
                    <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2 shrink-0">
                        <button type="button" 
                                @click="detailModal = false" 
                                class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-medium text-xs transition active:scale-95 text-center">
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            <template x-if="selectedBook.available_stock > 0">
                                <button type="button" 
                                        @click="$store.cart.add(selectedBook)" 
                                        class="btn-press px-3.5 py-1.5 rounded-lg bg-white border border-[#1a4f8b] text-[#1a4f8b] hover:bg-blue-50 font-medium text-xs transition flex items-center gap-1.5 shadow-2xs active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span x-text="$store.cart.has(selectedBook.id) ? 'Di Keranjang' : '+ Keranjang'"></span>
                                </button>
                            </template>

                            <template x-if="selectedBook.available_stock > 0">
                                <button type="button" 
                                        @click="submitLoanFromDetail()" 
                                        :disabled="!confirmedTerms || isSubmittingLoan" 
                                        :class="(!confirmedTerms || isSubmittingLoan) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-[#1a4f8b] hover:bg-[#102a54] active:scale-95'"
                                        class="btn-sheen px-4 py-1.5 rounded-lg text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs">
                                    <span x-show="isSubmittingLoan" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Ajukan Pinjam</span>
                                </button>
                            </template>

                            <template x-if="selectedBook.available_stock <= 0">
                                <button type="button" 
                                        disabled 
                                        class="px-3.5 py-1.5 rounded-lg bg-slate-200 text-slate-500 font-medium text-xs cursor-not-allowed">
                                    Stok Habis
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
