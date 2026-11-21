@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    newLoanModal: false,
    returnModal: false,
    selectedLoan: null,
    returnDate: '{{ date('Y-m-d') }}',
    notes: '',
    
    // New loan form
    bookId: '',
    studentNis: '',
    loanDate: '{{ date('Y-m-d') }}',
    dueDate: '{{ date('Y-m-d', strtotime('+7 days')) }}',

    openReturnModal(loan) {
        this.selectedLoan = loan;
        this.returnDate = '{{ date('Y-m-d') }}';
        this.notes = '';
        this.returnModal = true;
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
                {{ auth()->user()->isPustakawan() ? 'Persetujuan pinjaman, pemantauan masa tenggang, dan pencatatan pengembalian buku.' : 'Pantau status pinjaman aktif, tanggal jatuh tempo, dan riwayat buku yang telah selesai Anda baca.' }}
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
                    '' => 'Semua Sirkulasi',
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'dipinjam' => 'Sedang Dipinjam',
                    'terlambat' => 'Terlambat',
                    'kembali' => 'Sudah Kembali',
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama peminjam, NIS, judul buku, atau ISBN..." class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
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

    @if(auth()->user()->isPustakawan())
        <!-- Loans Table for Librarian (High-Density Management View) -->
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
                            <th class="py-3 px-4 w-12 text-center">ID</th>
                            <th class="py-3 px-4">Informasi Buku</th>
                            <th class="py-3 px-4">Peminjam</th>
                            <th class="py-3 px-4 text-center">Tgl Pinjam</th>
                            <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($loans as $loan)
                            @php
                                $isOverdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                            @endphp
                            <tr class="hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 transition-all duration-150 {{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-medium text-[11px]">
                                    #{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        @if($loan->book?->cover_url)
                                            <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->title }}" class="w-9 h-12 rounded object-cover border border-slate-200 shrink-0 shadow-2xs">
                                        @else
                                            <div class="w-9 h-12 rounded bg-[#102a54] text-white flex items-center justify-center shrink-0 shadow-2xs">
                                                <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-slate-900 leading-snug hover:text-[#1a4f8b] transition">{{ $loan->book?->title ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                ISBN: {{ $loan->book?->isbn ?? '-' }} &bull; Rak: {{ $loan->book?->rack_location ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $loan->user?->name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIS: {{ $loan->user?->nis_nip ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-600 font-mono text-[11px]">
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-[11px]">
                                    <span class="font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($loan->status === 'menunggu_persetujuan')
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                                Menunggu Persetujuan
                                            </span>
                                            <span class="text-[9px] text-amber-600 mt-0.5 font-medium">Ambil loket maks. 2 hari</span>
                                        </div>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            Terlambat
                                        </span>
                                    @elseif($loan->status === 'dipinjam')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            Dipinjam
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Kembali
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($loan->status === 'menunggu_persetujuan')
                                            <form method="POST" action="{{ route('loans.approve', $loan) }}">
                                                @csrf
                                                <button type="submit" class="btn-press px-3 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition active:scale-[0.98]">
                                                    Setujui
                                                </button>
                                            </form>
                                        @elseif(in_array($loan->status, ['dipinjam', 'terlambat']))
                                            <button type="button" @click="openReturnModal({{ json_encode($loan) }})" class="btn-press px-3 py-1 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium transition shadow-2xs active:scale-[0.98]">
                                                Kembalikan
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-xs font-mono">Selesai</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Tidak ada catatan peminjaman pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($loans->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Student Modern Status Cards (Lively & Responsive) -->
        <div class="space-y-3.5 scroll-reveal delay-2">
            @forelse($loans as $loan)
                @php
                    $dueDate = \Carbon\Carbon::parse($loan->due_date);
                    $isOverdue = $dueDate->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                    $daysRemaining = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200/90 p-3.5 sm:p-5 shadow-xs hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 {{ $isOverdue ? 'border-l-4 border-l-rose-500' : ($loan->status === 'menunggu_persetujuan' ? 'border-l-4 border-l-amber-400' : ($loan->status === 'kembali' ? 'border-l-4 border-l-slate-300' : 'border-l-4 border-l-emerald-500')) }}">
                    <!-- Left: Book Cover & Details -->
                    <div class="flex items-start gap-3 sm:gap-4 min-w-0">
                        <div class="relative shrink-0 w-12 sm:w-16 aspect-[2/3] rounded-lg overflow-hidden shadow-xs border border-slate-200 bg-slate-100">
                            @if($loan->book?->cover_url)
                                <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-[#102a54] text-white flex flex-col items-center justify-center p-1 text-center">
                                    <span class="text-[8px] font-mono text-amber-300 font-bold uppercase">DUPAT</span>
                                    <svg class="w-4 h-4 text-blue-200 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif
                            <!-- Spine Crease -->
                            <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-r from-black/40 to-transparent pointer-events-none"></div>
                        </div>

                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono text-slate-400">ID: #{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-[10px] font-semibold text-[#1a4f8b] uppercase tracking-wide">
                                    Lokasi Rak: {{ $loan->book?->rack_location ?? '-' }}
                                </span>
                            </div>

                            <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                                {{ $loan->book?->title ?? '-' }}
                            </h3>

                            <p class="text-xs text-slate-500">
                                Penulis: {{ $loan->book?->author ?? 'Anonim' }} &bull; ISBN: {{ $loan->book?->isbn ?? '-' }}
                            </p>

                            <div class="pt-1 flex flex-wrap items-center gap-3 text-[11px] text-slate-500 font-mono">
                                <span>Tgl Pinjam: <strong>{{ \Carbon\Carbon::parse($loan->loan_date)->format('d M Y') }}</strong></span>
                                <span>&bull;</span>
                                <span>Jatuh Tempo: <strong>{{ $dueDate->format('d M Y') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Countdown / Status Badge -->
                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-1.5 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100 shrink-0">
                        @if($loan->status === 'menunggu_persetujuan')
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Menunggu Persetujuan</span>
                            </span>
                            <span class="text-[11px] text-amber-700 font-medium">Batas ambil di loket: maks 2 hari</span>
                        @elseif($isOverdue)
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                <span>Terlambat {{ abs($daysRemaining) }} Hari</span>
                            </span>
                            <span class="text-[11px] text-rose-600 font-semibold">Harap segera kembalikan buku</span>
                        @elseif($loan->status === 'dipinjam')
                            @if($daysRemaining <= 2)
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Jatuh Tempo {{ $daysRemaining <= 0 ? 'Hari Ini' : ($daysRemaining == 1 ? 'Besok' : 'Lusa') }}!</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Sedang Dipinjam · Sisa {{ $daysRemaining }} Hari</span>
                                </span>
                            @endif
                            <span class="text-[11px] text-slate-500">Kembalikan sebelum {{ $dueDate->format('d/m/Y') }}</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                <span>✓ Selesai & Dikembalikan</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">Bebas denda</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1a4f8b] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Tidak ada riwayat peminjaman pada kategori ini</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Semua catatan pinjaman aktif maupun yang telah selesai akan tercatat rapi di sini.</p>
                    <a href="{{ route('books.index') }}" class="btn-press inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a4f8b] text-white text-xs font-medium mt-4 shadow-xs">
                        <span>Jelajah Buku Sekarang</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endforelse

            @if($loans->hasPages())
                <div class="p-4 bg-white rounded-xl border border-slate-200">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Librarian New Loan Modal -->
    @if(auth()->user()->isPustakawan())
        <div x-cloak x-show="newLoanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="newLoanModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                    <h3 class="font-bold text-gray-900 text-sm">Catat Transaksi Peminjaman Baru</h3>
                    <button @click="newLoanModal = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                <form action="{{ route('loans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">NIS Siswa Peminjam <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nip" required placeholder="Masukkan NIS siswa (contoh: 2024101)..." class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs font-mono bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Pilih Buku <span class="text-rose-500">*</span></label>
                        <select name="book_id" required class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                            <option value="">Pilih judul buku...</option>
                            @foreach(\App\Models\Book::available()->orderBy('title')->get() as $b)
                                <option value="{{ $b->id }}">{{ $b->title }} (Stok: {{ $b->available_stock }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Pinjam</label>
                            <input type="date" name="loan_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#e2e8f0]">
                        <button type="button" @click="newLoanModal = false" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium shadow-2xs transition active:scale-[0.98]">Catat Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Librarian Return Modal -->
    <div x-cloak x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="returnModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Proses Pengembalian Buku</h3>
                <button @click="returnModal = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
            </div>

            <template x-if="selectedLoan">
                <form :action="'/loans/' + selectedLoan.id + '/return'" method="POST" class="space-y-4">
                    @csrf
                    <div class="bg-[#e2e8f0]/25 p-3.5 rounded-lg border border-[#e2e8f0] text-xs space-y-1">
                        <div><span class="text-gray-500">Judul:</span> <strong class="text-gray-900" x-text="selectedLoan.book ? selectedLoan.book.title : '-'"></strong></div>
                        <div><span class="text-gray-500">Peminjam:</span> <strong class="text-gray-900" x-text="selectedLoan.user ? selectedLoan.user.name : '-'"></strong></div>
                        <div><span class="text-gray-500">Jatuh Tempo:</span> <span class="font-mono text-gray-700" x-text="selectedLoan.due_date"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Pengembalian</label>
                        <input type="date" name="return_date" x-model="returnDate" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Catatan Pustakawan</label>
                        <textarea name="librarian_notes" x-model="notes" rows="2" placeholder="Catatan kondisi buku..." class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#e2e8f0]">
                        <button type="button" @click="returnModal = false" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium shadow-2xs transition active:scale-[0.98]">Konfirmasi Selesai</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
