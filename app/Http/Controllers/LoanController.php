<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\CheckoutLoanRequest;
use App\Http\Requests\Loan\ReturnLoanRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanReceipt;
use App\Models\User;
use App\Services\ExcelReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LoanController extends Controller
{
    /**
     * Display a listing of loan receipts (one row per checkout).
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Loan::class);

        $user = $request->user();
        $status = $request->query('status');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = LoanReceipt::with([
            'user:id,name,nis_nip,phone,role',
            'loans.book:id,title,isbn,rack_location',
        ]);

        // Student is strictly scoped to own records
        if ($user->isMurid()) {
            $query->where('user_id', $user->id);
        } elseif ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('nis_nip', 'like', "%{$search}%");
                    })
                    ->orWhereHas('loans.book', function ($bq) use ($search) {
                        $bq->where('title', 'like', "%{$search}%")
                            ->orWhere('isbn', 'like', "%{$search}%");
                    });
            });
        }

        match ($status) {
            'menunggu_persetujuan' => $query->where('status', LoanReceipt::STATUS_PENDING),
            'dipinjam' => $query->where('status', LoanReceipt::STATUS_APPROVED),
            'terlambat' => $query->where('status', LoanReceipt::STATUS_APPROVED)->whereDate('due_date', '<', Carbon::today()),
            'kembali' => $query->where('status', LoanReceipt::STATUS_RETURNED),
            default => null,
        };

        if ($startDate) {
            $query->whereDate('loan_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('loan_date', '<=', $endDate);
        }

        $receipts = $query->latest('id')->paginate(20)->withQueryString();

        if ($request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'receipts' => $receipts->items(),
                'total' => $receipts->total(),
            ]);
        }

        $availableBooks = $user->isPustakawan()
            ? Book::available()->orderBy('title')->get(['id', 'title', 'isbn', 'available_stock'])
            : collect();

        return view('loans.index', compact('receipts', 'status', 'search', 'startDate', 'endDate', 'availableBooks'));
    }

    /**
     * Checkout the cart: create one receipt with one loan row per book.
     */
    public function store(CheckoutLoanRequest $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $currentUser = $request->user();
        // Books come from the session cart, or from an explicit list (librarian modal)
        $fromCart = ! $request->filled('book_ids');
        $bookIds = $fromCart
            ? CartController::ids($request)
            : array_values(array_unique(array_map('intval', (array) $request->input('book_ids'))));

        if (empty($bookIds)) {
            return $this->fail($request, 'Keranjang peminjaman masih kosong.');
        }

        // Determine target borrower
        if ($currentUser->isPustakawan()) {
            if ($request->filled('nis_nip')) {
                $borrower = User::where('nis_nip', trim($request->input('nis_nip')))->first();
                if (! $borrower) {
                    return $this->fail($request, 'Siswa dengan NIS tersebut tidak ditemukan.');
                }
            } elseif ($request->filled('user_id')) {
                $borrower = User::findOrFail($request->input('user_id'));
            } else {
                return $this->fail($request, 'Peminjam harus ditentukan.');
            }
        } else {
            $borrower = $currentUser;
        }

        // Check if borrower has overdue books
        $hasOverdue = Loan::where('user_id', $borrower->id)
            ->where('status', 'dipinjam')
            ->whereDate('due_date', '<', Carbon::today())
            ->exists();

        if ($hasOverdue) {
            return $this->fail($request, 'Peminjaman tidak dapat diproses karena peminjam memiliki buku yang melewati batas jatuh tempo.');
        }

        // Borrower must not already hold / have requested the same title
        $duplicate = Loan::where('user_id', $borrower->id)
            ->whereIn('book_id', $bookIds)
            ->whereIn('status', ['dipinjam', 'menunggu_persetujuan'])
            ->with('book:id,title')
            ->first();

        if ($duplicate) {
            return $this->fail($request, "Peminjam masih memiliki pinjaman/permintaan aktif untuk buku '{$duplicate->book->title}'.");
        }

        $loanDate = $request->input('loan_date') ? Carbon::parse($request->input('loan_date')) : Carbon::today();
        $dueDate = $request->input('due_date') ? Carbon::parse($request->input('due_date')) : $loanDate->copy()->addDays(7);

        $isPustakawan = $currentUser->isPustakawan();
        $loanStatus = $isPustakawan ? 'dipinjam' : 'menunggu_persetujuan';

        try {
            $receipt = DB::transaction(function () use ($bookIds, $borrower, $currentUser, $loanDate, $dueDate, $loanStatus, $isPustakawan) {
                // Lock rows so concurrent checkouts cannot oversell stock
                $books = Book::whereIn('id', $bookIds)->lockForUpdate()->get();

                if ($books->count() !== count($bookIds)) {
                    throw new \DomainException('Beberapa buku di keranjang sudah tidak tersedia.');
                }

                foreach ($books as $book) {
                    if ($book->available_stock <= 0) {
                        throw new \DomainException("Maaf, stok buku '{$book->title}' sedang kosong.");
                    }
                }

                $receipt = LoanReceipt::create([
                    'user_id' => $borrower->id,
                    'processed_by' => $isPustakawan ? $currentUser->id : null,
                    'loan_date' => $loanDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'status' => $isPustakawan ? LoanReceipt::STATUS_APPROVED : LoanReceipt::STATUS_PENDING,
                ]);

                $receipt->update(['receipt_number' => LoanReceipt::numberFor($receipt)]);

                foreach ($books as $book) {
                    if ($isPustakawan) {
                        $book->decrement('available_stock');
                    }

                    Loan::create([
                        'user_id' => $borrower->id,
                        'book_id' => $book->id,
                        'loan_receipt_id' => $receipt->id,
                        'loan_date' => $loanDate->toDateString(),
                        'due_date' => $dueDate->toDateString(),
                        'status' => $loanStatus,
                    ]);
                }

                return $receipt;
            });
        } catch (\DomainException $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($fromCart) {
            $request->session()->forget(CartController::SESSION_KEY);
        }

        $count = count($bookIds);
        $message = $isPustakawan
            ? "Peminjaman {$count} buku untuk {$borrower->name} berhasil dicatat."
            : "Permintaan peminjaman {$count} buku berhasil diajukan. Tunjukkan struk ini ke pustakawan untuk pengambilan buku.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'receipt' => $receipt->load(['user', 'loans.book']),
                'receipt_url' => route('loan-receipts.show', $receipt),
            ]);
        }

        return redirect()->route('loan-receipts.show', $receipt)->with('success', $message);
    }

    /**
     * Approve a single pending loan (legacy rows without a receipt only).
     * Loans that belong to a receipt are approved via LoanReceiptController@approve.
     */
    public function approve(Loan $loan): RedirectResponse
    {
        Gate::authorize('approve', $loan);

        if ($loan->loan_receipt_id) {
            return redirect()->route('loan-receipts.show', $loan->loan_receipt_id)
                ->with('error', 'Peminjaman ini bagian dari satu struk. Setujui melalui halaman struk.');
        }

        $book = $loan->book;

        if ($book->available_stock <= 0) {
            return back()->with('error', "Stok buku '{$book->title}' sedang habis.");
        }

        DB::transaction(function () use ($loan, $book) {
            $book->decrement('available_stock');
            $loan->update([
                'status' => 'dipinjam',
                'loan_date' => Carbon::today()->toDateString(),
                'due_date' => Carbon::today()->addDays(7)->toDateString(),
            ]);
        });

        return back()->with('success', "Permintaan peminjaman disetujui. Buku telah dipinjam oleh {$loan->user->name}.");
    }

    /**
     * Process return of a single legacy loan (no receipt). Loans that belong to a
     * receipt are returned together via LoanReceiptController@returnAll.
     */
    public function returnBook(ReturnLoanRequest $request, Loan $loan): RedirectResponse|JsonResponse
    {
        Gate::authorize('returnBook', $loan);

        if ($loan->loan_receipt_id) {
            return $this->fail($request, 'Pengembalian harus dilakukan untuk seluruh buku pada struk. Gunakan tombol "Kembalikan Semua".');
        }

        $returnDate = $request->input('return_date') ? Carbon::parse($request->input('return_date')) : Carbon::today();

        DB::transaction(function () use ($loan, $returnDate, $request) {
            $loan->book->increment('available_stock');
            $loan->update([
                'return_date' => $returnDate->toDateString(),
                'status' => 'kembali',
                'librarian_notes' => $request->input('librarian_notes'),
            ]);
        });

        $message = "Buku '{$loan->book->title}' berhasil dikembalikan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('loans.index')->with('success', $message);
    }

    /**
     * Export loan history to print-ready XLSX.
     */
    public function export(Request $request, ExcelReportService $excelService): BinaryFileResponse
    {
        Gate::authorize('export', Loan::class);

        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $filePath = $excelService->generateLoanReport($status, $startDate, $endDate);
        $fileName = 'laporan_peminjaman_'.date('Ymd_His').'.xlsx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function fail(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
    }
}