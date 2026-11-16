@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    checkinModal: false,
    nis: '',
    purpose: 'Membaca',
    notes: '',
    submitVisit() {
        fetch('{{ route('visits.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Alpine-Request': 'true'
            },
            body: JSON.stringify({
                nis_nip: this.nis,
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
                Alpine.store('toast').notify(data.message || 'Gagal menyimpan presensi', 'error');
            }
        })
        .catch(() => {
            Alpine.store('toast').notify('Koneksi LAN terputus', 'error');
        });
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 scroll-reveal">
        <div>
            <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">
                Pemantauan & Presensi Siswa
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                {{ auth()->user()->isPustakawan() ? 'Log Kunjungan Perpustakaan' : 'Riwayat Kunjungan Saya' }}
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                {{ auth()->user()->isPustakawan() ? 'Rekapitulasi presensi kehadiran siswa dan civitas sekolah di perpustakaan SMA Negeri 24.' : 'Catatan riwayat kunjungan dan kehadiran Anda di perpustakaan.' }}
            </p>
        </div>

        @if(auth()->user()->isPustakawan())
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="checkinModal = true" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Kunjungan
                </button>

                <a href="{{ route('visits.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98]" title="Ekspor laporan siap cetak ke format Excel">
                    <svg class="w-4 h-4 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Laporan (XLSX)
                </a>
            </div>
        @endif
    </div>

    <!-- Filter Bar (Librarian) -->
    @if(auth()->user()->isPustakawan())
        <div class="scroll-reveal delay-1 bg-white p-4.5 rounded-xl border border-[#e2e8f0] shadow-xs card-hover">
            <form method="GET" action="{{ route('visits.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Pencarian Siswa</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau NIS..." class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-press w-full py-2 px-3.5 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                        Terapkan Filter
                    </button>
                    @if($search || request('start_date') || request('end_date'))
                        <a href="{{ route('visits.index') }}" class="p-2 rounded-lg bg-[#e2e8f0]/60 hover:bg-[#e2e8f0] text-gray-700 transition shrink-0" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <!-- Visits Table Card with Scroll Reveal & Hover -->
    <div class="scroll-reveal delay-2 bg-white rounded-xl border border-[#e2e8f0] overflow-hidden shadow-xs card-hover">
        <!-- Mobile Swipe Helper Indicator -->
        <div class="md:hidden px-4 py-2 bg-[#f8fafc] text-[11px] text-gray-500 border-b border-[#e2e8f0] flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#1a4f8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
                Geser tabel ke samping untuk melihat detail
            </span>
            <span class="text-[10px] font-mono text-gray-400">Scroll &rarr;</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-xs">
                <thead class="bg-[#e2e8f0]/50 border-b border-[#e2e8f0] text-gray-600 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">NIS / NIP</th>
                        <th class="py-3 px-4">Nama Pengunjung</th>
                        <th class="py-3 px-4 text-center">Tujuan</th>
                        <th class="py-3 px-4">Catatan</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4 text-center w-16">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e8f0]/60">
                    @forelse($visits as $index => $visit)
                        <tr class="hover:bg-[#1a4f8b]/5 hover:translate-x-0.5 transition-all duration-150">
                            <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-[11px]">
                                {{ $visits->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($visit->visit_date)->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $visit->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-gray-700 text-[11px]">
                                {{ $visit->user?->nis_nip ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900 hover:text-[#1a4f8b] transition">{{ $visit->user?->name ?? 'Tamu' }}</div>
                                <div class="text-[10px] text-gray-400 capitalize">{{ $visit->user?->role ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-[#e2e8f0]/70 text-gray-700">
                                    {{ $visit->purpose }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate">
                                {{ $visit->notes ?: '-' }}
                            </td>
                            @if(auth()->user()->isPustakawan())
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm('Hapus log kunjungan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Log">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isPustakawan() ? 7 : 6 }}" class="py-12 text-center text-gray-400">
                                Belum ada data kunjungan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="p-4 border-t border-[#e2e8f0]">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

    @if(auth()->user()->isPustakawan())
        <!-- Manual Visit Check-in Modal -->
        <div x-cloak x-show="checkinModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="checkinModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-[#e2e8f0] space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-[#e2e8f0] pb-3">
                    <h3 class="font-bold text-gray-900 text-sm">Catat Kunjungan Baru</h3>
                    <button @click="checkinModal = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">NIS / NIP Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="nis" placeholder="Contoh: 2024101" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs font-mono bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tujuan Kunjungan</label>
                        <select x-model="purpose" class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                            <option value="Membaca">Membaca Buku / Referensi</option>
                            <option value="Meminjam">Meminjam / Mengembalikan Buku</option>
                            <option value="Tugas">Mengerjakan Tugas</option>
                            <option value="Diskusi">Diskusi Belajar Kelompok</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Catatan (Opsional)</label>
                        <input type="text" x-model="notes" placeholder="Catatan kunjungan..." class="w-full px-3 py-2 border border-[#e2e8f0] rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b]">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#e2e8f0]">
                        <button type="button" @click="checkinModal = false" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</button>
                        <button type="button" @click="submitVisit()" class="btn-press px-3.5 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-medium shadow-2xs transition active:scale-[0.98]">
                            Simpan Presensi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
