@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    newLoanModal: false,
    approveModalOpen: false,
    approveUrl: '',
    approveReceiptNumber: '',
    approveStudentName: '',
    approveBookCount: 1,

    openApproveModal(url, receiptNumber, studentName, bookCount) {
        this.approveUrl = url;
        this.approveReceiptNumber = receiptNumber;
        this.approveStudentName = studentName;
        this.approveBookCount = bookCount;
        this.approveModalOpen = true;
    },

    cancelModalOpen: false,
    cancelUrl: '',
    cancelReceiptNumber: '',
    cancelBookCount: 1,

    openCancelModal(url, receiptNumber, bookCount) {
        this.cancelUrl = url;
        this.cancelReceiptNumber = receiptNumber;
        this.cancelBookCount = bookCount;
        this.cancelModalOpen = true;
    },

    extendModalOpen: false,
    extendUrl: '',
    extendReceiptNumber: '',
    extendDueDate: '',
    extendBookCount: 1,

    openExtendModal(url, receiptNumber, dueDate, bookCount) {
        this.extendUrl = url;
        this.extendReceiptNumber = receiptNumber;
        this.extendDueDate = dueDate;
        this.extendBookCount = bookCount;
        this.extendModalOpen = true;
    },

    approveExtendModalOpen: false,
    approveExtendUrl: '',
    approveExtendReceiptNumber: '',
    approveExtendStudentName: '',
    approveExtendCurrentDue: '',
    approveExtendMinDate: '',
    approveExtendDefaultDate: '',

    openApproveExtendModal(url, receiptNumber, studentName, currentDue, minDate, defaultDate) {
        this.approveExtendUrl = url;
        this.approveExtendReceiptNumber = receiptNumber;
        this.approveExtendStudentName = studentName;
        this.approveExtendCurrentDue = currentDue;
        this.approveExtendMinDate = minDate;
        this.approveExtendDefaultDate = defaultDate;
        this.approveExtendModalOpen = true;
    },

    rejectExtendModalOpen: false,
    rejectExtendUrl: '',
    rejectExtendReceiptNumber: '',
    rejectExtendStudentName: '',

    openRejectExtendModal(url, receiptNumber, studentName) {
        this.rejectExtendUrl = url;
        this.rejectExtendReceiptNumber = receiptNumber;
        this.rejectExtendStudentName = studentName;
        this.rejectExtendModalOpen = true;
    },

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
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 scroll-reveal">
        <div>
            <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                Layanan Sirkulasi & Peminjaman
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                {{ auth()->user()->isPustakawan() ? 'Sirkulasi & Peminjaman Buku' : 'Riwayat Peminjaman Buku Saya' }}
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                {{ auth()->user()->isPustakawan() ? 'Daftar struk peminjaman. Klik struk untuk melihat detail, menyetujui, atau mencatat pengembalian.' : 'Pantau status struk peminjaman, tanggal jatuh tempo, dan riwayat buku yang telah Anda pinjam. Klik struk untuk melihat detail.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isPustakawan())
                <button type="button" @click="newLoanModal = true" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Peminjaman
                </button>

                <a href="{{ route('loans.export', ['status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]" title="Cetak laporan sirkulasi ke format Excel">
                    <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Lap. Peminjaman
                </a>
            @else
                <a href="{{ route('books.index') }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Buku untuk Dipinjam
                </a>
            @endif
        </div>
    </div>

    <!-- Filters & Status Tabs -->
    <div class="scroll-reveal delay-1 bg-white p-3.5 sm:p-4.5 rounded-xl border border-slate-200 space-y-3.5 shadow-xs card-hover">
        <!-- Status Tabs (Horizontal Swipe on Mobile) -->
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 text-xs scrollbar-none">
            @php
                $statusTabs = [
                    '' => 'Semua Struk',
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'perpanjangan' => 'Perpanjangan',
                    'dipinjam' => 'Sedang Dipinjam',
                    'terlambat' => 'Terlambat',
                    'kembali' => 'Sudah Kembali',
                    'ditolak' => 'Ditolak',
                ];
            @endphp

            @foreach($statusTabs as $val => $label)
                <a href="{{ route('loans.index', array_merge(request()->query(), ['status' => $val])) }}"
                   class="btn-press px-3 sm:px-3.5 py-1.5 rounded-lg font-medium transition shrink-0 {{ ($status === $val || (empty($status) && empty($val))) ? 'bg-[#1a4f8b] text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if(auth()->user()->isPustakawan())
            <!-- Search & Date Filter Form -->
            <form method="GET" action="{{ route('loans.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-[#e2e8f0]">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari no. struk, nama peminjam, NIS, judul buku, atau ISBN..." class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>
                <div>
                    <input type="date" name="start_date" value="{{ $startDate }}" placeholder="Dari tanggal pinjam" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-press w-full py-2 px-3.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                        Cari
                    </button>
                    @if($search || $startDate || $endDate)
                        <a href="{{ route('loans.index') }}" class="p-2 rounded-lg bg-[#e2e8f0]/60 hover:bg-[#e2e8f0] text-gray-700 transition shrink-0" title="Reset">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Receipts Table (High-Density & Clean SMAN 24 Styling) -->
    <div class="scroll-reveal delay-2 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs card-hover">
        <!-- Mobile Swipe Helper Indicator -->
        <div class="md:hidden px-4 py-2 bg-slate-50 text-[11px] text-slate-500 border-b border-slate-200 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
                Geser tabel ke samping untuk melihat detail
            </span>
            <span class="text-[10px] font-mono text-slate-400">Scroll &rarr;</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">No. Struk</th>
                        <th class="py-3 px-4">Daftar Buku</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4">Peminjam</th>
                        @endif
                        <th class="py-3 px-4 text-center">Tgl Pinjam</th>
                        <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pr-6 w-52">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $receipt)
                        @php
                            $isPending = $receipt->status === 'menunggu_persetujuan';
                            $isReturned = $receipt->status === 'dikembalikan';
                            $isOverdue = ! $isPending && ! $isReturned && $receipt->due_date && $receipt->due_date->lt(today());
                            $titles = $receipt->loans->map(fn ($l) => $l->book?->title)->filter()->values();
                            $url = route('loan-receipts.show', $receipt);
                        @endphp
                        <tr class="hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 transition-all duration-150 {{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ $url }}" class="font-mono font-semibold text-[#1a4f8b] hover:underline flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#1a4f8b]/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-[#1a4f8b] border border-blue-100 shrink-0">
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
                            <td class="py-3.5 px-4 text-center text-slate-600 font-mono text-[11px]">
                                {{ $receipt->loan_date ? $receipt->loan_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-[11px]">
                                <span class="font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ $receipt->due_date ? $receipt->due_date->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($receipt->status === 'ditolak')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @elseif($isPending)
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu Persetujuan
                                        </span>
                                        <span class="text-[9px] text-amber-600 mt-0.5 font-medium">Ambil loket maks. 2 hari</span>
                                    </div>
                                @elseif($receipt->isExtensionPending())
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Dipinjam
                                        </span>
                                        <span class="text-[9px] text-amber-600 mt-0.5 font-medium">Perpanjangan diajukan</span>
                                    </div>
                                @elseif($isOverdue)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Terlambat
                                    </span>
                                @elseif($isReturned)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kembali
                                    </span>
                                @else
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Dipinjam
                                        </span>
                                        @if($receipt->extension_count > 0)
                                            <span class="text-[9px] text-emerald-600 mt-0.5 font-medium">Diperpanjang ({{ $receipt->extension_count }}x)</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    @if(auth()->user()->isPustakawan())
                                        @if($isPending)
                                            <button type="button" 
                                                    @click="openApproveModal('{{ route('loan-receipts.approve', $receipt) }}', '{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}', '{{ addslashes($receipt->user?->name ?? 'Siswa') }}', {{ $receipt->loans->count() }})"
                                                    class="btn-press px-2.5 py-1 rounded-lg bg-white border border-slate-300 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 text-xs font-semibold transition active:scale-[0.98] shadow-2xs text-center">
                                                Setujui
                                            </button>
                                        @elseif($receipt->isExtensionPending())
                                            <button type="button" 
                                                    @click="openApproveExtendModal('{{ route('loan-receipts.approve-extension', $receipt) }}', '{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}', '{{ addslashes($receipt->user?->name ?? 'Siswa') }}', '{{ $receipt->due_date ? $receipt->due_date->translatedFormat('d F Y') : '-' }}', '{{ $receipt->due_date ? $receipt->due_date->copy()->addDay()->toDateString() : date('Y-m-d') }}', '{{ $receipt->due_date ? $receipt->due_date->copy()->addDays(7)->toDateString() : date('Y-m-d', strtotime('+7 days')) }}')"
                                                    class="btn-press px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-300 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition active:scale-[0.98] shadow-2xs text-center">
                                                ACC Perpanjang
                                            </button>
                                            <button type="button" 
                                                    @click="openRejectExtendModal('{{ route('loan-receipts.reject-extension', $receipt) }}', '{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}', '{{ addslashes($receipt->user?->name ?? 'Siswa') }}')"
                                                    class="btn-press px-2 py-1 rounded-lg bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 text-xs font-semibold transition active:scale-[0.98] shadow-2xs text-center">
                                                Tolak
                                            </button>
                                        @endif
                                    @elseif(auth()->user()->isMurid())
                                        @if($isPending)
                                            <button type="button" 
                                                    @click="openCancelModal('{{ route('loan-receipts.cancel', $receipt) }}', '{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}', {{ $receipt->loans->count() }})"
                                                    class="btn-press px-2.5 py-1 rounded-lg bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 text-xs font-semibold transition active:scale-[0.98] shadow-2xs text-center">
                                                Batalkan
                                            </button>
                                        @elseif($receipt->canBeExtended())
                                            <button type="button" 
                                                    @click="openExtendModal('{{ route('loan-receipts.extend', $receipt) }}', '{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}', '{{ $receipt->due_date ? $receipt->due_date->translatedFormat('d F Y') : '-' }}', {{ $receipt->loans->count() }})"
                                                    class="btn-press px-2.5 py-1 rounded-lg bg-white border border-[#1a4f8b]/30 hover:bg-[#1a4f8b] hover:text-white text-[#1a4f8b] text-xs font-semibold transition active:scale-[0.98] shadow-2xs text-center">
                                                Perpanjang
                                            </button>
                                        @endif
                                    @endif
                                    <a href="{{ $url }}" class="btn-press inline-flex items-center justify-center gap-1 w-24 py-1 px-2.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium transition shadow-2xs active:scale-[0.98] shrink-0">
                                        <span>Lihat Struk</span>
                                        <span class="text-xs">&rarr;</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isPustakawan() ? 7 : 6 }}" class="py-12 text-center text-slate-400">
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

    <!-- Modal Konfirmasi Batalkan Pengajuan oleh Siswa -->
    <div x-cloak x-show="cancelModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="cancelModalOpen = false" 
             class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Batalkan Pengajuan Peminjaman</h3>
                    <p class="text-xs text-gray-500">Konfirmasi pembatalan reservasi buku</p>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Struk:</span>
                    <span class="font-mono font-semibold text-[#1a4f8b]" x-text="cancelReceiptNumber"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Jumlah Buku:</span>
                    <span class="font-semibold text-slate-800" x-text="cancelBookCount + ' buku'"></span>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin membatalkan pengajuan ini? Status struk akan dibatalkan dan kuota peminjaman Anda akan segera aktif kembali sehingga Anda bisa memilih atau mengajukan buku lain.
            </p>

            <form :action="cancelUrl" method="POST" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                @csrf
                <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                    Tutup
                </button>
                <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                    Ya, Batalkan Pengajuan
                </button>
            </form>
        </div>
    </div>

    <!-- Librarian New Loan Modal (multi-book, creates one receipt) -->
    @if(auth()->user()->isPustakawan())
        <!-- Modal Konfirmasi Persetujuan Peminjaman -->
        <div x-cloak x-show="approveModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="approveModalOpen = false" 
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Konfirmasi Persetujuan Peminjaman</h3>
                        <p class="text-xs text-gray-500">Persetujuan peminjaman buku oleh pustakawan</p>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Struk:</span>
                        <span class="font-mono font-semibold text-[#1a4f8b]" x-text="approveReceiptNumber"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Peminjam:</span>
                        <span class="font-semibold text-slate-900" x-text="approveStudentName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Jumlah Buku:</span>
                        <span class="font-semibold text-slate-800" x-text="approveBookCount + ' buku'"></span>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menyetujui peminjaman ini? Masa pinjam (7 hari) akan segera dimulai dan status struk diperbarui menjadi <strong>Disetujui / Aktif</strong>.
                </p>

                <form :action="approveUrl" method="POST" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    @csrf
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                        Ya, Setujui Sekarang
                    </button>
                </form>
            </div>
        </div>

        <div x-cloak x-show="newLoanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="newLoanModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Catat Transaksi Peminjaman Baru</h3>
                        <p class="text-[11px] text-gray-500">Mencatat peminjaman hingga {{ \App\Http\Controllers\CartController::MAX_ITEMS }} buku sekaligus ke dalam 1 struk.</p>
                    </div>
                    <button type="button" @click="newLoanModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form action="{{ route('loans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">NIS Siswa Peminjam <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nip" required placeholder="Masukkan NIS siswa (contoh: 2024101)..." class="w-full px-3 py-2 border border-[#e2e8f0] rounded-xl text-xs font-mono bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Buku <span class="text-rose-500">*</span></label>
                            <span class="text-[11px] text-slate-500 font-medium" x-text="selectedBooks.length + ' / ' + maxBooks + ' judul dipilih (maks. ' + maxBooks + ')'"></span>
                        </div>
                        <input type="text" x-model="bookSearch" placeholder="Cari judul buku atau ISBN..." class="w-full px-3 py-2 border border-[#e2e8f0] rounded-xl text-xs mb-2 outline-none focus:border-[#1a4f8b] focus:ring-1 focus:ring-[#1a4f8b]">
                        <div class="max-h-48 overflow-y-auto border border-[#e2e8f0] rounded-xl divide-y divide-slate-100">
                            <template x-for="book in filteredBooks" :key="book.id">
                                <label class="flex items-center gap-2.5 px-3 py-2 text-xs hover:bg-slate-50 cursor-pointer" :class="isDisabled(book.id) ? 'opacity-50 cursor-not-allowed' : ''">
                                    <input type="checkbox" name="book_ids[]" :value="book.id" x-model="selectedBooks" :disabled="isDisabled(book.id)" class="rounded border-slate-300 text-[#1a4f8b] focus:ring-[#1a4f8b]">
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
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Pinjam</label>
                            <input type="date" name="loan_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-xl text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-xl text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="newLoanModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" :disabled="selectedBooks.length === 0" class="btn-press px-4 py-2 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold shadow-2xs transition">Catat Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    <!-- Modal Ajukan Perpanjangan oleh Siswa -->
    <div x-cloak x-show="extendModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="extendModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-[#1a4f8b] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Ajukan Perpanjangan Peminjaman</h3>
                    <p class="text-xs text-gray-500">Perpanjangan masa pinjam buku</p>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Struk:</span>
                    <span class="font-mono font-semibold text-[#1a4f8b]" x-text="extendReceiptNumber"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Batas Kembali Saat Ini:</span>
                    <span class="font-semibold text-slate-800" x-text="extendDueDate"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Jumlah Buku:</span>
                    <span class="font-semibold text-slate-800" x-text="extendBookCount + ' buku'"></span>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Perpanjangan berlaku untuk seluruh buku pada struk ini dan hanya dapat dilakukan maksimal 1 kali. Pengajuan akan diproses dan diverifikasi oleh pustakawan loket.
            </p>

            <form :action="extendUrl" method="POST" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                @csrf
                <button type="button" @click="extendModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                    Ya, Kirim Pengajuan
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Setujui Perpanjangan oleh Pustakawan -->
    <div x-cloak x-show="approveExtendModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="approveExtendModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Setujui Perpanjangan Peminjaman</h3>
                    <p class="text-xs text-gray-500">Tentukan batas kembali baru untuk struk ini</p>
                </div>
            </div>

            <form :action="approveExtendUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Struk:</span>
                        <span class="font-mono font-semibold text-[#1a4f8b]" x-text="approveExtendReceiptNumber"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Peminjam:</span>
                        <span class="font-semibold text-slate-800" x-text="approveExtendStudentName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Jatuh Tempo Lama:</span>
                        <span class="font-semibold text-slate-700" x-text="approveExtendCurrentDue"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo Baru:</label>
                    <input type="date" 
                           name="new_due_date" 
                           required 
                           :min="approveExtendMinDate" 
                           :value="approveExtendDefaultDate" 
                           class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white">
                    <span class="text-[10px] text-slate-500 mt-1 block">Rekomendasi otomatis: +7 hari dari batas waktu saat ini.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Pustakawan (Opsional):</label>
                    <textarea name="notes" rows="2" placeholder="Catatan perpanjangan..." class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="approveExtendModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                        Setujui Perpanjangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tolak Perpanjangan oleh Pustakawan -->
    <div x-cloak x-show="rejectExtendModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="rejectExtendModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Tolak Pengajuan Perpanjangan</h3>
                    <p class="text-xs text-gray-500">Konfirmasi penolakan perpanjangan struk</p>
                </div>
            </div>

            <form :action="rejectExtendUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Struk:</span>
                        <span class="font-mono font-semibold text-[#1a4f8b]" x-text="rejectExtendReceiptNumber"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Peminjam:</span>
                        <span class="font-semibold text-slate-800" x-text="rejectExtendStudentName"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alasan Penolakan (Opsional):</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Buku telah direservasi peminjam lain..." class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectExtendModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                        Tolak Perpanjangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
