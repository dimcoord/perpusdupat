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

<div class="max-w-3xl mx-auto receipt-container pb-12" x-data="{ approveModal: false, rejectModal: false, returnModal: false, cancelModal: false, extendModal: false, approveExtendModal: false, rejectExtendModal: false }">
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
                    <button type="button" 
                            @click="approveModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Setujui Semua Buku</span>
                    </button>
                @endcan

                @can('reject', $receipt)
                    <button type="button" 
                            @click="rejectModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Tolak Pengajuan</span>
                    </button>
                @endcan

                @can('cancel', $receipt)
                    @if(auth()->user()->isMurid())
                        <button type="button" 
                                @click="cancelModal = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Batalkan Pengajuan</span>
                        </button>
                    @endif
                @endcan

                @can('requestExtension', $receipt)
                    <button type="button" 
                            @click="extendModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-[#1a4f8b] hover:bg-[#153e75] rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Ajukan Perpanjangan</span>
                    </button>
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

        @if($receipt->isExtensionPending())
            <!-- Extension Pending Banner -->
            <div class="p-4 bg-amber-50/90 border border-amber-200 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-amber-900">Pengajuan Perpanjangan Peminjaman</div>
                        <div class="text-[11px] text-amber-700">
                            Diajukan pada {{ $receipt->extension_requested_at?->translatedFormat('d F Y, H:i') ?? '-' }}. Menunggu persetujuan pustakawan loket.
                        </div>
                    </div>
                </div>
                @if(auth()->user()->isPustakawan())
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="approveExtendModal = true"
                                class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setujui Perpanjangan</span>
                        </button>
                        <button type="button" 
                                @click="rejectExtendModal = true"
                                class="btn-press inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-rose-700 bg-white hover:bg-rose-50 border border-rose-300 rounded-xl shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Tolak</span>
                        </button>
                    </div>
                @endif
            </div>
        @elseif($receipt->isExtensionApproved())
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2 text-xs text-emerald-800 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Peminjaman pada struk ini telah diperpanjang 1x (Batas kembali baru: <strong>{{ $receipt->due_date->translatedFormat('d F Y') }}</strong>).@if($receipt->extension_notes) <span class="text-emerald-700 font-medium">Catatan: {{ $receipt->extension_notes }}</span>@endif</span>
            </div>
        @elseif($receipt->isExtensionRejected())
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2 text-xs text-rose-800 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Pengajuan perpanjangan untuk struk ini ditolak. Batas kembali tetap sesuai tanggal semula: <strong>{{ $receipt->due_date->translatedFormat('d F Y') }}</strong>.@if($receipt->extension_notes) <span class="text-rose-700 font-medium">Alasan: {{ $receipt->extension_notes }}</span>@endif</span>
            </div>
        @endif

        <!-- Librarian Return Bulk Action -->
        @can('returnBook', $receipt)
            <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl">
                <div class="text-xs font-semibold text-amber-900 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Proses Pengembalian Seluruh Buku (Pustakawan)
                </div>
                <form id="receipt-return-form"
                      method="POST" 
                      action="{{ route('loan-receipts.return', $receipt) }}" 
                      class="flex flex-wrap items-center gap-2.5">
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
                    <button type="button" 
                            @click="returnModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
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
        <!-- Header Struk (Perpustakaan SMA Negeri 24 Bandung & Alamat) -->
        <div class="flex items-center justify-center gap-3.5 pb-4 border-b border-slate-200 text-center">
            <img src="{{ asset('images/logo-sman24.jpg') }}" alt="Logo SMAN 24" class="w-12 h-12 rounded-full object-contain bg-white shadow-xs border border-slate-200 shrink-0">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-[#102a54] tracking-wide leading-tight">Perpustakaan SMA Negeri 24 Bandung</h2>
                <p class="text-xs text-slate-500 mt-0.5">Jl. AH. Nasution No. 27 / Pasir Kunci, Cibiru, Kota Bandung 40614</p>
            </div>
        </div>

        <!-- Document Title -->
        <div class="text-center my-5">
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
                @if ($receipt->extension_count > 0 || $receipt->extension_status)
                    <div class="flex">
                        <span class="w-28 text-slate-500">Perpanjangan</span>
                        <span class="font-semibold">: 
                            @if($receipt->isExtensionPending())
                                <span class="text-amber-700">Menunggu Persetujuan Loket</span>
                            @elseif($receipt->isExtensionApproved())
                                <span class="text-emerald-700">Sudah Diperpanjang ({{ $receipt->extension_count }}x)</span>
                            @elseif($receipt->isExtensionRejected())
                                <span class="text-rose-700">Ditolak</span>
                            @endif
                        </span>
                    </div>
                @endif
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
                        @elseif ($receipt->status === 'ditolak')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>
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
            <div class="py-2.5 text-xs text-slate-600 leading-relaxed border-t border-slate-200 mt-2">
                <span class="font-semibold text-slate-800">Pemberitahuan:</span> Struk ini merupakan bukti reservasi digital. Silakan tunjukkan struk ini kepada Pustakawan di loket sirkulasi untuk serah terima fisik buku dan aktivasi peminjaman.
            </div>
        @elseif ($receipt->status === 'ditolak')
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs flex items-start gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    Pengajuan peminjaman ini telah <strong>ditolak</strong> oleh petugas perpustakaan.@if($receipt->loans->first()?->librarian_notes) <span class="block mt-1 font-medium">Alasan: {{ $receipt->loans->first()->librarian_notes }}</span>@endif
                </div>
            </div>
        @else
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-[11px] leading-relaxed">
                <strong>Ketentuan Peminjaman:</strong> Harap menjaga keutuhan buku dan mengembalikan sebelum batas jatuh tempo. Keterlambatan pengembalian atau kerusakan buku akan dikenakan sanksi tata tertib perpustakaan SMAN 24 Bandung.
            </div>
        @endif
    </div>

    <!-- Modals (Hidden on print) -->
    <div class="no-print">
        @can('approve', $receipt)
            <!-- Modal Konfirmasi Setujui Struk -->
            <div x-cloak x-show="approveModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="approveModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Konfirmasi Persetujuan Pinjaman</h3>
                            <p class="text-xs text-gray-500">Persetujuan peminjaman buku oleh pustakawan</p>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Struk:</span>
                            <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nama Peminjam:</span>
                            <span class="font-semibold text-slate-900">{{ $receipt->user?->name ?? 'Siswa' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Buku:</span>
                            <span class="font-semibold text-slate-800">{{ $receipt->loans->count() }} buku</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Apakah Anda yakin ingin menyetujui peminjaman ini? Masa pinjam (7 hari) akan segera dimulai dan status struk diperbarui menjadi <strong>Disetujui / Aktif</strong>.
                    </p>

                    <form method="POST" action="{{ route('loan-receipts.approve', $receipt) }}" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="approveModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                            Ya, Setujui Sekarang
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        @can('reject', $receipt)
            <!-- Modal Konfirmasi Tolak Struk -->
            <div x-cloak x-show="rejectModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="rejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Tolak Pengajuan Peminjaman</h3>
                            <p class="text-xs text-gray-500">Konfirmasi penolakan pengajuan buku</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('loan-receipts.reject', $receipt) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan (Opsional)</label>
                            <input type="text" name="librarian_notes" placeholder="Contoh: Stok sedang tidak tersedia fisik..." class="w-full rounded-xl border border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white shadow-xs">
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            Apakah Anda yakin ingin menolak pengajuan ini? Siswa akan dapat mengajukan pinjaman lain setelah pengajuan ini ditolak.
                        </p>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                                Ya, Tolak Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        @can('returnBook', $receipt)
            <!-- Modal Konfirmasi Pengembalian Seluruh Buku -->
            <div x-cloak x-show="returnModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="returnModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Konfirmasi Pengembalian Buku</h3>
                            <p class="text-xs text-gray-500">Memproses pengembalian seluruh buku pada struk</p>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Struk:</span>
                            <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Peminjam:</span>
                            <span class="font-semibold text-slate-900">{{ $receipt->user?->name ?? 'Siswa' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Buku:</span>
                            <span class="font-semibold text-slate-800">{{ $receipt->loans->count() }} buku</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Apakah Anda yakin ingin memproses pengembalian seluruh buku pada struk ini? Status struk akan diperbarui menjadi <strong>Kembali</strong> dan stok buku akan dipulihkan.
                    </p>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="returnModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="button" 
                                @click="returnModal = false; document.getElementById('receipt-return-form').submit();" 
                                class="btn-press px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                            Ya, Konfirmasi Pengembalian
                        </button>
                    </div>
                </div>
            </div>
        @endcan

        @can('cancel', $receipt)
            <!-- Modal Konfirmasi Batalkan Struk oleh Siswa -->
            <div x-cloak x-show="cancelModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="cancelModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
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
                            <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Buku:</span>
                            <span class="font-semibold text-slate-800">{{ $receipt->loans->count() }} buku</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Apakah Anda yakin ingin membatalkan pengajuan ini? Status struk akan dibatalkan dan kuota peminjaman Anda akan segera aktif kembali sehingga Anda bisa memilih atau mengajukan buku lain.
                    </p>

                    <form method="POST" action="{{ route('loan-receipts.cancel', $receipt) }}" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="cancelModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                            Tutup
                        </button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                            Ya, Batalkan Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        @can('requestExtension', $receipt)
            <!-- Modal Ajukan Perpanjangan oleh Siswa -->
            <div x-cloak x-show="extendModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="extendModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
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
                            <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number ?? '#'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Batas Kembali Saat Ini:</span>
                            <span class="font-semibold text-slate-800">{{ $receipt->due_date->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jumlah Buku:</span>
                            <span class="font-semibold text-slate-800">{{ $receipt->loans->count() }} buku</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Perpanjangan berlaku untuk seluruh buku pada struk ini dan hanya dapat dilakukan maksimal 1 kali. Pengajuan akan diproses dan diverifikasi oleh pustakawan loket.
                    </p>

                    <form method="POST" action="{{ route('loan-receipts.extend', $receipt) }}" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="extendModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-[#1a4f8b] hover:bg-[#153e75] text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                            Ya, Kirim Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        @can('approveExtension', $receipt)
            <!-- Modal Setujui Perpanjangan oleh Pustakawan -->
            <div x-cloak x-show="approveExtendModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="approveExtendModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
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

                    <form method="POST" action="{{ route('loan-receipts.approve-extension', $receipt) }}" class="space-y-4 text-xs">
                        @csrf
                        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-500">No. Struk:</span>
                                <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Peminjam:</span>
                                <span class="font-semibold text-slate-800">{{ $receipt->user?->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Jatuh Tempo Lama:</span>
                                <span class="font-semibold text-slate-700">{{ $receipt->due_date->translatedFormat('d F Y') }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo Baru:</label>
                            <input type="date" 
                                   name="new_due_date" 
                                   required 
                                   min="{{ $receipt->due_date->copy()->addDay()->toDateString() }}" 
                                   value="{{ $receipt->due_date->copy()->addDays(7)->toDateString() }}" 
                                   class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white">
                            <span class="text-[10px] text-slate-500 mt-1 block">Rekomendasi otomatis: +7 hari dari batas waktu saat ini.</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Catatan Pustakawan (Opsional):</label>
                            <textarea name="notes" rows="2" placeholder="Catatan perpanjangan..." class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="approveExtendModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                                Setujui Perpanjangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        @can('rejectExtension', $receipt)
            <!-- Modal Tolak Perpanjangan oleh Pustakawan -->
            <div x-cloak x-show="rejectExtendModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                <div @click.outside="rejectExtendModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
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

                    <form method="POST" action="{{ route('loan-receipts.reject-extension', $receipt) }}" class="space-y-4 text-xs">
                        @csrf
                        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-500">No. Struk:</span>
                                <span class="font-mono font-semibold text-[#1a4f8b]">{{ $receipt->receipt_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Peminjam:</span>
                                <span class="font-semibold text-slate-800">{{ $receipt->user?->name }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Alasan Penolakan (Opsional):</label>
                            <textarea name="notes" rows="2" placeholder="Contoh: Buku telah direservasi peminjam lain..." class="w-full rounded-xl border-slate-300 text-xs px-3 py-2 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] bg-white"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="rejectExtendModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit" class="btn-press px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                                Tolak Perpanjangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</div>
@endsection
