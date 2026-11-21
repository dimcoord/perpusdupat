<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('librarian can reject a pending loan receipt with notes', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111111',
        'title' => 'Buku Uji Penolakan',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00001',
        'user_id' => $student->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_PENDING,
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $book->id,
        'loan_receipt_id' => $receipt->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'menunggu_persetujuan',
    ]);

    $response = $this->actingAs($librarian)->post("/loan-receipts/{$receipt->id}/reject", [
        'librarian_notes' => 'Buku sedang dalam perbaikan fisik.',
    ]);

    $response->assertRedirect(route('loan-receipts.show', $receipt));
    $response->assertSessionHas('success', 'Pengajuan peminjaman berhasil ditolak.');

    $receipt->refresh();
    $loan->refresh();
    $book->refresh();

    expect($receipt->status)->toBe(LoanReceipt::STATUS_REJECTED);
    expect($receipt->processed_by)->toBe($librarian->id);
    expect($loan->status)->toBe('ditolak');
    expect($loan->librarian_notes)->toBe('Buku sedang dalam perbaikan fisik.');
    // Stock remains untouched
    expect($book->available_stock)->toBe(5);
});

test('student cannot reject a loan receipt', function () {
    $student = User::factory()->create(['nis_nip' => 'STD002', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00002',
        'user_id' => $student->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_PENDING,
    ]);

    $response = $this->actingAs($student)->post("/loan-receipts/{$receipt->id}/reject");
    $response->assertForbidden();
});

test('cannot reject an already approved loan receipt', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB002', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD003', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00003',
        'user_id' => $student->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($librarian)->post("/loan-receipts/{$receipt->id}/reject");
    $response->assertForbidden();
});

test('librarian can reject a legacy loan without receipt', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB003', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD004', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111112',
        'title' => 'Buku Legacy',
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

    $response = $this->actingAs($librarian)->post("/loans/{$loan->id}/reject", [
        'librarian_notes' => 'Alasan tolak legacy.',
    ]);

    $response->assertRedirect();
    $loan->refresh();
    expect($loan->status)->toBe('ditolak');
    expect($loan->librarian_notes)->toBe('Alasan tolak legacy.');
});

test('receipt view displays reject button for librarian and shows rejected status', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB004', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD005', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261121-00004',
        'user_id' => $student->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_PENDING,
    ]);

    $response = $this->actingAs($librarian)->get(route('loan-receipts.show', $receipt));
    $response->assertOk();
    $response->assertSee('Tolak Pengajuan');

    // Reject it
    $receipt->update(['status' => LoanReceipt::STATUS_REJECTED]);

    $responseAfter = $this->actingAs($librarian)->get(route('loan-receipts.show', $receipt));
    $responseAfter->assertOk();
    $responseAfter->assertSee('Ditolak');
});
