<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loan\ReturnLoanRequest;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Models\Book;
use App\Models\Loan;
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
     * Display a listing of book loans.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Loan::class);

        $user = $request->user();
        $status = $request->query('status');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Loan::with([
            'user:id,name,nis_nip,phone,role',
            'book:id,title,isbn,rack_location,available_stock',
        ]);

        // Student is strictly scoped to own records
        if ($user->isMurid()) {
            $query->where('user_id', $user->id);
        } else {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('nis_nip', 'like', "%{$search}%");
                    })->orWhereHas('book', function ($bq) use ($search) {
                        $bq->where('title', 'like', "%{$search}%")
                            ->orWhere('isbn', 'like', "%{$search}%");
                    });
                });
            }
        }

        if ($status && $status !== 'semua') {
            if ($status === 'terlambat') {
                $query->overdue();
            } else {
                $query->where('status', $status);
            }
        }

        if ($startDate) {
            $query->whereDate('loan_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('loan_date', '<=', $endDate);
        }

        $loans = $query->latest('id')->paginate(20)->withQueryString();

        if ($request->wantsJson() || $request->header('X-Alpine-Request')) {
            return response()->json([
                'loans' => $loans->items(),
                'total' => $loans->total(),
            ]);
        }

        // Calculate potential dynamic overdue status for active loans
        foreach ($loans as $loan) {
            if ($loan->status === 'dipinjam' && Carbon::parse($loan->due_date)->isPast()) {
                $loan->is_overdue = true;
            } else {
                $loan->is_overdue = ($loan->status === 'terlambat');
            }
        }

        return view('loans.index', compact('loans', 'status', 'search', 'startDate', 'endDate'));
    }

    /**
     * Store a newly created loan or loan request.
     */
    public function store(StoreLoanRequest $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $currentUser = $request->user();
        $book = Book::findOrFail($request->input('book_id'));

        // Determine target borrower
        if ($currentUser->isPustakawan()) {
            if ($request->filled('nis_nip')) {
                $borrower = User::where('nis_nip', trim($request->input('nis_nip')))->first();
                if (! $borrower) {
                    return back()->with('error', 'Siswa dengan NIS tersebut tidak ditemukan.');
                }
            } elseif ($request->filled('user_id')) {
                $borrower = User::findOrFail($request->input('user_id'));
            } else {
                return back()->with('error', 'Peminjam harus ditentukan.');
            }
        } else {
            $borrower = $currentUser;
        }

        // Check if student has overdue books
        $hasOverdue = Loan::where('user_id', $borrower->id)
            ->where('status', 'dipinjam')
            ->whereDate('due_date', '<', Carbon::today())
            ->exists();

        if ($hasOverdue) {
            $errMsg = 'Peminjaman tidak dapat diproses karena peminjam memiliki buku yang melewati batas jatuh tempo.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg);
        }

        // Check stock
        if ($book->available_stock <= 0) {
            $errMsg = "Maaf, stok buku '{$book->title}' sedang kosong.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg);
        }

        $loanDate = $request->input('loan_date') ? Carbon::parse($request->input('loan_date')) : Carbon::today();
        $dueDate = $request->input('due_date') ? Carbon::parse($request->input('due_date')) : $loanDate->copy()->addDays(7);

        $status = $currentUser->isPustakawan() ? 'dipinjam' : 'menunggu_persetujuan';

        $loan = DB::transaction(function () use ($borrower, $book, $loanDate, $dueDate, $status) {
            if ($status === 'dipinjam') {
                $book->decrement('available_stock');
            }

            return Loan::create([
                'user_id' => $borrower->id,
                'book_id' => $book->id,
                'loan_date' => $loanDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => $status,
            ]);
        });

        $message = $currentUser->isPustakawan()
            ? "Peminjaman buku '{$book->title}' untuk {$borrower->name} berhasil dicatat."
            : "Permintaan peminjaman buku '{$book->title}' berhasil diajukan. Silakan temui pustakawan untuk pengambilan buku.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'loan' => $loan->load(['user', 'book']),
            ]);
        }

        return redirect()->route('loans.index')->with('success', $message);
    }

    /**
     * Approve a pending loan request (Librarian only).
     */
    public function approve(Loan $loan): RedirectResponse
    {
        Gate::authorize('approve', $loan);

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
     * Process return of a borrowed book (Librarian only).
     */
    public function returnBook(ReturnLoanRequest $request, Loan $loan): RedirectResponse|JsonResponse
    {
        Gate::authorize('returnBook', $loan);

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
}