@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    search: '{{ $search ?? '' }}',
    categoryId: '{{ $categoryId ?? '' }}',
    availability: '{{ $availability ?? '' }}',
    importModal: false,
    loanModal: false,
    detailModal: false,
    selectedBook: null,
    isSubmittingLoan: false,
    confirmedTerms: false,
    
    openBookDetail(book) {
        if (!book) return;
        this.selectedBook = book;
        this.confirmedTerms = false;
        this.detailModal = true;
    },

    openLoanModal(book) {
        if (!book) return;
        this.selectedBook = book;
        this.confirmedTerms = false;
        this.loanModal = true;
    },

    submitLoan() {
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
                Alpine.store('toast').notify(data.message, 'success');
                this.loanModal = false;
                this.detailModal = false;
                setTimeout(() => window.location.reload(), 1200);
            } else {
                Alpine.store('toast').notify(data.message || 'Gagal mengajukan pinjaman', 'error');
            }
        })
        .catch(() => {
            this.isSubmittingLoan = false;
            Alpine.store('toast').notify('Terjadi kesalahan jaringan LAN', 'error');
        });
    }
}">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 scroll-reveal">
        <div>
            <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                Koleksi & Sirkulasi Pustaka
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Katalog Buku Perpustakaan</h1>
            <p class="text-xs text-gray-500 mt-1">Daftar koleksi buku, literatur, dan bahan ajar yang terdaftar di perpustakaan SMA Negeri 24.</p>
        </div>

        @if(auth()->user()->isPustakawan())
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('books.create') }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Buku
                </a>
                <button type="button" @click="importModal = true" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import XLSX
                </button>
                <a href="{{ route('books.template') }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]" title="Unduh format file Excel untuk import massal">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh Template
                </a>
            </div>
        @endif
    </div>

    <!-- Live Search & Filtering Panel -->
    <div class="scroll-reveal delay-1 bg-white p-4.5 rounded-xl border border-[#e2e8f0] shadow-xs card-hover">
        <form method="GET" action="{{ route('books.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Keyword -->
            <div class="lg:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari judul buku, nomor ISBN, penulis, atau rak..."
                       class="w-full pl-9 pr-16 py-2.5 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-[10px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">Enter</span>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" class="w-full px-3 py-2.5 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                    <option value="">Semua Kategori (DDC)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->code }} - {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Availability & Filter Button -->
            <div class="flex items-center gap-2">
                <select name="availability" class="w-full px-3 py-2.5 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                    <option value="">Semua Status Stok</option>
                    <option value="available" {{ $availability === 'available' ? 'selected' : '' }}>Tersedia Saja</option>
                    <option value="out_of_stock" {{ $availability === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>
                <button type="submit" class="btn-press px-4 py-2.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition shrink-0 active:scale-[0.98]">
                    Cari
                </button>
                @if($search || $categoryId || $availability)
                    <a href="{{ route('books.index') }}" class="p-2.5 rounded-lg bg-[#e2e8f0]/60 hover:bg-[#e2e8f0] text-gray-700 text-xs font-medium transition shrink-0" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Books Card Grid with Staggered Scroll-Reveal & Hover Lift -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($books as $book)
            <div @click="openBookDetail({{ json_encode($book) }})" 
                 class="scroll-reveal delay-{{ ($loop->index % 4) + 1 }} bg-white rounded-xl border border-[#e2e8f0] flex flex-col justify-between overflow-hidden card-hover shadow-xs group cursor-pointer hover:border-[#1a4f8b]/50 hover:shadow-md transition-all">
                <!-- Book Cover Header with Spine Effect -->
                <div class="h-48 w-full bg-[#e2e8f0]/30 overflow-hidden relative border-b border-[#e2e8f0] before:absolute before:inset-y-0 before:left-0 before:w-1.5 before:bg-gradient-to-r before:from-black/25 before:to-transparent before:z-10">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center p-4 text-[#1a4f8b] relative">
                            <svg class="w-10 h-10 mb-1 opacity-70 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-[10px] text-gray-500 font-mono text-center uppercase tracking-wider">{{ $book->category?->code ?? 'DDC' }}</span>
                        </div>
                    @endif
                    <span class="absolute top-2.5 right-2.5 text-[10px] font-mono px-2 py-0.5 rounded bg-[#1a4f8b] text-white font-medium shadow-xs z-10">
                        {{ $book->rack_location ?: 'Rak -' }}
                    </span>
                </div>

                <div class="p-4 space-y-3">
                    <!-- Top Category & Year -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#e2e8f0]/70 text-gray-700">
                            {{ $book->category?->code ?? '000' }} &bull; {{ $book->category?->name ?? 'Umum' }}
                        </span>
                        <span class="text-[10px] text-gray-400 font-mono">{{ $book->publication_year ?: '-' }}</span>
                    </div>

                    <!-- Book Title & Author -->
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm line-clamp-2 group-hover:text-[#1a4f8b] transition-colors duration-150" title="{{ $book->title }}">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-1">
                            {{ $book->author ?: 'Anonim' }}
                        </p>
                    </div>

                    <!-- Details: ISBN & Publisher -->
                    <div class="pt-2 border-t border-[#e2e8f0]/60 space-y-1 text-[11px] text-gray-500">
                        <div class="flex items-center justify-between">
                            <span>ISBN:</span>
                            <span class="font-mono text-gray-700">{{ $book->isbn }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Penerbit:</span>
                            <span class="text-gray-700 truncate max-w-[120px]">{{ $book->publisher ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Stock & Actions -->
                <div class="p-3.5 bg-[#e2e8f0]/25 border-t border-[#e2e8f0] flex items-center justify-between gap-2">
                    <!-- Stock Badge -->
                    <div>
                        @if($book->available_stock > 0)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Tersedia {{ $book->available_stock }} / {{ $book->total_stock }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Stok Habis
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1.5" @click.stop>
                        @if(auth()->user()->isPustakawan())
                            <a href="{{ route('books.edit', $book) }}" class="btn-press p-1.5 rounded-lg bg-white border border-[#e2e8f0] text-gray-600 hover:text-[#1a4f8b] hover:bg-[#e2e8f0]/30 transition shadow-2xs" title="Edit Buku">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini dari katalog?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-press p-1.5 rounded-lg bg-white border border-[#e2e8f0] text-gray-600 hover:text-rose-600 hover:bg-rose-50 transition shadow-2xs" title="Hapus Buku">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            @if($book->available_stock > 0)
                                <button type="button" 
                                        @click="$store.cart.add({{ json_encode($book) }})" 
                                        :class="$store.cart.has({{ $book->id }}) ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white hover:bg-blue-50 text-slate-700 border-slate-200'"
                                        class="btn-press p-1.5 rounded-lg border font-medium text-xs transition shadow-2xs" 
                                        title="Tambah ke Keranjang Pinjam">
                                    <template x-if="!$store.cart.has({{ $book->id }})">
                                        <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </template>
                                    <template x-if="$store.cart.has({{ $book->id }})">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                </button>
                                <button type="button" @click="openBookDetail({{ json_encode($book) }})" class="btn-press px-3 py-1.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                                    Detail & Pinjam
                                </button>
                            @else
                                <button type="button" @click="openBookDetail({{ json_encode($book) }})" class="btn-press px-3.5 py-1.5 rounded-lg bg-[#e2e8f0]/80 hover:bg-[#e2e8f0] text-gray-600 font-medium text-xs transition">
                                    Lihat Info
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-[#e2e8f0] p-12 text-center shadow-xs">
                <div class="w-10 h-10 rounded-full bg-[#e2e8f0]/60 flex items-center justify-center text-[#1a4f8b] mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-800">Tidak ada buku yang sesuai dengan pencarian</h3>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci lain atau reset filter kategori.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6 scroll-reveal delay-2">
        {{ $books->links() }}
    </div>

    <!-- Import Modal (Librarian) -->
    <div x-cloak x-show="importModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="importModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4">
            <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Import Massal Katalog Buku (XLSX)</h3>
                <button @click="importModal = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>

            <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="p-3.5 rounded-lg bg-[#e2e8f0]/25 border border-[#e2e8f0] text-xs text-gray-700 space-y-1.5">
                    <div class="font-semibold text-gray-900">Petunjuk Import:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-gray-600 text-[11px]">
                        <li>Pastikan format kolom: <code class="font-mono bg-[#e2e8f0]/60 px-1 py-0.5 rounded text-gray-800">[ISBN, Judul, Penulis, Penerbit, Tahun, Kode_Kategori, Lokasi_Rak, Jumlah]</code></li>
                        <li>Buku dengan ISBN yang sudah ada akan otomatis di-update stoknya.</li>
                        <li>Proses import menggunakan stream chunking agar hemat memori server LAN.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Pilih Berkas Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-[#1a4f8b] file:text-white hover:file:bg-[#153e75] cursor-pointer border border-[#e2e8f0] rounded-lg p-2 bg-white">
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-[#e2e8f0]">
                    <a href="{{ route('books.template') }}" class="text-xs text-[#1a4f8b] hover:text-[#153e75] hover:underline flex items-center gap-1 font-medium">
                        Unduh Template Contoh
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="importModal = false" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</button>
                        <button type="submit" class="btn-press px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium shadow-2xs transition active:scale-[0.98]">Unggah & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Loan Request Modal -->
    <div x-cloak x-show="loanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="loanModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4">
            <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Konfirmasi Pengajuan Peminjaman</h3>
                <button @click="loanModal = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>

            <template x-if="selectedBook">
                <div class="space-y-4">
                    <div class="flex items-start gap-3 bg-[#e2e8f0]/25 p-3.5 rounded-lg border border-[#e2e8f0]">
                        <template x-if="selectedBook.cover_url">
                            <img :src="selectedBook.cover_url" alt="Cover" class="w-14 h-20 object-cover rounded border border-[#e2e8f0] shrink-0 shadow-2xs">
                        </template>
                        <template x-if="!selectedBook.cover_url">
                            <div class="w-14 h-20 rounded bg-[#e2e8f0]/60 text-[#1a4f8b] flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </template>
                        <div class="space-y-1 text-xs">
                            <div class="font-bold text-gray-900 line-clamp-2" x-text="selectedBook.title"></div>
                            <div class="text-gray-500">Penulis: <span class="text-gray-700" x-text="selectedBook.author || 'Anonim'"></span></div>
                            <div class="text-gray-500">ISBN: <span class="font-mono text-gray-700" x-text="selectedBook.isbn"></span></div>
                            <div class="text-gray-500">Lokasi: <span class="font-medium text-gray-900 font-mono" x-text="selectedBook.rack_location || '-'"></span></div>
                        </div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-[#e2e8f0] space-y-2 text-xs">
                        <div class="flex items-start gap-2">
                            <span class="text-amber-600 font-bold shrink-0">⏳</span>
                            <div class="text-gray-600 text-[11px] leading-relaxed">
                                <strong class="text-gray-900 font-semibold">Batas Pengambilan:</strong> Maksimal <span class="font-bold text-amber-700">2 hari sekolah</span> di loket perpustakaan. Pengajuan otomatis kedaluwarsa jika buku tidak diambil.
                            </div>
                        </div>
                        <div class="flex items-start gap-2 pt-2 border-t border-[#e2e8f0]">
                            <span class="text-[#1a4f8b] font-bold shrink-0">📅</span>
                            <div class="text-gray-600 text-[11px] leading-relaxed">
                                <strong class="text-gray-900 font-semibold">Masa Pinjam:</strong> Standar <strong>7 hari</strong>, baru mulai terhitung resmi saat buku fisik diserahkan oleh pustakawan di loket.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Konfirmasi Siswa -->
                    <label class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50/70 border border-amber-200/80 cursor-pointer text-xs select-none hover:bg-amber-50 transition">
                        <input type="checkbox" x-model="confirmedTerms" class="mt-0.5 w-4 h-4 rounded text-[#1a4f8b] border-gray-300 focus:ring-[#1a4f8b] transition">
                        <span class="text-amber-950 text-[11px] leading-relaxed">
                            Saya yakin ingin meminjam dan siap mengambil buku fisik di loket perpustakaan dalam batas waktu <strong>maksimal 2 hari</strong>.
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#e2e8f0]">
                        <button type="button" @click="loanModal = false" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</button>
                        <button type="button" @click="submitLoan()" :disabled="!confirmedTerms || isSubmittingLoan" :class="(!confirmedTerms || isSubmittingLoan) ? 'opacity-50 cursor-not-allowed' : 'active:scale-[0.98]'" class="btn-press px-4 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                            <span x-show="isSubmittingLoan" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Ajukan Sekarang</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Book Detail Modal (Pop up Informasi Buku Kompak & Tidak Full Layar) -->
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
             class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200/90 overflow-hidden flex flex-col max-h-[82vh]">
            
            <template x-if="selectedBook">
                <div class="flex flex-col h-full overflow-hidden">
                    <!-- Modal Header (Compact) -->
                    <div class="px-4 py-3 bg-gradient-to-r from-[#102a54] to-[#1a4f8b] text-white flex items-center justify-between shrink-0">
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
                    <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1 text-slate-800">
                        
                        <!-- Top Split: Cover and Book Identity -->
                        <div class="flex gap-4 items-start">
                            <!-- Compact Book Cover -->
                            <div class="relative shrink-0 w-22 sm:w-26 aspect-[3/4.2] rounded-xl overflow-hidden shadow-md border border-slate-200 bg-slate-100">
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
                        <template x-if="selectedBook.description || selectedBook.synopsis">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                                <span class="text-[10px] text-slate-500 font-semibold block uppercase tracking-wider">Sinopsis / Ringkasan Buku</span>
                                <p class="text-xs text-slate-600 leading-relaxed max-h-24 overflow-y-auto" x-text="selectedBook.description || selectedBook.synopsis"></p>
                            </div>
                        </template>

                        @if(!auth()->user()->isPustakawan())
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
                        @endif
                    </div>

                    <!-- Modal Actions Footer (Compact) -->
                    <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2 shrink-0">
                        <button type="button" 
                                @click="detailModal = false" 
                                class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-medium text-xs transition active:scale-95 text-center">
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            @if(auth()->user()->isPustakawan())
                                <a :href="'/books/' + selectedBook.id + '/edit'" 
                                   class="btn-press px-4 py-1.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs transition flex items-center gap-1.5 shadow-2xs active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    <span>Edit Buku</span>
                                </a>
                            @else
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
                                            @click="submitLoan()" 
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
                            @endif
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
