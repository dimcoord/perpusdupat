<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('librarian can create loan without fine amount', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567890',
        'title' => 'Laskar Pelangi',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    $response = $this->actingAs($librarian)->post('/loans', [
        'nis_nip' => $student->nis_nip,
        'book_id' => $book->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
    ]);

    $response->assertRedirect('/loans');

    $this->assertDatabaseHas('loans', [
        'user_id' => $student->id,
        'book_id' => $book->id,
        'status' => 'dipinjam',
    ]);

    expect($book->fresh()->available_stock)->toBe(4);
});

test('returning overdue loan processes successfully without fine calculation', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567890',
        'title' => 'Bumi Manusia',
        'total_stock' => 5,
        'available_stock' => 4,
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $book->id,
        'loan_date' => Carbon::today()->subDays(15)->toDateString(),
        'due_date' => Carbon::today()->subDays(8)->toDateString(),
        'status' => 'terlambat',
    ]);

    $response = $this->actingAs($librarian)->post("/loans/{$loan->id}/return", [
        'return_date' => Carbon::today()->toDateString(),
        'librarian_notes' => 'Dikembalikan dalam kondisi baik',
    ]);

    $response->assertRedirect('/loans');

    $loan->refresh();
    expect($loan->status)->toBe('kembali');
    expect($loan->return_date->toDateString())->toBe(Carbon::today()->toDateString());
    expect($loan->librarian_notes)->toBe('Dikembalikan dalam kondisi baik');
    expect($book->fresh()->available_stock)->toBe(5);
});

test('dashboards display overdue counts without fines', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781234567890',
        'title' => 'Buku Uji',
        'total_stock' => 2,
        'available_stock' => 1,
    ]);

    Loan::create([
        'user_id' => $student->id,
        'book_id' => $book->id,
        'loan_date' => Carbon::today()->subDays(10)->toDateString(),
        'due_date' => Carbon::today()->subDays(3)->toDateString(),
        'status' => 'dipinjam',
    ]);

    // Librarian dashboard
    $libResponse = $this->actingAs($librarian)->get('/librarian/dashboard');
    $libResponse->assertOk();
    $libResponse->assertViewHas('stats', function ($stats) {
        return ! isset($stats['total_fines']) && $stats['overdue_loans'] === 1;
    });

    // Student dashboard
    $stdResponse = $this->actingAs($student)->get('/student/dashboard');
    $stdResponse->assertOk();
    $stdResponse->assertViewHas('stats', function ($stats) {
        return ! isset($stats['unpaid_fines']) && $stats['overdue_loans'] === 1;
    });
});
