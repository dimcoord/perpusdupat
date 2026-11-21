@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    newLoanModal: false,
    bookSearch: '',
    selectedBooks: [],
    maxBooks: {{ \App\Http\Controllers\CartController::MAX_ITEMS }},
    books: {{ \Illuminate\Support\Js::from($availableBooks ?? []) }},

    get filteredBooks() {
        const q = this.bookSearch.trim().toLowerCase();
        if (!q) return this.books;
        return this.books.filter(b =>
            (b.title || '').toLowerCase().includes(q) || (b.isbn || '').toLowerCase().includes(q)
        );
    },

    isDisabled(id) {
        return !this.selectedBooks.includes(id) && this.selectedBooks.length >= this.maxBooks;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ auth()->user()->isPustakawan() ? 'Sirkulasi & Peminjaman Buku' : 'Riwayat Peminjaman Buku Saya' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ auth()->user()->isPustakawan() ? 'Daftar struk peminjaman. Klik struk untuk melihat detail, menyetujui, atau mencatat pengembalian.' : 'Pantau status struk peminjaman, tanggal jatuh tempo, dan riwayat buku yang telah Anda pinjam. Klik struk untuk melihat detail.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isPustakawan())
                <button type="button" @click="newLoanModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Peminjaman Baru
                </button>

                <a href="{{ route('loans.export', ['status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition" title="Cetak laporan sirkulasi ke format Excel">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Laporan (XLSX)
                </a>
            @else
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Buku untuk Dipinjam
                </a>
            @endif
        </div>
    </div>

    <!-- Filters & Status Tabs -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            @php
                $statusTabs = [
                    '' => 'Semua Struk',
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'dipinjam' => 'Sedang Dipinjam',
                    'terlambat' => 'Terlambat',
                    'kembali' => 'Sudah Kembali',
                    'ditolak' => 'Ditolak',
                ];
            @endphp

            @foreach($statusTabs as $val => $label)
                <a href="{{ route('loans.index', array_merge(request()->query(), ['status' => $val])) }}"
                   class="px-3.5 py-1.5 rounded-xl font-medium transition shrink-0 {{ ($status === $val || (empty($status) && empty($val))) ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if(auth()->user()->isPustakawan())
            <!-- Search & Date Filter Form -->
            <form method="GET" action="{{ route('loans.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari no. struk, nama peminjam, NIS, judul buku, atau ISBN..." class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                </div>
                <div>
                    <input type="date" name="start_date" value="{{ $startDate }}" placeholder="Dari tanggal pinjam" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-1.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition">
                        Cari
                    </button>
                    @if($search || $startDate || $endDate)
                        <a href="{{ route('loans.index') }}" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        @endif
    </div>

    @if (session('success')) <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">{{ session('error') }}</div> @endif

    <!-- Receipts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4">No. Struk</th>
                        <th class="py-3 px-4">Buku</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4">Peminjam</th>
                        @endif
                        <th class="py-3 px-4 text-center">Tgl Pinjam</th>
                        <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Denda (Rp)</th>
                        <th class="py-3 px-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $receipt)
                        @php
                            $isPending = $receipt->status === 'menunggu_persetujuan';
                            $isReturned = $receipt->status === 'dikembalikan';
                            $isOverdue = ! $isPending && ! $isReturned && $receipt->due_date->lt(today());
                            $titles = $receipt->loans->map(fn ($l) => $l->book?->title)->filter()->values();
                            $fine = $receipt->loans->sum('calculated_fine');
                            $url = route('loan-receipts.show', $receipt);
                        @endphp
                        <tr onclick="window.location='{{ $url }}'" class="cursor-pointer hover:bg-slate-50/60 transition {{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ $url }}" onclick="event.stopPropagation()" class="font-mono font-semibold text-blue-700 hover:underline">
                                    {{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 shrink-0">
                                        {{ $receipt->loans->count() }} buku
                                    </span>
                                    <span class="font-medium text-slate-900 truncate" title="{{ $titles->join(', ') }}">
                                        {{ $titles->first() ?? '-' }}@if($titles->count() > 1) <span class="text-slate-400 font-normal">+{{ $titles->count() - 1 }} lainnya</span>@endif
                                    </span>
                                </div>
                            </td>
                            @if(auth()->user()->isPustakawan())
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $receipt->user?->name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIS: {{ $receipt->user?->nis_nip ?? '-' }}</div>
                                </td>
                            @endif
                            <td class="py-3.5 px-4 text-center text-slate-600 font-medium">
                                {{ $receipt->loan_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ $receipt->due_date->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($receipt->status === 'ditolak')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">Ditolak</span>
                                @elseif($isPending)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Menunggu Persetujuan</span>
                                @elseif($isOverdue)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 animate-pulse">Terlambat</span>
                                @elseif($isReturned)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Kembali</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">Dipinjam</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold {{ $fine > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $fine > 0 ? number_format($fine, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">&rsaquo;</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isPustakawan() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                Tidak ada struk peminjaman pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

    <!-- Librarian New Loan Modal (multi-book, creates one receipt) -->
    @if(auth()->user()->isPustakawan())
        <div x-cloak x-show="newLoanModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="newLoanModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Catat Transaksi Peminjaman Baru</h3>
                    <button type="button" @click="newLoanModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('loans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIS Siswa Peminjam <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nip" required placeholder="Masukkan NIS siswa (contoh: 2024101)..." class="w-full px-3 py-2 border rounded-xl text-xs font-mono">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Buku <span class="text-rose-500">*</span></label>
                            <span class="text-[11px] text-slate-500" x-text="selectedBooks.length + ' / ' + maxBooks + ' judul dipilih (maks. ' + maxBooks + ')'"></span>
                        </div>
                        <input type="text" x-model="bookSearch" placeholder="Cari judul atau ISBN..." class="w-full px-3 py-2 border rounded-xl text-xs mb-2">
                        <div class="max-h-48 overflow-y-auto border rounded-xl divide-y divide-slate-100">
                            <template x-for="book in filteredBooks" :key="book.id">
                                <label class="flex items-center gap-2.5 px-3 py-2 text-xs hover:bg-slate-50 cursor-pointer" :class="isDisabled(book.id) ? 'opacity-50 cursor-not-allowed' : ''">
                                    <input type="checkbox" name="book_ids[]" :value="book.id" x-model="selectedBooks" :disabled="isDisabled(book.id)" class="rounded border-slate-300">
                                    <span class="flex-1 min-w-0">
                                        <span class="block font-medium text-slate-900 truncate" x-text="book.title"></span>
                                        <span class="block text-[10px] text-slate-400 font-mono" x-text="'ISBN: ' + (book.isbn || '-') + ' • Stok: ' + book.available_stock"></span>
                                    </span>
                                </label>
                            </template>
                            <div x-show="filteredBooks.length === 0" class="px-3 py-6 text-center text-xs text-slate-400">Tidak ada buku yang cocok.</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pinjam</label>
                            <input type="date" name="loan_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 border rounded-xl text-xs">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="newLoanModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" :disabled="selectedBooks.length === 0" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold transition">Catat Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection