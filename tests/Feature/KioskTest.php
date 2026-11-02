<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kiosk screen displays available book catalog and categories', function () {
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $availableBook = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567890',
        'title' => 'Buku Tersedia',
        'author' => 'Penulis A',
        'rack_location' => 'Rak-A1',
        'total_stock' => 5,
        'available_stock' => 3,
    ]);

    $outOfStockBook = Book::create([
        'category_id' => $category->id,
        'isbn' => '9780987654321',
        'title' => 'Buku Habis',
        'author' => 'Penulis B',
        'rack_location' => 'Rak-A2',
        'total_stock' => 2,
        'available_stock' => 0,
    ]);

    $response = $this->get('/kiosk');

    $response->assertOk();
    $response->assertViewIs('kiosk.index');
    $response->assertViewHas('availableBooks', function ($books) use ($availableBook, $outOfStockBook) {
        $ids = collect($books)->pluck('id');

        return $ids->contains($availableBook->id) && ! $ids->contains($outOfStockBook->id);
    });
    $response->assertViewHas('categories');
});

test('student can self check-in successfully via kiosk endpoint', function () {
    $student = User::factory()->create(['nis_nip' => '2024999', 'name' => 'Siswa Test', 'role' => 'murid']);

    $response = $this->postJson('/kiosk/check-in', [
        'nis' => '2024999',
        'purpose' => 'Membaca',
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'name' => 'Siswa Test',
            'nis' => '2024999',
            'purpose' => 'Membaca',
        ],
    ]);

    $this->assertDatabaseHas('visits', [
        'user_id' => $student->id,
        'purpose' => 'Membaca',
    ]);

    expect(Visit::where('user_id', $student->id)->whereDate('visit_date', Carbon::today())->exists())->toBeTrue();
});

test('check-in fails when nis is not found', function () {
    $response = $this->postJson('/kiosk/check-in', [
        'nis' => 'NONEXISTENT_NIS',
        'purpose' => 'Membaca',
    ]);

    $response->assertStatus(404);
    $response->assertJson([
        'success' => false,
    ]);
});
