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
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ auth()->user()->isPustakawan() ? 'Sirkulasi & Peminjaman Buku' : 'Riwayat Peminjaman Buku Saya' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ auth()->user()->isPustakawan() ? 'Persetujuan pinjaman, pemantauan masa tenggang, dan pencatatan pengembalian buku.' : 'Pantau status pinjaman aktif, tanggal jatuh tempo, dan riwayat buku yang telah selesai Anda baca.' }}
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
                    '' => 'Semua Sirkulasi',
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'dipinjam' => 'Sedang Dipinjam',
                    'terlambat' => 'Terlambat',
                    'kembali' => 'Sudah Kembali',
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
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama peminjam, NIS, judul buku, atau ISBN..." class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
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

    <!-- Loans Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">ID</th>
                        <th class="py-3 px-4">Informasi Buku</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4">Peminjam</th>
                        @endif
                        <th class="py-3 px-4 text-center">Tgl Pinjam</th>
                        <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        @if(auth()->user()->isPustakawan())
                            <th class="py-3 px-4 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($loans as $loan)
                        @php
                            $isOverdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && in_array($loan->status, ['dipinjam', 'terlambat']);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition {{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-semibold">
                                #{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($loan->book?->cover_url)
                                        <img src="{{ $loan->book->cover_url }}" alt="{{ $loan->book->title }}" class="w-9 h-12 rounded object-cover border border-slate-200 shadow-2xs shrink-0">
                                    @else
                                        <div class="w-9 h-12 rounded bg-slate-800 text-blue-300 flex items-center justify-center shrink-0 font-bold text-[9px] uppercase shadow-2xs">
                                            BUKU
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $loan->book?->title ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            ISBN: <span class="font-mono text-slate-600">{{ $loan->book?->isbn ?? '-' }}</span> &bull; Rak: {{ $loan->book?->rack_location ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if(auth()->user()->isPustakawan())
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $loan->user?->name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIS: {{ $loan->user?->nis_nip ?? '-' }}</div>
                                </td>
                            @endif
                            <td class="py-3.5 px-4 text-center text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($loan->loan_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($loan->status === 'menunggu_persetujuan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        Menunggu Persetujuan
                                    </span>
                                @elseif($isOverdue)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 animate-pulse">
                                        Terlambat
                                    </span>
                                @elseif($loan->status === 'dipinjam')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">
                                        Dipinjam
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->isPustakawan())
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
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
                                            <span class="text-slate-400 text-[11px] font-mono">Selesai</span>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isPustakawan() ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                                Tidak ada catatan peminjaman pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loans->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

    <!-- Librarian New Loan Modal -->
    @if(auth()->user()->isPustakawan())
        <div x-cloak x-show="newLoanModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.outside="newLoanModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Catat Transaksi Peminjaman Baru</h3>
                    <button @click="newLoanModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('loans.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIS Siswa Peminjam <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis_nip" required placeholder="Masukkan NIS siswa (contoh: 2024101)..." class="w-full px-3 py-2 border rounded-xl text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Buku <span class="text-rose-500">*</span></label>
                        <select name="book_id" required class="w-full px-3 py-2 border rounded-xl text-xs bg-white">
                            <option value="">Pilih judul buku...</option>
                            @foreach(\App\Models\Book::available()->orderBy('title')->get() as $b)
                                <option value="{{ $b->id }}">{{ $b->title }} (Stok: {{ $b->available_stock }})</option>
                            @endforeach
                        </select>
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
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition">Catat Peminjaman</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Librarian Return Modal -->
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
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pustakawan</label>
                        <textarea name="librarian_notes" x-model="notes" rows="2" placeholder="Catatan kondisi buku..." class="w-full px-3 py-2 border rounded-xl text-xs"></textarea>
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
