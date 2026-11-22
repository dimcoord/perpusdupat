@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    coverPreview: null,
    onCoverChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.coverPreview = URL.createObjectURL(file);
        } else {
            this.coverPreview = null;
        }
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 scroll-reveal">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Buku Baru</h1>
            <p class="text-xs text-gray-500 mt-1">Daftarkan koleksi buku baru ke dalam katalog perpustakaan.</p>
        </div>
        <a href="{{ route('books.index') }}" class="btn-press px-3.5 py-2 rounded-lg bg-white hover:bg-[#e2e8f0]/40 text-gray-700 font-medium text-xs border border-[#e2e8f0] shadow-2xs transition active:scale-[0.98] self-start sm:self-auto">
            &larr; Kembali ke Katalog
        </a>
    </div>

    <div class="scroll-reveal delay-1 bg-white rounded-2xl border border-[#e2e8f0] p-6 shadow-xs card-hover">
        <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Title -->
                <div class="sm:col-span-2">
                    <label for="title" class="block text-xs font-medium text-gray-700 mb-1.5">Judul Buku <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Masukkan judul lengkap buku..." class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white @error('title') border-rose-400 @enderror">
                    @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- ISBN -->
                <div>
                    <label for="isbn" class="block text-xs font-medium text-gray-700 mb-1.5">Nomor ISBN <span class="text-rose-500">*</span></label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}" required placeholder="Contoh: 9786020332956" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs font-mono focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white @error('isbn') border-rose-400 @enderror">
                    @error('isbn') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-medium text-gray-700 mb-1.5">Klasifikasi Kategori (DDC) <span class="text-rose-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white @error('category_id') border-rose-400 @enderror">
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->code }} - {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Author -->
                <div>
                    <label for="author" class="block text-xs font-medium text-gray-700 mb-1.5">Nama Penulis / Pengarang</label>
                    <input type="text" name="author" id="author" value="{{ old('author') }}" placeholder="Contoh: Pramoedya Ananta Toer" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>

                <!-- Publisher -->
                <div>
                    <label for="publisher" class="block text-xs font-medium text-gray-700 mb-1.5">Penerbit</label>
                    <input type="text" name="publisher" id="publisher" value="{{ old('publisher') }}" placeholder="Contoh: Balai Pustaka / Gramedia" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>

                <!-- Year -->
                <div>
                    <label for="publication_year" class="block text-xs font-medium text-gray-700 mb-1.5">Tahun Terbit</label>
                    <input type="number" name="publication_year" id="publication_year" value="{{ old('publication_year', date('Y')) }}" min="1800" max="{{ date('Y') + 1 }}" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>

                <!-- Page Count -->
                <div>
                    <label for="jumlah_halaman" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Halaman</label>
                    <input type="number" name="jumlah_halaman" id="jumlah_halaman" value="{{ old('jumlah_halaman') }}" min="1" max="99999" placeholder="Contoh: 350" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('jumlah_halaman') border-rose-500 @enderror">
                    @error('jumlah_halaman') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Rack Location -->
                <div>
                    <label for="rack_location" class="block text-xs font-medium text-gray-700 mb-1.5">Lokasi Rak Simpan</label>
                    <input type="text" name="rack_location" id="rack_location" value="{{ old('rack_location') }}" placeholder="Contoh: Rak-A2, Lemari-01" class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs font-mono focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white">
                </div>

                <!-- Total Stock -->
                <div>
                    <label for="total_stock" class="block text-xs font-medium text-gray-700 mb-1.5">Jumlah Stok Eksemplar <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_stock" id="total_stock" value="{{ old('total_stock', 1) }}" min="0" required class="w-full px-3 py-2 rounded-lg border border-[#e2e8f0] text-xs font-semibold focus:ring-1 focus:ring-[#1a4f8b] focus:border-[#1a4f8b] outline-none transition bg-white @error('total_stock') border-rose-400 @enderror">
                    @error('total_stock') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Sinopsis -->
                <div class="sm:col-span-2">
                    <label for="sinopsis" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Sinopsis Buku</label>
                    <textarea name="sinopsis" id="sinopsis" rows="4" placeholder="Tuliskan ringkasan atau sinopsis buku..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition @error('sinopsis') border-rose-500 @enderror">{{ old('sinopsis') }}</textarea>
                    @error('sinopsis') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Cover Image Upload -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Sampul Buku (Cover Image)</label>
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-dashed border-[#e2e8f0] bg-[#e2e8f0]/20">
                        <!-- Preview Box -->
                        <div class="w-16 h-24 rounded-lg bg-[#e2e8f0]/60 border border-[#e2e8f0] flex items-center justify-center shrink-0 overflow-hidden relative shadow-2xs">
                            <template x-if="coverPreview">
                                <img :src="coverPreview" alt="Preview Sampul" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!coverPreview">
                                <div class="text-center p-2 text-[#1a4f8b]">
                                    <svg class="w-6 h-6 mx-auto mb-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[9px] block text-gray-500">Tanpa Foto</span>
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
                                   class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-[#1a4f8b] file:text-white hover:file:bg-[#153e75] cursor-pointer">
                            <p class="text-[11px] text-gray-400">Format yang didukung: JPG, PNG, WEBP. Maksimal ukuran 2MB.</p>
                            @error('cover_image') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-[#e2e8f0]">
                <a href="{{ route('books.index') }}" class="px-3.5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white border border-[#e2e8f0] hover:bg-[#e2e8f0]/40 transition active:scale-[0.98]">Batal</a>
                <button type="submit" class="btn-press px-4 py-2 rounded-lg bg-[#1a4f8b] hover:bg-[#153e75] text-white font-medium text-xs shadow-2xs transition active:scale-[0.98]">
                    Simpan Buku Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
