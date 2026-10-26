<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Librarian dashboard view with performance-optimized aggregates.
     */
    public function librarian(Request $request): View
    {
        $today = Carbon::today()->toDateString();

        $stats = [
            'total_titles' => Book::count(),
            'total_stock' => (int) Book::sum('total_stock'),
            'available_stock' => (int) Book::sum('available_stock'),
            'today_visits' => Visit::whereDate('visit_date', $today)->count(),
            'pending_loans' => Loan::where('status', 'menunggu_persetujuan')->count(),
            'active_loans' => Loan::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'overdue_loans' => Loan::overdue()->count(),
        ];

        // Recent visits with eager loaded user
        $recentVisits = Visit::with('user:id,name,nis_nip,role')
            ->whereDate('visit_date', $today)
            ->latest('id')
            ->take(10)
            ->get();

        // Recent loans with eager loaded relations
        $recentLoans = Loan::with(['user:id,name,nis_nip', 'book:id,title,isbn'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('dashboard.librarian', compact('stats', 'recentVisits', 'recentLoans'));
    }

    /**
     * Student dashboard view strictly scoped to authenticated student.
     */
    public function student(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'active_loans' => Loan::where('user_id', $user->id)
                ->whereIn('status', ['dipinjam', 'terlambat', 'menunggu_persetujuan'])
                ->count(),
            'completed_loans' => Loan::where('user_id', $user->id)
                ->where('status', 'kembali')
                ->count(),
            'total_visits' => Visit::where('user_id', $user->id)->count(),
        ];

        $myActiveLoans = Loan::with('book:id,title,author,isbn,rack_location')
            ->where('user_id', $user->id)
            ->whereIn('status', ['dipinjam', 'terlambat', 'menunggu_persetujuan'])
            ->latest('loan_date')
            ->get();

        $myRecentVisits = Visit::where('user_id', $user->id)
            ->latest('visit_date')
            ->take(5)
            ->get();

        return view('dashboard.student', compact('stats', 'myActiveLoans', 'myRecentVisits'));
    }
}
