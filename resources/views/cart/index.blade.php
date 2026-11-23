@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 scroll-reveal">
    <div class="flex items-center justify-between pb-3 border-b border-[#e2e8f0]">
        <div>
            <div class="text-xs font-semibold text-[#1a4f8b] tracking-wide mb-1">Peminjaman Koleksi</div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Keranjang Peminjaman Buku</h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftar buku yang telah Anda pilih untuk diajukan sekaligus dalam satu struk peminjaman.</p>
        </div>
        <a href="{{ route('books.index') }}" class="btn-press px-3.5 py-1.5 rounded-lg border border-[#e2e8f0] bg-white hover:bg-slate-50 text-gray-700 text-xs font-medium shadow-2xs transition">
            &larr; Kembali ke Katalog
        </a>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($books->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#1a4f8b] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Keranjang Masih Kosong</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                Anda belum menambahkan buku ke dalam keranjang. Silakan pilih buku dari katalog untuk meminjam.
            </p>
            <a href="{{ route('books.index') }}" class="btn-press mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] text-white font-semibold text-xs shadow-2xs transition active:scale-95">
                Buka Katalog Buku &rarr;
            </a>
        </div>
    @else
        @if (auth()->user()->isMurid() && auth()->user()->hasUnfinishedLoans())
            <div class="mb-4 p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <strong>Perhatian:</strong> Anda belum dapat mengajukan peminjaman baru karena masih memiliki peminjaman sebelumnya yang belum selesai (menunggu persetujuan/penolakan petugas, atau buku belum dikembalikan).
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-700">Daftar Buku Terpilih ({{ $books->count() }} dari batas maksimal {{ $max }} judul buku dalam sekali pengajuan)</span>
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Kosongkan seluruh buku dari keranjang?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-rose-600 hover:text-rose-700 hover:underline text-[11px] font-medium transition">
                        Kosongkan Keranjang
                    </button>
                </form>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($books as $book)
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-14 rounded-lg bg-[#102a54] text-white flex items-center justify-center shrink-0 text-[10px] font-mono font-bold shadow-2xs">
                                DUPAT
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $book->title }}</h4>
                                <div class="text-[11px] text-slate-500 truncate">{{ $book->author }}</div>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">
                                    ISBN: {{ $book->isbn }} &bull; Rak: <span class="font-semibold text-blue-700">{{ $book->rack_location }}</span> &bull; Stok: <span class="text-emerald-700 font-semibold">{{ $book->available_stock }}</span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('cart.remove', $book) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition active:scale-95" title="Hapus dari keranjang">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="p-4 bg-slate-50/70 border-t border-slate-200">
                <form method="POST" action="{{ route('loans.store') }}" class="space-y-4">
                    @csrf

                    @if (auth()->user()->isPustakawan())
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">NIS Siswa Peminjam <span class="text-rose-500">*</span></label>
                            <input name="nis_nip" value="{{ old('nis_nip') }}" placeholder="Contoh: 2024101" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:border-[#1a4f8b] outline-none" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Pinjam</label>
                                <input type="date" name="loan_date" value="{{ old('loan_date', today()->toDateString()) }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Jatuh Tempo</label>
                                <input type="date" name="due_date" value="{{ old('due_date', today()->addDays(7)->toDateString()) }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs outline-none">
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">{{ $errors->first() }}</div>
                    @endif

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-slate-500">Struk otomatis diterbitkan setelah pengajuan</span>
                        <button type="submit" 
                                @if(auth()->user()->isMurid() && auth()->user()->hasUnfinishedLoans()) disabled title="Anda masih memiliki peminjaman yang belum selesai" @endif 
                                class="btn-press px-5 py-2.5 bg-[#1a4f8b] hover:bg-[#153e75] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs rounded-xl shadow-2xs transition active:scale-95">
                            {{ auth()->user()->isPustakawan() ? 'Catat Peminjaman & Buat Struk' : 'Ajukan Peminjaman & Terbitkan Struk' }} &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
