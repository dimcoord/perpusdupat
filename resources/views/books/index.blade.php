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

    cartIds: {{ \Illuminate\Support\Js::from(array_map('intval', array_values(session('loan_cart', [])))) }},
    cartMax: {{ \App\Http\Controllers\CartController::MAX_ITEMS }},
    busyId: null,

    inCart(id) {
        return this.cartIds.includes(id);
    },

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

    async cartRequest(book, method) {
        if (this.busyId) return;
        this.busyId = book.id;

        try {
            const res = await fetch('{{ url('cart/books') }}/' + book.id, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            if (data.success) {
                if (method === 'POST') {
                    if (!this.inCart(book.id)) this.cartIds.push(book.id);
                } else {
                    this.cartIds = this.cartIds.filter(i => i !== book.id);
                }
                Alpine.store('toast').notify(data.message, 'success');
            } else {
                Alpine.store('toast').notify(data.message || 'Gagal memperbarui keranjang', 'error');
            }
        } catch (e) {
            Alpine.store('toast').notify('Terjadi kesalahan jaringan LAN', 'error');
        } finally {
            this.busyId = null;
        }
    },

    addToCart(book) { return this.cartRequest(book, 'POST'); },
    removeFromCart(book) { return this.cartRequest(book, 'DELETE'); },

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
                book_ids: [this.selectedBook.id]
            })
        })
        .then(res => res.json())
        .then(data => {
            this.isSubmittingLoan = false;
            if (data.success) {
                Alpine.store('toast').notify(data.message, 'success');
                this.loanModal = false;
                this.detailModal = false;
                const receiptUrl = data.receipt_url || data.redirect || '{{ route('loans.index') }}';
                setTimeout(() => {
                    window.location.href = receiptUrl;
                }, 800);
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
        @else
            <button type="button"
                    @click="$store.cart.open()"
                    class="btn-press inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Keranjang Pinjam</span>
                <span x-text="$store.cart.count + '/' + $store.cart.maxItems" class="px-1.5 py-0.5 rounded-md bg-white/20 font-mono text-[10px]"></span>
            </button>
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

            <!-- Availability Filter & Submit Button -->
            <div class="flex items-center gap-2">
                <select name="availability" class="w-full px-3 py-2.5 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                    <option value="">Semua Ketersediaan</option>
                    <option value="available" {{ $availability == 'available' ? 'selected' : '' }}>Buku Tersedia</option>
                    <option value="out_of_stock" {{ $availability == 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>

                <button type="submit" class="btn-press px-4 py-2.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium transition shadow-2xs shrink-0 active:scale-[0.98]">
                    Filter
                </button>

                @if($search || $categoryId || $availability)
                    <a href="{{ route('books.index') }}" class="p-2.5 rounded-lg bg-[#e2e8f0]/60 hover:bg-[#e2e8f0] text-gray-700 transition shrink-0" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Book Grid List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($books as $book)
            <div class="scroll-reveal delay-{{ ($loop->index % 4) + 1 }} bg-white rounded-xl border border-[#e2e8f0] overflow-hidden flex flex-col justify-between shadow-xs card-hover group cursor-pointer"
                 @click="openBookDetail({{ json_encode($book) }})">
                <!-- Top Section: Visual Cover + Meta Info -->
                <div class="p-3.5 sm:p-4">
                    <div class="flex gap-3 sm:gap-3.5">
                        <!-- Book Cover with Fallback -->
                        <div class="relative shrink-0 w-16 sm:w-20 aspect-[2/3] rounded-lg overflow-hidden shadow-xs border border-[#e2e8f0] bg-gray-50">
                            @if($book->cover_url)
                                <img src="{{ $book->cover_url }}"
                                     alt="Sampul {{ $book->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full bg-[#102a54] text-white flex flex-col items-center justify-center p-1 text-center group-hover:bg-[#1a4f8b] transition duration-300">
                                    <span class="text-[8px] font-mono text-amber-300 font-bold uppercase tracking-wider">DUPAT</span>
                                    <svg class="w-5 h-5 text-blue-200 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-1 left-1">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-[#102a54]/80 text-white backdrop-blur-xs">
                                    {{ $book->category?->code ?? 'GEN' }}
                                </span>
                            </div>
                        </div>

                        <!-- Book Metadata -->
                        <div class="flex-1 min-w-0 space-y-1">
                            <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider block truncate">
                                {{ $book->category?->name ?? 'Umum' }}
                            </span>
                            <h3 class="font-bold text-xs sm:text-sm text-gray-900 leading-snug line-clamp-2 group-hover:text-[#1a4f8b] transition" title="{{ $book->title }}">
                                {{ $book->title }}
                            </h3>
                            <p class="text-xs text-gray-600 truncate">
                                {{ $book->author ?: 'Anonim' }}
                            </p>
                            @if($book->sinopsis)
                                <p class="text-[11px] text-gray-500 line-clamp-2 italic mt-1" title="{{ $book->sinopsis }}">
                                    {{ $book->sinopsis }}
                                </p>
                            @endif
                            <div class="pt-1 text-[11px] text-gray-500 space-y-0.5">
                                <div class="truncate">Rak: <span class="font-semibold text-gray-800">{{ $book->rack_location }}</span></div>
                                <div class="font-mono text-[10px] text-gray-400">ISBN: {{ $book->isbn }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Stock & Actions -->
                <div class="p-2.5 sm:p-3.5 bg-[#e2e8f0]/25 border-t border-[#e2e8f0] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <!-- Stock Badge -->
                    <div>
                        @if($book->available_stock > 0)
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 text-[11px] sm:text-xs font-semibold text-emerald-700">
                                <span class="w-1.5 sm:w-2 h-1.5 sm:h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Tersedia {{ $book->available_stock }} / {{ $book->total_stock }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 sm:gap-1.5 text-[11px] sm:text-xs font-medium text-rose-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Stok Habis
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1 sm:gap-1.5 w-full sm:w-auto justify-end" @click.stop>
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
                                        x-show="!$store.cart.has({{ $book->id }})"
                                        @click="$store.cart.add({{ json_encode($book) }})"
                                        class="btn-press p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 text-slate-700 font-medium text-xs transition shadow-2xs"
                                        title="Tambah ke Keranjang Pinjam">
                                    <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </button>
                                <button type="button"
                                        x-cloak
                                        x-show="$store.cart.has({{ $book->id }})"
                                        @click="$store.cart.remove({{ $book->id }})"
                                        class="btn-press p-1.5 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 font-medium text-xs transition shadow-2xs"
                                        title="Sudah di keranjang. Klik untuk membatalkan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button type="button" @click="openBookDetail({{ json_encode($book) }})" class="btn-press flex-1 sm:flex-initial text-center px-2.5 sm:px-3 py-1.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                                    Detail & Pinjam
                                </button>
                            @else
                                <button type="button" @click="openBookDetail({{ json_encode($book) }})" class="btn-press flex-1 sm:flex-initial text-center px-2.5 sm:px-3.5 py-1.5 rounded-lg bg-[#e2e8f0]/80 hover:bg-[#e2e8f0] text-gray-600 font-medium text-xs transition">
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

    <!-- Student Book Detail & Action Modal (Compact & Minimalist) -->
    <div x-cloak x-show="detailModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.outside="detailModal = false" 
             class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-150">
            <template x-if="selectedBook">
                <div class="flex flex-col h-full">
                    <!-- Modal Header (Compact) -->
                    <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between bg-slate-50/80 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#1a4f8b] text-white" x-text="selectedBook.category?.code || 'Buku'"></span>
                            <span class="text-xs font-semibold text-slate-800 truncate" x-text="selectedBook.category?.name || 'Katalog'"></span>
                        </div>
                        <button type="button" @click="detailModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable, Tight Padding) -->
                    <div class="p-4 overflow-y-auto space-y-3.5 text-xs">
                        <div class="flex gap-3.5 items-start">
                            <!-- Cover Thumbnail -->
                            <div class="w-16 sm:w-20 aspect-[2/3] rounded-lg overflow-hidden border border-slate-200 bg-slate-100 shrink-0 shadow-xs">
                                <template x-if="selectedBook.cover_url">
                                    <img :src="selectedBook.cover_url" :alt="selectedBook.title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!selectedBook.cover_url">
                                    <div class="w-full h-full bg-[#102a54] text-white flex flex-col items-center justify-center p-1 text-center">
                                        <span class="text-[8px] font-mono text-amber-300 font-bold">DUPAT</span>
                                        <svg class="w-4 h-4 text-blue-200 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Title & Essential Specs -->
                            <div class="flex-1 min-w-0 space-y-1">
                                <h2 class="font-bold text-sm sm:text-base text-slate-900 leading-snug line-clamp-2" x-text="selectedBook.title"></h2>
                                <div class="text-slate-600 text-[11px]">Penulis: <span class="font-medium text-slate-800" x-text="selectedBook.author"></span></div>
                                <div class="text-slate-500 text-[11px]">Penerbit: <span class="text-slate-700" x-text="selectedBook.publisher || '-'"></span> (<span x-text="selectedBook.publication_year || '-'"></span>)</div>
                                <div class="text-slate-500 font-mono text-[10px]">ISBN: <span x-text="selectedBook.isbn"></span></div>
                            </div>
                        </div>

                        <!-- Location & Inventory Badge Grid -->
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="text-slate-400 text-[10px]">Lokasi Rak</div>
                                <div class="font-bold text-[#1a4f8b] mt-0.5" x-text="selectedBook.rack_location || '-'"></div>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="text-slate-400 text-[10px]">Status Stok</div>
                                <div class="font-bold mt-0.5" 
                                     :class="selectedBook.available_stock > 0 ? 'text-emerald-700' : 'text-rose-600'" 
                                     x-text="selectedBook.available_stock > 0 ? 'Tersedia ' + selectedBook.available_stock + ' / ' + selectedBook.total_stock + ' buku' : 'Stok Habis'"></div>
                            </div>
                        </div>

                        <!-- Sinopsis Buku -->
                        <template x-if="selectedBook.sinopsis">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <span>Sinopsis Buku</span>
                                </div>
                                <p class="text-[11px] text-slate-600 leading-relaxed max-h-32 overflow-y-auto whitespace-pre-line" x-text="selectedBook.sinopsis"></p>
                            </div>
                        </template>

                        <!-- Loan Terms & Limit Note -->
                        @if(!auth()->user()->isPustakawan())
                            <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200/80 text-[11px] text-slate-700 space-y-1">
                                <div class="font-bold text-[#1a4f8b] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#1a4f8b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Ketentuan Peminjaman Mandiri:</span>
                                </div>
                                <ul class="list-disc list-inside text-slate-600 text-[10px] space-y-0.5 pl-1">
                                    <li>Maksimal <strong>2 buku</strong> aktif per siswa.</li>
                                    <li>Durasi pinjam standar: <strong>7 hari</strong> sejak disetujui pustakawan.</li>
                                    <li>Buku wajib diambil di meja sirkulasi maksimal <strong>2 hari</strong> setelah diajukan.</li>
                                </ul>
                            </div>

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

                    <!-- Modal Actions Footer -->
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
                                            @click="addToCart(selectedBook)" 
                                            class="btn-press px-3.5 py-1.5 rounded-lg bg-white border border-[#1a4f8b] text-[#1a4f8b] hover:bg-blue-50 font-medium text-xs transition flex items-center gap-1.5 shadow-2xs active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span x-text="inCart(selectedBook.id) ? 'Di Keranjang' : '+ Keranjang'"></span>
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

    @if(! auth()->user()->isPustakawan())
        <!-- Floating Cart Button (Opens Right Side Drawer) -->
        <button type="button"
                @click="$store.cart.open()"
                x-cloak
                x-show="$store.cart.count > 0"
                x-transition
                class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full bg-[#102a54] hover:bg-[#1a4f8b] text-white text-xs font-semibold shadow-xl transition active:scale-95 border border-white/20">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Lihat Keranjang (<span x-text="$store.cart.count"></span>)</span>
        </button>
    @endif
</div>
@endsection
