@extends('layouts.app')

@section('content')
<style>
    .receipt { max-width: 640px; margin: 0 auto; padding: 24px; background: #fff; border: 1px solid #ddd; font-size: 14px; }
    .receipt h1 { font-size: 18px; margin: 0; text-align: center; }
    .receipt .sub { text-align: center; color: #666; margin-bottom: 16px; }
    .receipt table { width: 100%; border-collapse: collapse; margin: 12px 0; }
    .receipt th, .receipt td { padding: 6px 4px; border-bottom: 1px solid #eee; text-align: left; }
    .receipt .meta td { border: 0; padding: 2px 4px; }
    .receipt .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; }
    .receipt .pending { background: #fef3c7; }
    .receipt .approved { background: #d1fae5; }
    .receipt .sign { display: flex; justify-content: space-between; margin-top: 48px; text-align: center; }
    .receipt .sign div { width: 40%; border-top: 1px solid #999; padding-top: 4px; }
    @media print {
        .no-print { display: none !important; }
        .receipt { border: 0; }
    }
</style>

<div class="no-print" style="max-width:640px;margin:0 auto 12px;display:flex;flex-wrap:wrap;gap:8px;">
    <button onclick="window.print()" class="px-3 py-2 bg-blue-600 text-white rounded">Cetak Struk</button>
    <a href="{{ route('loans.index') }}" class="px-3 py-2 border rounded">Kembali</a>
    @can('approve', $receipt)
        <form method="POST" action="{{ route('loan-receipts.approve', $receipt) }}">
            @csrf
            <button class="px-3 py-2 bg-green-600 text-white rounded">Setujui Semua Buku</button>
        </form>
    @endcan
    @can('returnBook', $receipt)
        <form method="POST" action="{{ route('loan-receipts.return', $receipt) }}" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;" onsubmit="return confirm('Kembalikan semua buku pada struk ini?');">
            @csrf
            <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="border rounded p-2">
            <input type="text" name="librarian_notes" placeholder="Catatan kondisi buku" class="border rounded p-2">
            <button class="px-3 py-2 bg-amber-600 text-white rounded">Kembalikan Semua Buku</button>
        </form>
    @endcan
</div>

@if (session('success')) <div class="no-print" style="max-width:640px;margin:0 auto 12px;padding:8px;background:#d1fae5;">{{ session('success') }}</div> @endif
@if (session('error')) <div class="no-print" style="max-width:640px;margin:0 auto 12px;padding:8px;background:#fee2e2;">{{ session('error') }}</div> @endif

<div class="receipt">
    <h1>STRUK PEMINJAMAN BUKU</h1>
    <div class="sub">{{ config('app.name') }}</div>

    <table class="meta">
        <tr><td>No. Struk</td><td>: <strong>{{ $receipt->receipt_number }}</strong></td></tr>
        <tr><td>Peminjam</td><td>: {{ $receipt->user->name }} ({{ $receipt->user->nis_nip }})</td></tr>
        <tr><td>Tanggal pinjam</td><td>: {{ $receipt->loan_date->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jatuh tempo</td><td>: {{ $receipt->due_date->translatedFormat('d F Y') }}</td></tr>
        @if ($receipt->status === 'dikembalikan')
            <tr><td>Tanggal kembali</td><td>: {{ \Carbon\Carbon::parse($receipt->loans->max('return_date'))->translatedFormat('d F Y') }}</td></tr>
        @endif
        <tr><td>Status</td>
            <td>:
                @if ($receipt->isPending())
                    <span class="badge pending">Menunggu persetujuan</span>
                @elseif ($receipt->status === 'dikembalikan')
                    <span class="badge approved">Sudah dikembalikan</span>
                @else
                    <span class="badge approved">Disetujui</span>
                @endif
            </td>
        </tr>
        @if ($receipt->processor)
            <tr><td>Petugas</td><td>: {{ $receipt->processor->name }}</td></tr>
        @endif
    </table>

    <table>
        <thead>
            <tr><th>#</th><th>Judul</th><th>ISBN</th><th>Rak</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($receipt->loans as $i => $loan)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $loan->book->title }}</td>
                    <td>{{ $loan->book->isbn }}</td>
                    <td>{{ $loan->book->rack_location }}</td>
                    <td>{{ str_replace('_', ' ', $loan->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Total: <strong>{{ $receipt->loans->count() }}</strong> buku</p>

    @if ($receipt->isPending())
        <p style="color:#92400e;">Struk ini belum berlaku sebagai bukti peminjaman sampai disetujui pustakawan.</p>
    @endif

    <div class="sign">
        <div>Peminjam</div>
        <div>Pustakawan</div>
    </div>
</div>
@endsection