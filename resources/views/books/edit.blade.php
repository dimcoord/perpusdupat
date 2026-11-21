@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    coverPreview: '{{ $book->cover_url }}',
    onCoverChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.coverPreview = URL.createObjectURL(file);
        } else {
            this.coverPreview = '{{ $book->cover_url }}';
        }
    }
}">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Data Buku</h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui informasi bibliografi, sampul, atau stok buku: {{ $book->title }}</p>
        </div>
        <a href="{{ route('books.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs border border-slate-300 shadow-xs transition">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Title -->
                <div class="sm:col-span-2">
                    <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Judul Buku <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $book->title) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('title') border-rose-500 @enderror">
                    @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- ISBN -->
                <div>
                    <label for="isbn" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor ISBN <span class="text-rose-500">*</span></label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('isbn') border-rose-500 @enderror">
                    @error('isbn') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Klasifikasi Kategori (DDC) <span class="text-rose-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition bg-white @error('category_id') border-rose-500 @enderror">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->code }} - {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Author -->
                <div>
                    <label for="author" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Penulis / Pengarang</label>
                    <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                </div>

                <!-- Publisher -->
                <div>
                    <label for="publisher" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Penerbit</label>
                    <input type="text" name="publisher" id="publisher" value="{{ old('publisher', $book->publisher) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                </div>

                <!-- Year -->
                <div>
                    <label for="publication_year" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Terbit</label>
                    <input type="number" name="publication_year" id="publication_year" value="{{ old('publication_year', $book->publication_year) }}" min="1800" max="{{ date('Y') + 1 }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                </div>

                <!-- Page Count -->
                <div>
                    <label for="jumlah_halaman" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Halaman</label>
                    <input type="number" name="jumlah_halaman" id="jumlah_halaman" value="{{ old('jumlah_halaman', $book->jumlah_halaman) }}" min="1" max="99999" placeholder="Contoh: 350" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('jumlah_halaman') border-rose-500 @enderror">
                    @error('jumlah_halaman') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Rack Location -->
                <div>
                    <label for="rack_location" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Lokasi Rak Simpan</label>
                    <input type="text" name="rack_location" id="rack_location" value="{{ old('rack_location', $book->rack_location) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                </div>

                <!-- Total Stock -->
                <div>
                    <label for="total_stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Total Stok <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_stock" id="total_stock" value="{{ old('total_stock', $book->total_stock) }}" min="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('total_stock') border-rose-500 @enderror">
                    <p class="text-[11px] text-slate-400 mt-1">Stok tersedia saat ini: {{ $book->available_stock }} eksemplar.</p>
                    @error('total_stock') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Sinopsis -->
                <div class="sm:col-span-2">
                    <label for="sinopsis" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Sinopsis Buku</label>
                    <textarea name="sinopsis" id="sinopsis" rows="4" placeholder="Tuliskan ringkasan atau sinopsis buku..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('sinopsis') border-rose-500 @enderror">{{ old('sinopsis', $book->sinopsis) }}</textarea>
                    @error('sinopsis') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Cover Image Upload -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Sampul Buku (Cover Image)</label>
                    <div class="flex items-center gap-5 p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/50">
                        <!-- Preview Box -->
                        <div class="w-20 h-28 rounded-lg bg-slate-200 border border-slate-300 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs relative">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" alt="Preview Sampul" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!coverPreview">
                                <div class="text-center p-2 text-slate-400">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[9px] block">Tanpa Foto</span>
                                </div>
                            </template>
                        </div>

                        <!-- Upload File Input -->
                        <div class="flex-1 space-y-1">
                            <input type="file"
                                   name="cover_image"
                                   id="cover_image"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   @change="onCoverChange($event)"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400">Pilih berkas gambar baru untuk mengganti sampul saat ini. Format JPG, PNG, WEBP (maks. 2MB).</p>
                            @error('cover_image') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('books.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition active:scale-98">
                    Perbarui Data Buku
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
