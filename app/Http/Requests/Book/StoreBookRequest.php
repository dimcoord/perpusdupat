<?php

namespace App\Http\Requests\Book;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Book::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 1)],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'sinopsis' => ['nullable', 'string'],
            'total_stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori',
            'isbn' => 'ISBN',
            'title' => 'Judul Buku',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'publication_year' => 'Tahun Terbit',
            'rack_location' => 'Lokasi Rak',
            'sinopsis' => 'Sinopsis',
            'total_stock' => 'Jumlah Stok',
            'cover_image' => 'Sampul Buku',
        ];
    }
}
