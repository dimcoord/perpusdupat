@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    checkinModal: false,
    purpose: 'Membaca',
    notes: '',
    submitCheckin() {
        fetch('{{ route('visits.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Alpine-Request': 'true'
            },
            body: JSON.stringify({
                purpose: this.purpose,
                notes: this.notes
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Alpine.store('toast').notify(data.message, 'success');
                this.checkinModal = false;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Alpine.store('toast').notify(data.message || 'Gagal melakukan presensi', 'error');
            }
        })
        .catch(() => {
            Alpine.store('toast').notify('Koneksi server LAN terganggu', 'error');
        });
    }
}">
    <!-- Greeting & Quick Check-in Hero -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-medium mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Siswa Aktif &bull; NIS: {{ auth()->user()->nis_nip }}
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                Selamat datang di portal perpustakaan digital. Jelajahi ribuan koleksi buku referensi, ajukan pinjaman, dan pantau masa pengembalian Anda.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="checkinModal = true" class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Presensi Kunjungan Hari Ini
            </button>
            <a href="{{ route('books.index') }}" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/10 transition backdrop-blur-xs">
                Cari Buku
            </a>
        </div>
    </div>

    <!-- Student Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pinjaman Aktif</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['active_loans'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Buku yang sedang Anda pinjam</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai Dikembalikan</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['completed_loans'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Total riwayat peminjaman</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Presensi Kunjungan</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_visits'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Kehadiran di perpustakaan</div>
        </div>
    </div>

    <!-- Active Loans Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 text-sm">Buku yang Sedang Dipinjam</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pastikan mengembalikan tepat waktu sebelum jatuh tempo.</p>
            </div>
            <a href="{{ route('loans.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                Lihat Semua Riwayat &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($myActiveLoans as $loan)
                @php
                    $isOverdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 transition">
                    <div class="flex items-start gap-3">
                        @if($loan->book?->cover_url)
                            <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->title }}" class="w-12 h-16 rounded-lg object-cover border border-slate-200 shadow-2xs shrink-0">
                        @else
                            <div class="w-12 h-16 rounded-lg bg-blue-100/70 border border-blue-200/60 flex items-center justify-center text-blue-700 shrink-0 font-bold text-xs uppercase shadow-2xs">
                                BUKU
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $loan->book?->title ?? '-' }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Penulis: {{ $loan->book?->author ?? 'Anonim' }} &bull; ISBN: {{ $loan->book?->isbn }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">Lokasi Rak: <span class="font-medium text-slate-700">{{ $loan->book?->rack_location ?? '-' }}</span></div>
                        </div>
                    </div>

                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-1 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                        @if($loan->status === 'menunggu_persetujuan')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                Menunggu Verifikasi Pustakawan
                            </span>
                        @elseif($isOverdue)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 animate-pulse">
                                Terlambat (Jatuh Tempo: {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }})
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                Dipinjam (Tempo: {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }})
                            </span>
                        @endif
                        <span class="text-[10px] text-slate-400">Tgl Pinjam: {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="text-sm font-medium text-slate-700">Tidak ada peminjaman buku yang sedang aktif.</p>
                    <p class="text-xs text-slate-400 mt-1">Ingin membaca buku? Jelajahi katalog dan ajukan peminjaman sekarang.</p>
                    <a href="{{ route('books.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs mt-4 shadow-sm transition">
                        Jelajah Koleksi Buku
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Check-in Modal -->
    <div x-cloak x-show="checkinModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="checkinModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Presensi Kunjungan Perpustakaan</h3>
                <button @click="checkinModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan Kunjungan</label>
                    <select x-model="purpose" class="w-full px-3 py-2 border rounded-xl text-xs bg-slate-50">
                        <option value="Membaca">Membaca Buku / Referensi</option>
                        <option value="Meminjam">Meminjam / Mengembalikan Buku</option>
                        <option value="Tugas">Mengerjakan Tugas Sekolah</option>
                        <option value="Diskusi">Diskusi Belajar Kelompok</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" x-model="notes" placeholder="Contoh: Tugas Biologi Bab 3" class="w-full px-3 py-2 border rounded-xl text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="checkinModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="button" @click="submitCheckin()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition">
                        Simpan Presensi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
