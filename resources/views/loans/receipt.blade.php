@extends('layouts.app')

@section('content')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        /* Explicitly hide every layout and action component */
        header, 
        footer, 
        nav, 
        aside, 
        .no-print, 
        [role="navigation"],
        .print\:hidden {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body, html {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: auto !important;
            height: auto !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .receipt-container {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .receipt-paper {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
    }
</style>

<div class="max-w-3xl mx-auto receipt-container pb-12">
    <!-- Action Toolbar (Hidden when printing) -->
    <div class="no-print mb-6 space-y-3">
        <!-- Main Buttons Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-white rounded-2xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center gap-2">
                <a href="{{ route('loans.index') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition shadow-xs active:scale-95">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Sirkulasi</span>
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('approve', $receipt)
                    <form method="POST" action="{{ route('loan-receipts.approve', $receipt) }}" class="inline-block">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setujui Semua Buku</span>
                        </button>
                    </form>
                @endcan

                <button onclick="window.print()" 
                        class="btn-sheen inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-[#102a54] to-[#1a4f8b] hover:from-[#0d2244] hover:to-[#153e75] rounded-xl shadow-xs transition active:scale-95">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Struk</span>
                </button>
            </div>
        </div>

        <!-- Librarian Return Bulk Action -->
        @can('returnBook', $receipt)
            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl">
                <div class="text-xs font-semibold text-amber-900 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Proses Pengembalian Seluruh Buku (Pustakawan)
                </div>
                <form method="POST" 
                      action="{{ route('loan-receipts.return', $receipt) }}" 
                      class="flex flex-wrap items-center gap-2.5" 
                      onsubmit="return confirm('Apakah Anda yakin ingin memproses pengembalian seluruh buku pada struk ini?');">
                    @csrf
                    <div class="flex items-center gap-1.5">
                        <label class="text-[11px] font-medium text-slate-600">Tgl Kembali:</label>
                        <input type="date" 
                               name="return_date" 
                               value="{{ date('Y-m-d') }}" 
                               class="rounded-xl border-slate-300 text-xs px-2.5 py-1.5 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" 
                               name="librarian_notes" 
                               placeholder="Catatan kondisi buku (opsional)..." 
                               class="w-full rounded-xl border-slate-300 text-xs px-3 py-1.5 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white">
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Kembalikan Semua</span>
                    </button>
                </form>
            </div>
        @endcan

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Official Printable Receipt Paper -->
    <div class="receipt-paper bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8">
        <!-- School Letterhead (KOP) -->
        <div class="flex items-center justify-between pb-3 border-b-2 border-slate-800">
            <div class="w-16 shrink-0">
                <img src="{{ asset('images/logo-sman24.jpg') }}" alt="Logo SMAN 24" class="w-14 h-14 object-contain">
            </div>
            <div class="flex-1 text-center px-2">
                <div class="text-[10px] sm:text-[11px] font-semibold tracking-wider text-slate-700 uppercase">Pemerintah Daerah Provinsi Jawa Barat &bull; Dinas Pendidikan</div>
                <div class="text-sm sm:text-base font-extrabold text-[#102a54] tracking-wider uppercase mt-0.5">SMA NEGERI 24 BANDUNG</div>
                <div class="text-[10px] text-slate-600 font-medium mt-0.5">Unit Pelaksana Teknis Perpustakaan Digital &amp; Sirkulasi Koleksi</div>
                <div class="text-[9px] text-slate-500">Jl. AH. Nasution No. 27 / Pasir Kunci, Cibiru, Kota Bandung 40614</div>
            </div>
            <div class="w-16 shrink-0 flex justify-end">
                <div class="text-right">
                    <span class="inline-block px-2 py-0.5 text-[9px] font-mono font-bold bg-slate-100 border border-slate-300 rounded text-slate-700">STRUK RESMI</span>
                </div>
            </div>
        </div>
        <div class="h-0.5 bg-slate-800 mt-0.5 mb-5"></div>

        <!-- Document Title -->
        <div class="text-center mb-6">
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-wide uppercase">BUKTI PEMINJAMAN BUKU</h1>
            <div class="text-xs font-mono font-semibold text-slate-600 mt-0.5">Nomor: {{ $receipt->receipt_number }}</div>
        </div>

        <!-- Key Information Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-xs mb-6 bg-slate-50/70 p-4 rounded-xl border border-slate-200">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-28 text-slate-500">Nama Peminjam</span>
                    <span class="text-slate-900 font-semibold">: {{ $receipt->user->name }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-slate-500">NIS / NIP</span>
                    <span class="text-slate-900 font-medium">: {{ $receipt->user->nis_nip ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-slate-500">No. Kontak</span>
                    <span class="text-slate-900 font-medium">: {{ $receipt->user->phone ?? '-' }}</span>
                </div>
            </div>
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-28 text-slate-500">Tanggal Pinjam</span>
                    <span class="text-slate-900 font-semibold">: {{ $receipt->loan_date->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-slate-500">Batas Kembali</span>
                    <span class="text-slate-900 font-bold text-rose-700">: {{ $receipt->due_date->translatedFormat('d F Y') }}</span>
                </div>
                @if ($receipt->status === 'dikembalikan')
                    <div class="flex">
                        <span class="w-28 text-slate-500">Tgl Dikembalikan</span>
                        <span class="text-slate-900 font-semibold">: {{ \Carbon\Carbon::parse($receipt->loans->max('return_date'))->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
                <div class="flex items-center">
                    <span class="w-28 text-slate-500">Status Struk</span>
                    <span class="font-medium">: 
                        @if ($receipt->isPending())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">Menunggu Persetujuan</span>
                        @elseif ($receipt->status === 'dikembalikan')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Sudah Dikembalikan</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-100 text-[#102a54] border border-blue-200">Disetujui / Aktif</span>
                        @endif
                    </span>
                </div>
                @if ($receipt->processor)
                    <div class="flex">
                        <span class="w-28 text-slate-500">Petugas Loket</span>
                        <span class="text-slate-900 font-medium">: {{ $receipt->processor->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Books Table -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-300 bg-slate-100/75 text-slate-700">
                        <th class="py-2.5 px-3 font-bold w-10 text-center">#</th>
                        <th class="py-2.5 px-3 font-bold">Judul Buku</th>
                        <th class="py-2.5 px-3 font-bold w-32">ISBN</th>
                        <th class="py-2.5 px-3 font-bold w-24">Lokasi Rak</th>
                        <th class="py-2.5 px-3 font-bold w-32 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @foreach ($receipt->loans as $i => $loan)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 text-center font-medium text-slate-500">{{ $i + 1 }}</td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $loan->book->title }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-600 text-[11px]">{{ $loan->book->isbn }}</td>
                            <td class="py-2.5 px-3 text-slate-700 font-medium">{{ $loan->book->rack_location ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="capitalize text-[11px] font-medium text-slate-700">
                                    {{ str_replace('_', ' ', $loan->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 font-bold bg-slate-50/50">
                        <td colspan="4" class="py-2.5 px-3 text-right text-slate-800">Total Buku:</td>
                        <td class="py-2.5 px-3 text-center text-slate-900 font-extrabold">{{ $receipt->loans->count() }} Buku</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes / Disclaimer -->
        @if ($receipt->isPending())
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs flex items-start gap-2 mb-6">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <strong>Pemberitahuan:</strong> Struk ini merupakan bukti reservasi digital. Silakan tunjukkan struk ini kepada Pustakawan di loket sirkulasi untuk serah terima fisik buku dan aktivasi peminjaman.
                </div>
            </div>
        @else
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-[11px] leading-relaxed mb-6">
                <strong>Ketentuan Peminjaman:</strong> Harap menjaga keutuhan buku dan mengembalikan sebelum batas jatuh tempo. Keterlambatan pengembalian atau kerusakan buku akan dikenakan sanksi tata tertib perpustakaan SMAN 24 Bandung.
            </div>
        @endif

        <!-- Signatures Block -->
        <div class="mt-8 pt-4 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs text-slate-800">
            <div>
                <div class="text-slate-500 mb-1">Peminjam,</div>
                <div class="h-16"></div>
                <div class="font-bold underline decoration-slate-400 decoration-1 underline-offset-4">{{ $receipt->user->name }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">NIS/NIP: {{ $receipt->user->nis_nip ?? '-' }}</div>
            </div>
            <div>
                <div class="text-slate-500 mb-1">Bandung, {{ $receipt->loan_date->translatedFormat('d F Y') }}</div>
                <div class="text-slate-500">Petugas Perpustakaan,</div>
                <div class="h-12"></div>
                <div class="font-bold underline decoration-slate-400 decoration-1 underline-offset-4">{{ $receipt->processor->name ?? '..........................................' }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Petugas Loket Sirkulasi</div>
            </div>
        </div>
    </div>
</div>
@endsection
