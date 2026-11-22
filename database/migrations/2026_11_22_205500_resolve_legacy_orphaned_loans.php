<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanReceipt;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Clean up orphaned pre-receipt legacy loans so that students who have returned all
     * their visible loans are not permanently blocked by invisible records.
     */
    public function up(): void
    {
        $orphanedLoans = Loan::whereNull('loan_receipt_id')
            ->whereIn('status', ['menunggu_persetujuan', 'dipinjam', 'terlambat'])
            ->get();

        foreach ($orphanedLoans as $loan) {
            if (in_array($loan->status, ['dipinjam', 'terlambat'], true)) {
                $book = Book::find($loan->book_id);
                if ($book && $book->available_stock < $book->total_stock) {
                    $book->increment('available_stock');
                }
            }

            $loan->update([
                'status' => 'kembali',
                'return_date' => $loan->return_date ?? Carbon::today()->toDateString(),
                'librarian_notes' => $loan->librarian_notes ?? 'Selesai (Migrasi Sistem Struk)',
            ]);
        }

        // Also close any receipts that have no remaining unfinished loans
        LoanReceipt::whereIn('status', [LoanReceipt::STATUS_PENDING, LoanReceipt::STATUS_APPROVED])
            ->whereDoesntHave('loans', function ($q) {
                $q->whereIn('status', ['menunggu_persetujuan', 'dipinjam', 'terlambat']);
            })
            ->update(['status' => LoanReceipt::STATUS_RETURNED]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
