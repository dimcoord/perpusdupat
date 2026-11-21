@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    search: '{{ $search ?? '' }}',
    categoryId: '{{ $categoryId ?? '' }}',
    availability: '{{ $availability ?? '' }}',
    importModal: false,
    cartIds: {{ \Illuminate\Support\Js::from(array_map('intval', array_values(session('loan_cart', [])))) }},
    cartMax: {{ \App\Http\Controllers\CartController::MAX_ITEMS }},
    busyId: null,

    inCart(id) {
        return this.cartIds.includes(id);
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
    removeFromCart(book) { return this.cartRequest(book, 'DELETE'); }
}">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Buku Perpustakaan</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar koleksi buku, literatur, dan bahan ajar yang terdaftar di perpustakaan.</p>
        </div>

        @if(auth()->user()->isPustakawan())
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('books.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Buku Baru
                </a>
                <button type="button" @click="importModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import XLSX
                </button>
                <a href="{{ route('books.template') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition" title="Unduh format file Excel untuk import massal">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh Template
                </a>
            </div>
        @else
            <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Keranjang
                <span x-text="cartIds.length + '/' + cartMax" class="px-1.5 py-0.5 rounded-md bg-white/20 font-mono text-[10px]"></span>
            </a>
        @endif
    </div>

    <!-- Live Search & Filtering Panel -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('books.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Keyword -->
            <div class="lg:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari judul buku, nomor ISBN, penulis, atau rak..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white">
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
                <select name="availability" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white">
                    <option value="">Semua Status Stok</option>
                    <option value="available" {{ $availability === 'available' ? 'selected' : '' }}>Tersedia Saja</option>
                    <option value="out_of_stock" {{ $availability === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition shrink-0">
                    Cari
                </button>
                @if($search || $categoryId || $availability)
                    <a href="{{ route('books.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition shrink-0" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Books Card Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($books as $book)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between overflow-hidden group">
                <!-- Book Cover Header -->
                <div class="h-44 w-full bg-slate-100 overflow-hidden relative border-b border-slate-100">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center p-4 bg-gradient-to-br from-slate-800 via-slate-900 to-blue-950 text-white relative">
                            <svg class="w-10 h-10 text-slate-500 mb-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-[10px] text-slate-300 font-mono text-center uppercase tracking-widest">{{ $book->category?->code ?? 'DDC' }}</span>
                        </div>
                    @endif
                    <span class="absolute top-2.5 right-2.5 text-[10px] font-mono px-2 py-0.5 rounded-md bg-slate-900/80 text-white font-medium backdrop-blur-xs border border-white/10">
                        {{ $book->rack_location ?: 'Rak -' }}
                    </span>
                </div>

                <div class="p-5 space-y-3">
                    <!-- Top Category -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                            {{ $book->category?->code ?? '000' }} &bull; {{ $book->category?->name ?? 'Umum' }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $book->publication_year ?: '-' }}</span>
                    </div>

                    <!-- Book Title & Author -->
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm line-clamp-2 group-hover:text-blue-600 transition" title="{{ $book->title }}">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">
                            {{ $book->author ?: 'Anonim' }}
                        </p>
                    </div>

                    <!-- Details: ISBN & Publisher -->
                    <div class="pt-2 border-t border-slate-100 space-y-1 text-[11px] text-slate-400">
                        <div class="flex items-center justify-between">
                            <span>ISBN:</span>
                            <span class="font-mono text-slate-600">{{ $book->isbn }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Penerbit:</span>
                            <span class="text-slate-600 truncate max-w-[120px]">{{ $book->publisher ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer: Stock & Actions -->
                <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
                    <!-- Stock Badge -->
                    <div>
                        @if($book->available_stock > 0)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Tersedia {{ $book->available_stock }} / {{ $book->total_stock }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                Stok Habis
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1.5">
                        @if(auth()->user()->isPustakawan())
                            <a href="{{ route('books.edit', $book) }}" class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 transition" title="Edit Buku">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini dari katalog?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-rose-600 hover:border-rose-300 transition" title="Hapus Buku">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            @if($book->available_stock > 0)
                                <button type="button"
                                        x-show="!inCart({{ $book->id }})"
                                        @click="addToCart({ id: {{ $book->id }} })"
                                        :disabled="busyId === {{ $book->id }}"
                                        class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white font-semibold text-xs shadow-2xs transition active:scale-95">
                                    + Keranjang
                                </button>
                                <button type="button"
                                        x-cloak
                                        x-show="inCart({{ $book->id }})"
                                        @click="removeFromCart({ id: {{ $book->id }} })"
                                        :disabled="busyId === {{ $book->id }}"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 font-semibold text-xs transition active:scale-95"
                                        title="Klik untuk menghapus dari keranjang">
                                    &#10003; Di Keranjang
                                </button>
                            @else
                                <button type="button" disabled class="px-3 py-1.5 rounded-lg bg-slate-200 text-slate-400 font-semibold text-xs cursor-not-allowed">
                                    Kosong
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-semibold text-slate-800">Tidak ada buku yang sesuai dengan pencarian</h3>
                <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci lain atau reset filter kategori.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $books->links() }}
    </div>

    <!-- Import Modal (Librarian) -->
    <div x-cloak x-show="importModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="importModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Import Massal Katalog Buku (XLSX)</h3>
                <button @click="importModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200/80 text-xs text-blue-900 space-y-1.5">
                    <div class="font-semibold">Petunjuk Import:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-blue-800/90 text-[11px]">
                        <li>Pastikan menggunakan format kolom: <code class="font-mono bg-blue-100 px-1 rounded">[ISBN, Judul, Penulis, Penerbit, Tahun, Kode_Kategori, Lokasi_Rak, Jumlah]</code></li>
                        <li>Buku dengan ISBN yang sudah ada akan otomatis di-update stoknya.</li>
                        <li>Proses import menggunakan metode stream chunking agar tidak membebani memori RAM server.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Berkas Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer border rounded-xl p-2 bg-slate-50">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <a href="{{ route('books.template') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-medium">
                        Unduh Template Contoh
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="importModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">Unggah & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(! auth()->user()->isPustakawan())
        <!-- Floating Cart Button -->
        <a href="{{ route('cart.index') }}"
           x-cloak
           x-show="cartIds.length > 0"
           x-transition
           class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xl transition">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Lihat Keranjang (<span x-text="cartIds.length"></span>)
        </a>
    @endif
</div>
@endsection