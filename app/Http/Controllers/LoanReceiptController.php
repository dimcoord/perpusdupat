<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\ReturnReceiptRequest;
use App\Models\Book;
use App\Models\LoanReceipt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoanReceiptController extends Controller
{
    /**
     * Show a printable receipt.
     */
    public function show(LoanReceipt $receipt): View
    {
        Gate::authorize('view', $receipt);

        $receipt->load(['user:id,name,nis_nip,phone', 'processor:id,name', 'loans.book:id,title,isbn,rack_location']);

        return view('loans.receipt', compact('receipt'));
    }

    /**
     * Approve every item of a pending receipt (Librarian only).
     */
    public function approve(LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('approve', $receipt);

        try {
            DB::transaction(function () use ($receipt) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if (! $receipt->isPending()) {
                    throw new \DomainException('Permintaan ini sudah diproses.');
                }

                $loans = $receipt->loans()->with('book')->get();
                $books = \App\Models\Book::whereIn('id', $loans->pluck('book_id'))->lockForUpdate()->get()->keyBy('id');

                foreach ($loans as $loan) {
                    if ($books[$loan->book_id]->available_stock <= 0) {
                        throw new \DomainException("Stok buku '{$books[$loan->book_id]->title}' sedang habis.");
                    }
                }

                $today = Carbon::today();
                $due = $today->copy()->addDays(7);

                foreach ($loans as $loan) {
                    $books[$loan->book_id]->decrement('available_stock');
                    $loan->update([
                        'status' => 'dipinjam',
                        'loan_date' => $today->toDateString(),
                        'due_date' => $due->toDateString(),
                    ]);
                }

                $receipt->update([
                    'status' => LoanReceipt::STATUS_APPROVED,
                    'processed_by' => auth()->id(),
                    'loan_date' => $today->toDateString(),
                    'due_date' => $due->toDateString(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loan-receipts.show', $receipt)
            ->with('success', 'Permintaan peminjaman disetujui. Struk siap dicetak.');
    }

    /**
     * Return ALL books of a receipt at once (Librarian only). All-or-nothing.
     */
    public function returnAll(ReturnReceiptRequest $request, LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('returnBook', $receipt);

        $returnDate = $request->input('return_date') ? Carbon::parse($request->input('return_date')) : Carbon::today();

        try {
            DB::transaction(function () use ($receipt, $request, $returnDate) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if ($receipt->status !== LoanReceipt::STATUS_APPROVED) {
                    throw new \DomainException('Struk ini tidak dalam status dipinjam atau sudah dikembalikan.');
                }

                $loans = $receipt->loans()->whereIn('status', ['dipinjam', 'terlambat'])->lockForUpdate()->get();

                if ($loans->isEmpty()) {
                    throw new \DomainException('Tidak ada buku yang perlu dikembalikan pada struk ini.');
                }

                foreach ($loans as $loan) {
                    Book::whereKey($loan->book_id)->increment('available_stock');
                    $loan->update([
                        'return_date' => $returnDate->toDateString(),
                        'status' => 'kembali',
                        'librarian_notes' => $request->input('librarian_notes'),
                    ]);
                }

                $receipt->update(['status' => LoanReceipt::STATUS_RETURNED]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loan-receipts.show', $receipt)
            ->with('success', 'Semua buku pada struk '.$receipt->receipt_number.' berhasil dikembalikan.');
    }
}