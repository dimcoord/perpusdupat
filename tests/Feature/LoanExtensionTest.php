<?php

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanReceipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create(['code' => '800', 'name' => 'Sastra']);
    $this->book = Book::create([
        'category_id' => $this->category->id,
        'isbn' => '9781234567890',
        'title' => 'Laskar Pelangi',
        'total_stock' => 5,
        'available_stock' => 5,
    ]);
});

test('student can request extension for their active receipt before due date', function () {
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00001',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $this->book->id,
        'loan_receipt_id' => $receipt->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($student)
        ->post(route('loan-receipts.extend', $receipt));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $receipt->refresh();
    expect($receipt->extension_status)->toBe(LoanReceipt::EXTENSION_PENDING);
    expect($receipt->isExtensionPending())->toBeTrue();
    expect($receipt->extension_requested_at)->not->toBeNull();
});

test('student cannot request extension for another student receipt', function () {
    $student1 = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);
    $student2 = User::factory()->create(['nis_nip' => 'STD002', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00002',
        'user_id' => $student1->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
    ]);

    $response = $this->actingAs($student2)
        ->post(route('loan-receipts.extend', $receipt));

    $response->assertStatus(403);
});

test('student cannot request extension if loan is overdue', function () {
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00003',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->subDays(10)->toDateString(),
        'due_date' => Carbon::today()->subDays(3)->toDateString(),
    ]);

    $response = $this->actingAs($student)
        ->post(route('loan-receipts.extend', $receipt));

    $response->assertStatus(403);
    $receipt->refresh();
    expect($receipt->extension_status)->toBeNull();
});

test('student cannot request extension more than once', function () {
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00004',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'extension_count' => 1,
        'extension_status' => LoanReceipt::EXTENSION_APPROVED,
    ]);

    $response = $this->actingAs($student)
        ->post(route('loan-receipts.extend', $receipt));

    $response->assertStatus(403);
});

test('librarian can approve extension with flexible new due date', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00005',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'extension_status' => LoanReceipt::EXTENSION_PENDING,
        'extension_requested_at' => now(),
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $this->book->id,
        'loan_receipt_id' => $receipt->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $newDueDate = Carbon::today()->addDays(14)->toDateString();

    $response = $this->actingAs($librarian)
        ->post(route('loan-receipts.approve-extension', $receipt), [
            'new_due_date' => $newDueDate,
            'notes' => 'Diperpanjang 1 minggu tambahan',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $receipt->refresh();
    $loan->refresh();

    expect($receipt->due_date->toDateString())->toBe($newDueDate);
    expect($receipt->extension_status)->toBe(LoanReceipt::EXTENSION_APPROVED);
    expect($receipt->extension_count)->toBe(1);
    expect($receipt->extension_notes)->toBe('Diperpanjang 1 minggu tambahan');

    expect($loan->due_date->toDateString())->toBe($newDueDate);
    expect($loan->status)->toBe('dipinjam');
});

test('librarian can reject extension request', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $oldDueDate = Carbon::today()->addDays(7)->toDateString();

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00006',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => $oldDueDate,
        'extension_status' => LoanReceipt::EXTENSION_PENDING,
        'extension_requested_at' => now(),
    ]);

    $response = $this->actingAs($librarian)
        ->post(route('loan-receipts.reject-extension', $receipt), [
            'notes' => 'Buku dibutuhkan untuk kegiatan sekolah',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $receipt->refresh();
    expect($receipt->due_date->toDateString())->toBe($oldDueDate);
    expect($receipt->extension_status)->toBe(LoanReceipt::EXTENSION_REJECTED);
    expect($receipt->extension_notes)->toBe('Buku dibutuhkan untuk kegiatan sekolah');
    expect($receipt->canBeExtended())->toBeFalse();
});

test('librarian dashboard displays extension badge and link for pending extension', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create(['nis_nip' => 'STD001', 'role' => 'murid']);

    $receipt = LoanReceipt::create([
        'receipt_number' => 'LN-20261123-00007',
        'user_id' => $student->id,
        'status' => LoanReceipt::STATUS_APPROVED,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'extension_status' => LoanReceipt::EXTENSION_PENDING,
        'extension_requested_at' => now(),
    ]);

    $loan = Loan::create([
        'user_id' => $student->id,
        'book_id' => $this->book->id,
        'loan_receipt_id' => $receipt->id,
        'loan_date' => Carbon::today()->toDateString(),
        'due_date' => Carbon::today()->addDays(7)->toDateString(),
        'status' => 'dipinjam',
    ]);

    $response = $this->actingAs($librarian)->get(route('librarian.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Perpanjangan');
    $response->assertSee(route('loan-receipts.show', $receipt));
    $response->assertSee('1 perpanjangan');
});
