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
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Pustakawan</h1>
            <p class="text-xs text-slate-500 mt-1">Pusat kontrol sirkulasi buku, kunjungan siswa, dan manajemen perpustakaan.</p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('books.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Buku
            </a>
            <a href="{{ route('visits.export') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cetak Lap. Kunjungan
            </a>
            <a href="{{ route('loans.export') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cetak Lap. Peminjaman
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Books -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Koleksi Judul</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_titles']) }}</div>
                <div class="text-xs text-slate-500 mt-1">Total stok: <span class="font-semibold text-slate-700">{{ number_format($stats['total_stock']) }} eks</span> (Tersedia: {{ number_format($stats['available_stock']) }})</div>
            </div>
        </div>

        <!-- Card 2: Today Visits -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kunjungan Hari Ini</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['today_visits']) }}</div>
                <div class="text-xs text-slate-500 mt-1">Siswa & civitas presensi mandiri</div>
            </div>
        </div>

        <!-- Card 3: Active Loans & Pending -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sirkulasi Aktif</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['active_loans']) }}</div>
                <div class="text-xs text-slate-500 mt-1">
                    <span class="font-medium text-amber-600">{{ $stats['pending_loans'] }} menunggu persetujuan</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Overdue Loans -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterlambatan</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-rose-600">{{ number_format($stats['overdue_loans']) }}</div>
                <div class="text-xs text-slate-500 mt-1">Buku melewati batas jatuh tempo</div>
            </div>
        </div>
    </div>

    <!-- Two Columns: Recent Visits & Recent Loans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Recent Visits Monitoring -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Monitoring Kunjungan Hari Ini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <a href="{{ route('visits.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($recentVisits as $visit)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs uppercase">
                                {{ substr($visit->user?->name ?? 'G', 0, 2) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-900">{{ $visit->user?->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">NIS/NIP: {{ $visit->user?->nis_nip ?? '-' }} &bull; {{ $visit->created_at->format('H:i') }} WIB</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $visit->purpose }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Belum ada siswa yang presensi kunjungan hari ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Recent Loans & Approvals -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Aktivitas Peminjaman Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Persetujuan pinjam & pengembalian buku</p>
                </div>
                <a href="{{ route('loans.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                    Kelola Sirkulasi &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($recentLoans as $loan)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 transition">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-slate-900">{{ $loan->book?->title ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500">
                                Peminjam: <span class="font-medium text-slate-700">{{ $loan->user?->name }}</span> ({{ $loan->user?->nis_nip }})
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Pinjam: {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }} &bull; Tempo: {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($loan->status === 'menunggu_persetujuan')
                                <form method="POST" action="{{ route('loans.approve', $loan) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-[11px] shadow-2xs transition">
                                        Setujui
                                    </button>
                                </form>
                            @elseif(in_array($loan->status, ['dipinjam', 'terlambat']))
                                <button type="button" @click="openReturnModal({{ json_encode($loan) }})" class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-[11px] shadow-2xs transition">
                                    Kembalikan
                                </button>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    Kembali
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Belum ada riwayat transaksi peminjaman.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Return Book Modal (Alpine.js) -->
    <div x-cloak x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="returnModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Proses Pengembalian Buku</h3>
                <button @click="returnModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="selectedLoan">
                <form :action="'/loans/' + selectedLoan.id + '/return'" method="POST" class="space-y-4">
                    @csrf
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs space-y-1">
                        <div><span class="text-slate-500">Judul:</span> <strong class="text-slate-900" x-text="selectedLoan.book ? selectedLoan.book.title : '-'"></strong></div>
                        <div><span class="text-slate-500">Peminjam:</span> <strong class="text-slate-900" x-text="selectedLoan.user ? selectedLoan.user.name : '-'"></strong></div>
                        <div><span class="text-slate-500">Jatuh Tempo:</span> <span class="font-mono text-slate-700" x-text="selectedLoan.due_date"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pengembalian</label>
                        <input type="date" name="return_date" x-model="returnDate" class="w-full px-3 py-2 border rounded-xl text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pustakawan (Kondisi Buku)</label>
                        <textarea name="librarian_notes" x-model="notes" rows="2" placeholder="Contoh: Kondisi buku baik dan lengkap" class="w-full px-3 py-2 border rounded-xl text-xs"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="returnModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">Konfirmasi Selesai</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
