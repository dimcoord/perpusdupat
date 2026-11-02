@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    returnModal: false,
    selectedLoan: null,
    returnDate: '{{ date('Y-m-d') }}',
    fineAmount: 0,
    notes: '',
    openReturnModal(loan) {
        this.selectedLoan = loan;
        this.returnDate = '{{ date('Y-m-d') }}';
        this.notes = '';
        this.calculateFine(loan);
        this.returnModal = true;
    },
    calculateFine(loan) {
        let due = new Date(loan.due_date);
        let ret = new Date(this.returnDate);
        let diffTime = ret - due;
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays > 0) {
            this.fineAmount = diffDays * 1000;
        } else {
            this.fineAmount = 0;
        }
    }
}">
    <!-- Header Page & Editorial Quick Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-2 border-b border-[#E7E3DA]">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl lg:text-3xl font-serif font-bold text-stone-900 tracking-tight">Dashboard Pustakawan</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#EAEFD9] text-[#2B5941] border border-[#C6D8B3]">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#385A34] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#2B5941]"></span>
                    </span>
                    Operasional Aktif
                </span>
            </div>
            <p class="text-xs text-stone-500 mt-1.5 flex items-center gap-2">
                <span class="font-medium text-stone-700">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                <span class="text-stone-300">&bull;</span>
                <span>Pusat kontrol sirkulasi buku, kunjungan siswa, dan manajemen perpustakaan.</span>
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('books.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#183527] hover:bg-[#234A37] text-[#FAF8F5] border border-[#163324] font-medium text-xs shadow-xs hover:shadow transition active:scale-[0.98]">
                <svg class="w-4 h-4 text-[#E5C158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Buku
            </a>
            <a href="{{ route('visits.export') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-[#F5F2EB] text-stone-700 font-medium text-xs border border-[#E7E3DA] shadow-2xs hover:border-[#D6CEBF] transition active:scale-[0.98]">
                <svg class="w-4 h-4 text-[#2B5941]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cetak Lap. Kunjungan
            </a>
            <a href="{{ route('loans.export') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-[#F5F2EB] text-stone-700 font-medium text-xs border border-[#E7E3DA] shadow-2xs hover:border-[#D6CEBF] transition active:scale-[0.98]">
                <svg class="w-4 h-4 text-[#9A6A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cetak Lap. Peminjaman
            </a>
        </div>
    </div>

    <!-- Editorial Metrics Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Books -->
        @php
            $totalStock = max(1, (int) $stats['total_stock']);
            $availPercent = round(((int) $stats['available_stock'] / $totalStock) * 100);
        @endphp
        <div class="bg-white p-5 rounded-2xl border border-[#E7E3DA] shadow-xs hover:border-[#D6CEBF] hover:shadow-sm transition-all duration-200 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider">Koleksi Judul</span>
                    <span class="w-9 h-9 rounded-xl bg-[#F4EFEA] text-[#183527] border border-[#E7E0D6] flex items-center justify-center transition group-hover:scale-105">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-serif font-bold text-[#183527] tracking-tight">{{ number_format($stats['total_titles']) }}</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#F0ECE1] space-y-1.5">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-stone-500">Stok: <strong class="text-stone-800">{{ number_format($stats['total_stock']) }}</strong> eks</span>
                    <span class="font-medium text-[#2B5941] bg-[#EAEFD9] px-1.5 py-0.5 rounded text-[10px] border border-[#C6D8B3]">
                        {{ number_format($stats['available_stock']) }} Tersedia
                    </span>
                </div>
                <div class="w-full bg-[#F0ECE1] h-1.5 rounded-full overflow-hidden">
                    <div class="bg-[#183527] h-full rounded-full transition-all duration-500" style="width: {{ $availPercent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Today Visits -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E3DA] shadow-xs hover:border-[#D6CEBF] hover:shadow-sm transition-all duration-200 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider">Kunjungan Hari Ini</span>
                    <span class="w-9 h-9 rounded-xl bg-[#EAEFD9] text-[#2B5941] border border-[#C6D8B3] flex items-center justify-center transition group-hover:scale-105">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-serif font-bold text-[#183527] tracking-tight">{{ number_format($stats['today_visits']) }}</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#F0ECE1] flex items-center justify-between">
                <span class="text-[11px] text-stone-500">Presensi Kiosk & Manual</span>
                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-[#2B5941] bg-[#EAEFD9] px-1.5 py-0.5 rounded border border-[#C6D8B3]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2B5941]"></span> Live
                </span>
            </div>
        </div>

        <!-- Card 3: Active Loans & Pending -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E3DA] shadow-xs hover:border-[#D6CEBF] hover:shadow-sm transition-all duration-200 group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-wider">Sirkulasi Aktif</span>
                    <span class="w-9 h-9 rounded-xl bg-[#FDF4E7] text-[#9A6A1E] border border-[#F3DFC1] flex items-center justify-center transition group-hover:scale-105">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-serif font-bold text-[#183527] tracking-tight">{{ number_format($stats['active_loans']) }}</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-[#F0ECE1]">
                @if($stats['pending_loans'] > 0)
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium bg-[#FDF4E7] text-[#9A6A1E] border border-[#F3DFC1]">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#D4A373] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#9A6A1E]"></span>
                            </span>
                            {{ $stats['pending_loans'] }} butuh persetujuan
                        </span>
                        <a href="{{ route('loans.index') }}" class="text-[10px] font-semibold text-[#9A6A1E] hover:underline">Periksa &rarr;</a>
                    </div>
                @else
                    <span class="text-[11px] text-stone-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#2B5941]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tidak ada antrean tertunda
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Visits & Recent Loans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: Recent Visits Monitoring -->
        <div class="bg-white rounded-2xl border border-[#E7E3DA] shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-[#F0ECE1] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#EAEFD9] text-[#2B5941] flex items-center justify-center border border-[#C6D8B3]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-serif font-bold text-stone-900 text-sm leading-tight">Monitoring Kunjungan Hari Ini</h2>
                        <p class="text-[11px] text-stone-500 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('visits.index') }}" class="group inline-flex items-center gap-1 text-xs font-semibold text-[#183527] hover:text-[#2B5941] transition">
                    Lihat Semua
                    <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                </a>
            </div>

            <div class="divide-y divide-[#F0ECE1] max-h-[460px] overflow-y-auto">
                @php
                    $avatarPalettes = [
                        'bg-[#EAEFD9] text-[#2B5941] border-[#C6D8B3]',
                        'bg-[#FDF4E7] text-[#9A6A1E] border-[#F3DFC1]',
                        'bg-[#FBF0ED] text-[#B84233] border-[#F4D1CA]',
                        'bg-[#EBF1E8] text-[#385A34] border-[#C8DAC3]',
                        'bg-[#F4EFEA] text-[#183527] border-[#E7E0D6]',
                        'bg-[#F0ECE1] text-[#3D3833] border-[#DDD5C7]',
                    ];
                @endphp

                @forelse($recentVisits as $visit)
                    @php
                        $colorClass = $avatarPalettes[abs(crc32($visit->user?->name ?? 'G')) % count($avatarPalettes)];
                        $purpose = strtolower($visit->purpose ?? '');
                        $badgeStyle = match(true) {
                            str_contains($purpose, 'pinjam') => 'bg-[#FDF4E7] text-[#9A6A1E] border-[#F3DFC1]',
                            str_contains($purpose, 'baca') => 'bg-[#EAEFD9] text-[#2B5941] border-[#C6D8B3]',
                            default => 'bg-[#F0ECE1] text-stone-700 border-[#DDD5C7]',
                        };
                    @endphp
                    <div class="p-4 flex items-center justify-between hover:bg-[#FAF8F5] transition-colors duration-150 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl {{ $colorClass }} flex items-center justify-center font-bold text-xs uppercase border shrink-0">
                                {{ substr($visit->user?->name ?? 'G', 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-stone-900 truncate group-hover:text-[#183527] transition-colors">
                                    {{ $visit->user?->name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-stone-400 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-stone-500">NIS: {{ $visit->user?->nis_nip ?? '-' }}</span>
                                    <span class="text-stone-300">&bull;</span>
                                    <span class="inline-flex items-center gap-1 text-stone-500">
                                        <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $visit->created_at->format('H:i') }} WIB
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-medium border {{ $badgeStyle }}">
                                {{ $visit->purpose }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#F0ECE1] text-stone-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-stone-600">Belum ada presensi pengunjung hari ini</p>
                        <p class="text-[11px] text-stone-400 mt-1">Siswa dapat presensi mandiri melalui Kiosk atau diinput manual.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Recent Loans & Approvals -->
        <div class="bg-white rounded-2xl border border-[#E7E3DA] shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-[#F0ECE1] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#F4EFEA] text-[#183527] flex items-center justify-center border border-[#E7E0D6]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-serif font-bold text-stone-900 text-sm leading-tight">Aktivitas Peminjaman Terkini</h2>
                        <p class="text-[11px] text-stone-500 mt-0.5">Persetujuan pinjam & sirkulasi pengembalian buku</p>
                    </div>
                </div>
                <a href="{{ route('loans.index') }}" class="group inline-flex items-center gap-1 text-xs font-semibold text-[#183527] hover:text-[#2B5941] transition">
                    Kelola Sirkulasi
                    <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                </a>
            </div>

            <div class="divide-y divide-[#F0ECE1] max-h-[460px] overflow-y-auto">
                @forelse($recentLoans as $loan)
                    @php
                        $dueDate = \Carbon\Carbon::parse($loan->due_date);
                        $isOverdue = $dueDate->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                    @endphp
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-[#FAF8F5] transition-colors duration-150">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-[#F4EFEA] text-[#183527] border border-[#E7E0D6] flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            <div class="space-y-1 min-w-0">
                                <div class="text-xs font-serif font-bold text-stone-900 leading-snug truncate">
                                    {{ $loan->book?->title ?? '-' }}
                                </div>
                                <div class="text-[11px] text-stone-500 truncate">
                                    Peminjam: <span class="font-medium text-stone-800">{{ $loan->user?->name }}</span>
                                    <span class="text-stone-400 font-mono text-[10px]">({{ $loan->user?->nis_nip }})</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-stone-400 pt-0.5">
                                    <span>Pinjam: {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }}</span>
                                    <span class="text-stone-300">&bull;</span>
                                    <span class="{{ $isOverdue ? 'text-[#B84233] font-semibold' : 'text-stone-500' }}">
                                        Tempo: {{ $dueDate->format('d/m/Y') }}
                                    </span>

                                    @if($loan->status === 'menunggu_persetujuan')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FDF4E7] text-[#9A6A1E] border border-[#F3DFC1]">
                                            Menunggu
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FBF0ED] text-[#B84233] border border-[#F4D1CA]">
                                            Terlambat
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($loan->status === 'menunggu_persetujuan')
                                <form method="POST" action="{{ route('loans.approve', $loan) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#183527] hover:bg-[#234A37] text-white font-medium text-xs shadow-2xs hover:shadow-xs transition active:scale-95">
                                        <svg class="w-3.5 h-3.5 text-[#E5C158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Setujui
                                    </button>
                                </form>
                            @elseif(in_array($loan->status, ['dipinjam', 'terlambat']))
                                <button type="button" @click="openReturnModal({{ json_encode($loan) }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#2D2A26] hover:bg-[#1C1A17] text-white font-medium text-xs shadow-2xs hover:shadow-xs transition active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    Kembalikan
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-[#F0ECE1] text-stone-600 border border-[#DDD5C7]">
                                    <svg class="w-3 h-3 text-[#2B5941]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesai
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 px-4 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#F0ECE1] text-stone-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-stone-600">Belum ada riwayat transaksi peminjaman</p>
                        <p class="text-[11px] text-stone-400 mt-1">Transaksi peminjaman buku oleh siswa akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Return Book Modal (Alpine.js) -->
    <div x-cloak x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto bg-[#183527]/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.outside="returnModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[#E7E3DA] space-y-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between border-b border-[#F0ECE1] pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-[#EAEFD9] text-[#2B5941] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </div>
                    <h3 class="font-serif font-bold text-stone-900 text-sm">Proses Pengembalian Buku</h3>
                </div>
                <button type="button" @click="returnModal = false" class="text-stone-400 hover:text-stone-600 p-1 rounded-lg hover:bg-[#FAF8F5] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <template x-if="selectedLoan">
                <form :action="'/loans/' + selectedLoan.id + '/return'" method="POST" class="space-y-4">
                    @csrf
                    <div class="bg-[#FAF8F5] p-3.5 rounded-xl border border-[#E7E3DA] text-xs space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-stone-500 shrink-0">Judul Buku:</span>
                            <strong class="font-serif text-stone-900 text-right leading-tight" x-text="selectedLoan.book ? selectedLoan.book.title : '-'"></strong>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-stone-500">Peminjam:</span>
                            <span class="font-medium text-stone-900" x-text="selectedLoan.user ? selectedLoan.user.name : '-'"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-[#E7E3DA]/80">
                            <span class="text-stone-500">Jatuh Tempo:</span>
                            <span class="font-mono text-stone-800 font-semibold" x-text="selectedLoan.due_date"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Tanggal Pengembalian</label>
                        <input type="date" name="return_date" x-model="returnDate" @change="calculateFine(selectedLoan)" class="w-full px-3 py-2 border border-[#E7E3DA] rounded-xl text-xs focus:ring-2 focus:ring-[#183527] focus:border-[#183527] outline-none transition bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Pustakawan (Kondisi Buku)</label>
                        <textarea name="librarian_notes" x-model="notes" rows="2" placeholder="Contoh: Kondisi buku baik, halaman utuh dan bersih" class="w-full px-3 py-2 border border-[#E7E3DA] rounded-xl text-xs focus:ring-2 focus:ring-[#183527] focus:border-[#183527] outline-none transition bg-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F0ECE1]">
                        <button type="button" @click="returnModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:bg-[#FAF8F5] transition active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#183527] hover:bg-[#234A37] text-white text-xs font-semibold shadow-xs hover:shadow transition active:scale-[0.98]">
                            Konfirmasi Selesai
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
