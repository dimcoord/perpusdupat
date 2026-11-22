@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Keranjang Peminjaman</h1>

    @if (session('success')) <div class="mb-3 p-3 bg-green-100 rounded">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-3 p-3 bg-red-100 rounded">{{ session('error') }}</div> @endif

    @if ($books->isEmpty())
        <p class="text-gray-500">Keranjang masih kosong. Tambahkan buku dari katalog.</p>
    @else
        @if (auth()->user()->isMurid() && auth()->user()->hasUnfinishedLoans())
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs">
                <strong>Perhatian:</strong> Anda belum dapat mengajukan peminjaman baru karena masih memiliki peminjaman sebelumnya yang belum selesai (menunggu persetujuan/penolakan petugas, atau buku belum dikembalikan).
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-2">{{ $books->count() }} dari batas maksimal {{ $max }} judul buku dalam sekali pengajuan</p>

        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2">Judul</th><th>ISBN</th><th>Rak</th><th>Stok</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($books as $book)
                    <tr class="border-b">
                        <td class="py-2">{{ $book->title }}</td>
                        <td>{{ $book->isbn }}</td>
                        <td>{{ $book->rack_location }}</td>
                        <td>{{ $book->available_stock }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove', $book) }}">
                                @csrf @method('DELETE')
                                <button class="text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route('loans.store') }}" class="space-y-3">
            @csrf

            @if (auth()->user()->isPustakawan())
                <div>
                    <label class="block text-sm">NIS/NIP peminjam</label>
                    <input name="nis_nip" value="{{ old('nis_nip') }}" class="border rounded p-2 w-full" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm">Tanggal pinjam</label>
                        <input type="date" name="loan_date" value="{{ old('loan_date', today()->toDateString()) }}" class="border rounded p-2 w-full">
                    </div>
                    <div>
                        <label class="block text-sm">Jatuh tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date', today()->addDays(7)->toDateString()) }}" class="border rounded p-2 w-full">
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 bg-red-100 rounded text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="flex gap-2">
                <button type="submit" @if(auth()->user()->isMurid() && auth()->user()->hasUnfinishedLoans()) disabled title="Anda masih memiliki peminjaman yang belum selesai" @endif class="px-4 py-2 bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded">
                    {{ auth()->user()->isPustakawan() ? 'Catat Peminjaman' : 'Ajukan Peminjaman' }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('cart.clear') }}" class="mt-2">
            @csrf @method('DELETE')
            <button class="text-sm text-gray-500 underline">Kosongkan keranjang</button>
        </form>
    @endif
</div>
@endsection
