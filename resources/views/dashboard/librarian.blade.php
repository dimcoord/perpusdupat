@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    returnModal: false,
    selectedLoan: null,
    returnDate: '{{ date('Y-m-d') }}',
    notes: '',
    openReturnModal(loan) {
        this.selectedLoan = loan;
        this.returnDate = '{{ date('Y-m-d') }}';
        this.notes = '';
        this.returnModal = true;
    }
}">
    <!-- Header Page & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 scroll-reveal">
        <div>
            <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                Panel Manajemen Sirkulasi
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard Pustakawan</h1>
            <p class="text-xs text-gray-500 mt-1">Pusat kontrol sirkulasi buku, kunjungan siswa, dan manajemen perpustakaan SMA Negeri 24.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('books.create') }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Buku
            </a>
            <a href="{{ route('books.index') }}" class="btn-press inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Laporan Buku
            </a>
            <a href="{{ route('loans.export') }}" class="btn-press inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Lap. Peminjaman
            </a>
        </div>
    </div>

    <!-- 4 Metrics Cards Grid with Scroll-Reveal & Hover Lift -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Koleksi Judul -->
        <div class="scroll-reveal delay-1 bg-white p-4.5 rounded-xl border border-[#e2e8f0] flex flex-col justify-between shadow-xs card-hover group cursor-default">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold tracking-wider text-gray-500 uppercase">Koleksi Judul</span>
                <span class="w-7 h-7 rounded-lg bg-[#e2e8f0]/60 flex items-center justify-center text-[#1a4f8b] group-hover:scale-110 group-hover:bg-[#1a4f8b] group-hover:text-white transition duration-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-bold text-gray-900 tracking-tight font-sans">{{ number_format($stats['total_titles']) }}</div>
                <div class="text-[11px] text-gray-500 mt-1 flex items-center gap-1.5">
                    <span>Stok: {{ number_format($stats['total_stock']) }} eks</span>
                    <span>&bull;</span>
                    <span class="text-emerald-700 font-medium">Tersedia {{ number_format($stats['available_stock']) }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Kunjungan Hari Ini -->
        <div class="scroll-reveal delay-2 bg-white p-4.5 rounded-xl border border-[#e2e8f0] flex flex-col justify-between shadow-xs card-hover group cursor-default">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold tracking-wider text-gray-500 uppercase">Kunjungan Hari Ini</span>
                <span class="w-7 h-7 rounded-lg bg-[#e2e8f0]/60 flex items-center justify-center text-[#1a4f8b] group-hover:scale-110 group-hover:bg-[#1a4f8b] group-hover:text-white transition duration-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-bold text-gray-900 tracking-tight font-sans">{{ number_format($stats['today_visits']) }}</div>
                <div class="text-[11px] text-gray-500 mt-1">Siswa & civitas presensi mandiri</div>
            </div>
        </div>

        <!-- Card 3: Sirkulasi Aktif -->
        <div class="scroll-reveal delay-3 bg-white p-4.5 rounded-xl border border-[#e2e8f0] flex flex-col justify-between shadow-xs card-hover group cursor-default">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold tracking-wider text-gray-500 uppercase">Sirkulasi Aktif</span>
                <span class="w-7 h-7 rounded-lg bg-[#e2e8f0]/60 flex items-center justify-center text-[#1a4f8b] group-hover:scale-110 group-hover:bg-[#1a4f8b] group-hover:text-white transition duration-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-bold text-gray-900 tracking-tight font-sans">{{ number_format($stats['active_loans']) }}</div>
                <div class="text-[11px] text-amber-600 font-medium mt-1 flex flex-wrap items-center gap-1">
                    <span>{{ $stats['pending_loans'] }} menunggu persetujuan</span>
                    @if(($stats['pending_extensions'] ?? 0) > 0)
                        <span>&bull;</span>
                        <a href="{{ route('loans.index', ['status' => 'perpanjangan']) }}" class="hover:underline font-semibold text-amber-700">
                            {{ $stats['pending_extensions'] }} perpanjangan
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 4: Buku Terlambat -->
        <div class="scroll-reveal delay-4 bg-white p-4.5 rounded-xl border border-[#e2e8f0] flex flex-col justify-between shadow-xs card-hover group cursor-default">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold tracking-wider text-gray-500 uppercase">Buku Terlambat</span>
                <span class="w-7 h-7 rounded-lg bg-[#e2e8f0]/60 flex items-center justify-center text-[#1a4f8b] group-hover:scale-110 group-hover:bg-[#1a4f8b] group-hover:text-white transition duration-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-bold tracking-tight font-sans {{ $stats['overdue_loans'] > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ number_format($stats['overdue_loans']) }}</div>
                <div class="text-[11px] text-gray-500 mt-1">Melewati batas tempo</div>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Visits & Recent Loans with Scroll-Reveal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: Monitoring Kunjungan Hari Ini -->
        <div class="scroll-reveal delay-2 bg-white rounded-xl border border-[#e2e8f0] overflow-hidden flex flex-col shadow-xs card-hover">
            <div class="p-4 border-b border-[#e2e8f0] flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-gray-900">Monitoring Kunjungan Hari Ini</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::now()->format('l, d F Y') }}</p>
                </div>
                <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded-full bg-[#1a4f8b]/10 text-[#1a4f8b]">
                    Live Queue
                </span>
            </div>

            <!-- Table Container with Horizontal Scroll on Mobile -->
            <div class="overflow-x-auto">
                <div class="min-w-[480px]">
                    <!-- Table Header Strip -->
                    <div class="bg-[#e2e8f0]/50 px-4 py-2.5 text-[11px] font-semibold text-gray-600 uppercase tracking-wider grid grid-cols-12 gap-2 border-b border-[#e2e8f0]">
                        <div class="col-span-1">No</div>
                        <div class="col-span-4">Nama Siswa</div>
                        <div class="col-span-2">Waktu</div>
                        <div class="col-span-3">NIS</div>
                        <div class="col-span-2">Tujuan</div>
                    </div>

                    <!-- Table Body -->
                    <div class="divide-y divide-[#e2e8f0]/60 max-h-[380px] overflow-y-auto min-h-[260px] flex flex-col">
                        @forelse($recentVisits as $index => $visit)
                            <div class="px-4 py-3 text-xs text-gray-700 grid grid-cols-12 gap-2 items-center hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 transition-all duration-150">
                                <div class="col-span-1 text-gray-400 font-mono">{{ $index + 1 }}</div>
                                <div class="col-span-4 font-semibold text-gray-900 truncate">{{ $visit->user?->name ?? 'Tamu' }}</div>
                                <div class="col-span-2 text-gray-500 font-mono text-[11px]">{{ $visit->created_at->format('H:i') }} WIB</div>
                                <div class="col-span-3 text-gray-500 font-mono truncate text-[11px]">{{ $visit->user?->nis_nip ?? '-' }}</div>
                                <div class="col-span-2">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium bg-[#e2e8f0]/70 text-gray-700">
                                        {{ $visit->purpose ?? 'Membaca' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="m-auto py-16 px-4 text-center">
                                <div class="w-10 h-10 rounded-full bg-[#e2e8f0]/60 text-[#1a4f8b] flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-500">Belum ada siswa yang presensi kunjungan hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Aktivitas Peminjaman Terkini -->
        <div class="scroll-reveal delay-3 bg-white rounded-xl border border-[#e2e8f0] overflow-hidden flex flex-col p-4 shadow-xs card-hover">
            <div class="pb-3 border-b border-[#e2e8f0] flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm text-gray-900">Aktivitas Peminjaman Terkini</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Persetujuan pinjam & pengembalian buku</p>
                </div>
                <a href="{{ route('loans.index') }}" class="text-xs text-[#1a4f8b] hover:underline font-medium">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-[#e2e8f0]/60 max-h-[460px] overflow-y-auto">
                @php
                    // Loans of one receipt are shown (approved and returned) as one row
                    $loanRows = $recentLoans->groupBy(fn ($l) => $l->loan_receipt_id ? 'r'.$l->loan_receipt_id : 'l'.$l->id);
                @endphp

                @forelse($loanRows as $rowLoans)
                    @php
                        $loan = $rowLoans->first();
                        $receipt = $loan->loan_receipt_id ? $loan->receipt : null;
                        $receiptBooks = $receipt ? $receipt->loans->load('book') : collect();
                        $isBundle = $receipt && $receiptBooks->count() > 1;

                        if ($receipt) {
                            $rowStatus = $receipt->isPending()
                                ? 'menunggu_persetujuan'
                                : ($receiptBooks->whereIn('status', ['dipinjam', 'terlambat'])->isNotEmpty() ? 'dipinjam' : 'kembali');
                            $rowDue = $receipt->due_date;
                        } else {
                            $rowStatus = $loan->status;
                            $rowDue = $loan->due_date;
                        }

                        $returnPayload = [
                            'action' => $receipt ? route('loan-receipts.return', $receipt) : url('/loans/'.$loan->id.'/return'),
                            'due_date' => \Carbon\Carbon::parse($rowDue)->toDateString(),
                            'user' => ['name' => $loan->user?->name],
                            'book' => ['title' => $isBundle
                                ? $receiptBooks->count().' buku: '.$receiptBooks->pluck('book.title')->join(', ')
                                : $loan->book?->title],
                        ];
                        $dueDate = \Carbon\Carbon::parse($rowDue);
                        $isOverdue = $dueDate->isPast() && in_array($rowStatus, ['dipinjam', 'terlambat']);
                    @endphp
                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 transition-all duration-150 px-2 rounded-lg">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Book Cover / Thumbnail -->
                            <div class="w-10 h-13 rounded-md bg-[#e2e8f0]/60 flex items-center justify-center shrink-0 text-[#1a4f8b] shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            <div class="space-y-0.5 min-w-0">
                                <div class="text-xs font-bold text-gray-900 leading-snug truncate hover:text-[#1a4f8b] transition">
                                    @if($isBundle)
                                        {{ $receiptBooks->count() }} buku
                                        <span class="font-mono font-normal text-[10px] text-gray-400">&middot; {{ $receipt->receipt_number }}</span>
                                    @else
                                        {{ $loan->book?->title ?? '-' }}
                                    @endif
                                </div>
                                @if($isBundle)
                                    <div class="text-[11px] text-gray-500 truncate" title="{{ $receiptBooks->pluck('book.title')->join(', ') }}">
                                        {{ $receiptBooks->pluck('book.title')->join(', ') }}
                                    </div>
                                @endif
                                <div class="text-[11px] text-gray-500 truncate">
                                    Peminjam: <strong class="text-gray-700">{{ $loan->user?->name ?? '-' }}</strong> ({{ $loan->user?->nis_nip ?? '-' }})
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono flex flex-wrap items-center gap-1.5 pt-0.5">
                                    <span>Pinjam: {{ \Carbon\Carbon::parse($loan->loan_date ?? $loan->created_at)->format('d/m/Y') }}</span>
                                    <span>&bull;</span>
                                    <span>Tempo: <span class="{{ $isOverdue ? 'text-rose-600 font-semibold' : 'text-gray-600' }}">{{ $dueDate->format('d/m/Y') }}</span></span>

                                    @if($rowStatus === 'menunggu_persetujuan')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu
                                        </span>
                                    @elseif($receipt?->isExtensionPending())
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Perpanjangan
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Terlambat
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($rowStatus === 'menunggu_persetujuan' || $receipt?->isExtensionPending())
                                <a href="{{ $receipt ? route('loan-receipts.show', $receipt) : route('loans.index') }}" class="btn-press inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium transition shadow-2xs active:scale-[0.98]">
                                    <span>Lihat Struk</span>
                                    <span class="text-xs">&rarr;</span>
                                </a>
                            @elseif(in_array($rowStatus, ['dipinjam', 'terlambat']))
                                <button type="button" @click="openReturnModal({{ json_encode($returnPayload) }})" class="btn-press px-3.5 py-1.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium transition shadow-2xs">
                                    {{ $isBundle ? 'Kembalikan Semua' : 'Kembalikan' }}
                                </button>
                            @else
                                <span class="px-2.5 py-1 rounded text-[10px] font-medium bg-[#e2e8f0]/60 text-gray-600">
                                    Selesai
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-xs text-gray-500">
                        Belum ada riwayat transaksi peminjaman.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Return Book Modal (Alpine.js) -->
    <div x-cloak x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.outside="returnModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4 max-h-[90vh] overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Proses Pengembalian Buku</h3>
                <button type="button" @click="returnModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-[#e2e8f0]/40 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <template x-if="selectedLoan">
                <form :action="selectedLoan.action" method="POST" class="space-y-4">
                    @csrf
                    <div class="bg-[#e2e8f0]/25 p-3 rounded-lg border border-[#e2e8f0] text-xs space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Judul Buku:</span>
                            <strong class="text-gray-900 text-right leading-tight" x-text="selectedLoan.book ? selectedLoan.book.title : '-'"></strong>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500">Peminjam:</span>
                            <span class="font-medium text-gray-900" x-text="selectedLoan.user ? selectedLoan.user.name : '-'"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-[#e2e8f0]">
                            <span class="text-gray-500">Jatuh Tempo:</span>
                            <span class="font-mono text-gray-800 font-semibold" x-text="selectedLoan.due_date"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pengembalian</label>
                        <input type="date" name="return_date" x-model="returnDate" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Pustakawan (Kondisi Buku)</label>
                        <textarea name="librarian_notes" x-model="notes" rows="2" placeholder="Contoh: Kondisi buku baik, halaman utuh dan bersih" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#e2e8f0]">
                        <button type="button" @click="returnModal = false" class="px-4 py-2 rounded-lg text-xs font-medium text-gray-600 hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium shadow-xs transition active:scale-[0.98]">
                            Konfirmasi Selesai
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection