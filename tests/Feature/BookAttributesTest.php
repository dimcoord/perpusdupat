<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('librarian can create book with jumlah_halaman and publication_year', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $response = $this->actingAs($librarian)->post('/books', [
        'category_id' => $category->id,
        'isbn' => '9781234567810',
        'title' => 'Buku Halaman dan Tahun',
        'author' => 'Penulis',
        'publication_year' => 2024,
        'jumlah_halaman' => 450,
        'total_stock' => 5,
    ]);

    $response->assertRedirect('/books');

    $book = Book::where('isbn', '9781234567810')->first();
    expect($book)->not->toBeNull();
    expect($book->publication_year)->toBe(2024);
    expect($book->tahun_terbit)->toBe(2024);
    expect($book->jumlah_halaman)->toBe(450);
});

test('librarian can update book jumlah_halaman', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567811',
        'title' => 'Buku Uji Halaman',
        'author' => 'Penulis',
        'publication_year' => 2023,
        'jumlah_halaman' => 200,
        'total_stock' => 3,
        'available_stock' => 3,
    ]);

    $response = $this->actingAs($librarian)->put("/books/{$book->id}", [
        'category_id' => $category->id,
        'isbn' => '9781234567811',
        'title' => 'Buku Uji Halaman',
        'publication_year' => 2024,
        'jumlah_halaman' => 320,
        'total_stock' => 3,
    ]);

    $response->assertRedirect('/books');

    $book->refresh();
    expect($book->jumlah_halaman)->toBe(320);
    expect($book->publication_year)->toBe(2024);
});

test('books index displays jumlah_halaman', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567812',
        'title' => 'Buku Index Halaman',
        'author' => 'Penulis',
        'publication_year' => 2022,
        'jumlah_halaman' => 512,
        'total_stock' => 2,
        'available_stock' => 2,
    ]);

    $response = $this->actingAs($librarian)->get('/books');
    $response->assertOk();
    $response->assertSee('512 hlm');
});
