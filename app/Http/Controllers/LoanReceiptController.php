<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\ReturnReceiptRequest;
use App\Models\Book;
use App\Models\LoanReceipt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        LoanReceipt::cancelExpiredPendingReceipts();

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
                $books = Book::whereIn('id', $loans->pluck('book_id'))->lockForUpdate()->get()->keyBy('id');

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
     * Reject a pending receipt (Librarian only).
     */
    public function reject(Request $request, LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('reject', $receipt);

        $notes = $request->input('librarian_notes');

        try {
            DB::transaction(function () use ($receipt, $notes) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if (! $receipt->isPending()) {
                    throw new \DomainException('Permintaan ini sudah diproses dan tidak dapat ditolak.');
                }

                $receipt->loans()->update([
                    'status' => 'ditolak',
                    'librarian_notes' => $notes,
                ]);

                $receipt->update([
                    'status' => LoanReceipt::STATUS_REJECTED,
                    'processed_by' => auth()->id(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loan-receipts.show', $receipt)
            ->with('success', 'Pengajuan peminjaman berhasil ditolak.');
    }

    /**
     * Cancel a pending receipt (Student borrower or Librarian).
     */
    public function cancel(LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('cancel', $receipt);

        try {
            DB::transaction(function () use ($receipt) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if (! $receipt->isPending()) {
                    throw new \DomainException('Pengajuan ini sudah diproses dan tidak dapat dibatalkan.');
                }

                $notes = auth()->user()->isPustakawan() ? 'Dibatalkan oleh pustakawan.' : 'Dibatalkan oleh siswa peminjam.';

                $receipt->loans()->update([
                    'status' => 'ditolak',
                    'librarian_notes' => $notes,
                ]);

                $receipt->update([
                    'status' => LoanReceipt::STATUS_REJECTED,
                    'processed_by' => auth()->id(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loan-receipts.show', $receipt)
            ->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
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
                    $receipt->update(['status' => LoanReceipt::STATUS_RETURNED]);

                    return;
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

    /**
     * Student requests extension for this receipt.
     */
    public function requestExtension(Request $request, LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('requestExtension', $receipt);

        if (! $receipt->canBeExtended()) {
            return back()->with('error', 'Peminjaman ini tidak memenuhi syarat untuk diperpanjang.');
        }

        $receipt->update([
            'extension_status' => LoanReceipt::EXTENSION_PENDING,
            'extension_requested_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan perpanjangan peminjaman berhasil dikirim. Menunggu persetujuan pustakawan.');
    }

    /**
     * Librarian approves extension with flexible new due date.
     */
    public function approveExtension(Request $request, LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('approveExtension', $receipt);

        $request->validate([
            'new_due_date' => ['required', 'date', 'after:'.$receipt->due_date->toDateString()],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newDueDate = Carbon::parse($request->input('new_due_date'));

        try {
            DB::transaction(function () use ($receipt, $newDueDate, $request) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if (! $receipt->isExtensionPending()) {
                    throw new \DomainException('Pengajuan perpanjangan tidak ditemukan atau sudah diproses.');
                }

                $receipt->update([
                    'due_date' => $newDueDate->toDateString(),
                    'extension_status' => LoanReceipt::EXTENSION_APPROVED,
                    'extension_count' => $receipt->extension_count + 1,
                    'extension_notes' => $request->input('notes'),
                ]);

                $receipt->loans()->whereIn('status', ['dipinjam', 'terlambat'])->update([
                    'due_date' => $newDueDate->toDateString(),
                    'status' => 'dipinjam',
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Perpanjangan peminjaman berhasil disetujui hingga '.$newDueDate->translatedFormat('d F Y').'.');
    }

    /**
     * Librarian rejects extension request.
     */
    public function rejectExtension(Request $request, LoanReceipt $receipt): RedirectResponse
    {
        Gate::authorize('rejectExtension', $receipt);

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($receipt, $request) {
                $receipt = LoanReceipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();

                if (! $receipt->isExtensionPending()) {
                    throw new \DomainException('Pengajuan perpanjangan tidak ditemukan atau sudah diproses.');
                }

                $receipt->update([
                    'extension_status' => LoanReceipt::EXTENSION_REJECTED,
                    'extension_notes' => $request->input('notes'),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan perpanjangan peminjaman telah ditolak.');
    }
}
