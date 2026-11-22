<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('students cannot create, update, or delete books', function () {
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111111',
        'title' => 'Buku Siswa',
        'total_stock' => 2,
        'available_stock' => 2,
    ]);

    $this->actingAs($student)
        ->get('/books/create')
        ->assertStatus(403);

    $this->actingAs($student)
        ->post('/books', [
            'category_id' => $category->id,
            'isbn' => '9782222222222',
            'title' => 'Hack Book',
            'total_stock' => 1,
        ])
        ->assertStatus(403);

    $this->actingAs($student)
        ->delete("/books/{$book->id}")
        ->assertStatus(403);
});

test('students can only see their own loans', function () {
    $student1 = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $student2 = User::factory()->create(['nis_nip' => 'STD002', 'role' => 'murid']);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111111',
        'title' => 'Buku Siswa',
        'total_stock' => 2,
        'available_stock' => 2,
    ]);

    $receipt1 = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00001',
        'user_id' => $student1->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_APPROVED,
    ]);

    $loan1 = Loan::create([
        'user_id' => $student1->id,
        'book_id' => $book->id,
        'loan_receipt_id' => $receipt1->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $receipt2 = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00002',
        'user_id' => $student2->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_APPROVED,
    ]);

    $loan2 = Loan::create([
        'user_id' => $student2->id,
        'book_id' => $book->id,
        'loan_receipt_id' => $receipt2->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($student1)->getJson('/loans');
    $response->assertStatus(200);

    $loanIds = collect($response->json('loans'))->pluck('id')->all();
    expect($loanIds)->toContain($loan1->id);
    expect($loanIds)->not->toContain($loan2->id);
});

test('librarian can approve loan and return book', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD003', 'role' => 'murid']);

    $category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9783333333333',
        'title' => 'Buku Peminjaman',
        'total_stock' => 3,
        'available_stock' => 3,
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $book->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'menunggu_persetujuan',
    ]);

    // Approve loan
    $this->actingAs($librarian)
        ->post("/loans/{$loan->id}/approve")
        ->assertRedirect();

    $loan->refresh();
    $book->refresh();

    expect($loan->status)->toBe('dipinjam');
    expect($book->available_stock)->toBe(2);

    // Return book
    $this->actingAs($librarian)
        ->post("/loans/{$loan->id}/return", [
            'return_date' => Carbon::today()->toDateString(),
        ])
        ->assertRedirect();

    $loan->refresh();
    $book->refresh();

    expect($loan->status)->toBe('kembali');
    expect($book->available_stock)->toBe(3);
});
