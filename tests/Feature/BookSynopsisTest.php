<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('librarian can create book with sinopsis', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $response = $this->actingAs($librarian)->post('/books', [
        'category_id' => $category->id,
        'isbn' => '9781234567800',
        'title' => 'Buku Dengan Sinopsis',
        'author' => 'Penulis Hebat',
        'sinopsis' => 'Ini adalah sinopsis lengkap tentang petualangan luar biasa.',
        'total_stock' => 5,
    ]);

    $response->assertRedirect('/books');

    $book = Book::where('isbn', '9781234567800')->first();
    expect($book)->not->toBeNull();
    expect($book->sinopsis)->toBe('Ini adalah sinopsis lengkap tentang petualangan luar biasa.');
});

test('librarian can update book sinopsis', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567801',
        'title' => 'Buku Uji Sinopsis',
        'author' => 'Penulis',
        'sinopsis' => 'Sinopsis lama.',
        'total_stock' => 3,
        'available_stock' => 3,
    ]);

    $response = $this->actingAs($librarian)->put("/books/{$book->id}", [
        'category_id' => $category->id,
        'isbn' => '9781234567801',
        'title' => 'Buku Uji Sinopsis',
        'sinopsis' => 'Sinopsis baru yang telah diperbarui.',
        'total_stock' => 3,
    ]);

    $response->assertRedirect('/books');

    $book->refresh();
    expect($book->sinopsis)->toBe('Sinopsis baru yang telah diperbarui.');
});

test('books index displays sinopsis', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB999', 'role' => 'pustakawan']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567802',
        'title' => 'Buku Berita Sinopsis',
        'author' => 'Penulis',
        'sinopsis' => 'Sinopsis unik yang muncul di katalog.',
        'total_stock' => 2,
        'available_stock' => 2,
    ]);

    $response = $this->actingAs($librarian)->get('/books');
    $response->assertOk();
    $response->assertSee('Sinopsis unik yang muncul di katalog.');
});

test('search scope matches terms in sinopsis', function () {
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567803',
        'title' => 'Judul Biasa',
        'author' => 'Penulis Biasa',
        'sinopsis' => 'Petualangan astronomi galaksi andromeda.',
        'total_stock' => 1,
        'available_stock' => 1,
    ]);

    $results = Book::search('andromeda')->get();
    expect($results->pluck('id'))->toContain($book->id);
});

test('mobile scanner api supports sinopsis field', function () {
    $scannerUser = User::factory()->create(['nis_nip' => 'SCANNER-DEVICE-01', 'role' => 'pustakawan']);
    Sanctum::actingAs($scannerUser);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $response = $this->postJson('/api/v1/books/isbn-scan', [
        'isbn' => '9781234567804',
        'title' => 'Buku Scanner Sinopsis',
        'category_id' => $category->id,
        'stock' => 2,
        'sinopsis' => 'Sinopsis dari scanner hardware.',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'sinopsis' => 'Sinopsis dari scanner hardware.',
            ],
        ]);

    $book = Book::where('isbn', '9781234567804')->first();
    expect($book->sinopsis)->toBe('Sinopsis dari scanner hardware.');
});
