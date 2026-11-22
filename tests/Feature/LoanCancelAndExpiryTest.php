<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student can cancel their own pending loan receipt', function () {
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111111',
        'title' => 'Buku Uji Pembatalan',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261122-00001',
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

    expect($student->hasUnfinishedLoans())->toBeTrue();

    $response = $this->actingAs($student)->post("/loan-receipts/{$receipt->id}/cancel");

    $response->assertRedirect(route('loan-receipts.show', $receipt));
    $response->assertSessionHas('success', 'Pengajuan peminjaman berhasil dibatalkan.');

    $receipt->refresh();
    $loan->refresh();

    expect($receipt->status)->toBe(LoanReceipt::STATUS_REJECTED);
    expect($loan->status)->toBe('ditolak');
    expect($loan->librarian_notes)->toContain('Dibatalkan oleh siswa');
    expect($student->hasUnfinishedLoans())->toBeFalse();
});

test('student cannot cancel another student pending loan receipt', function () {
    $studentA = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $studentB = User::factory()->create(['nis_nip' => 'STD002', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111112',
        'title' => 'Buku Uji Otorisasi',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261122-00002',
        'user_id' => $studentA->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => LoanReceipt::STATUS_PENDING,
    ]);

    Loan::create([
        'user_id' => $studentA->id,
        'book_id' => $book->id,
        'loan_receipt_id' => $receipt->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'menunggu_persetujuan',
    ]);

    $response = $this->actingAs($studentB)->post("/loan-receipts/{$receipt->id}/cancel");
    $response->assertForbidden();

    expect($receipt->fresh()->status)->toBe(LoanReceipt::STATUS_PENDING);
});

test('auto-cancel automatically cancels pending loans older than 2 days', function () {
    $student = User::factory()->create(['nis_nip' => 'STD003', 'role' => 'murid']);
    $category = Category::create(['code' => '800', 'name' => 'Sastra']);

    $book = Book::create([
        'category_id' => $category->id,
        'isbn' => '9781111111113',
        'title' => 'Buku Uji Kedaluwarsa',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);

    // Create a pending receipt created 3 days ago (> 48 hours)
    $expiredReceipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261119-00001',
        'user_id' => $student->id,
        'loan_date' => Carbon::now()->subDays(3)->toDateString(),
        'due_date' => Carbon::now()->addDays(4)->toDateString(),
        'status' => LoanReceipt::STATUS_PENDING,
    ]);
    $expiredReceipt->timestamps = false;
    $expiredReceipt->created_at = Carbon::now()->subDays(3);
    $expiredReceipt->save();

    $expiredLoan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $book->id,
        'loan_receipt_id' => $expiredReceipt->id,
        'loan_date' => Carbon::now()->subDays(3)->toDateString(),
        'due_date' => Carbon::now()->addDays(4)->toDateString(),
        'status' => 'menunggu_persetujuan',
    ]);
    $expiredLoan->timestamps = false;
    $expiredLoan->created_at = Carbon::now()->subDays(3);
    $expiredLoan->save();

    // Run artisan command
    $this->artisan('loans:cancel-expired')
        ->assertSuccessful();

    $expiredReceipt->refresh();
    $expiredLoan->refresh();

    expect($expiredReceipt->status)->toBe(LoanReceipt::STATUS_REJECTED);
    expect($expiredLoan->status)->toBe('ditolak');
    expect($expiredLoan->librarian_notes)->toContain('Otomatis dibatalkan: batas waktu pengambilan loket (2 hari) telah berakhir.');
    expect($student->hasUnfinishedLoans())->toBeFalse();
});
