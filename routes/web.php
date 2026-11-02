<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isPustakawan()
            ? redirect()->route('librarian.dashboard')
            : redirect()->route('student.dashboard');
    }

    return redirect()->route('login');
});

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Role-specific Dashboards
    Route::middleware('role:pustakawan')->group(function () {
        Route::get('/librarian/dashboard', [DashboardController::class, 'librarian'])->name('librarian.dashboard');

        // Librarian Books Management
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
        Route::get('/books/template', [BookController::class, 'downloadTemplate'])->name('books.template');

        // Librarian Exports & Admin Actions
        Route::get('/visits/export', [VisitController::class, 'export'])->name('visits.export');
        Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');
        Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
        Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
        Route::get('/loans/export', [LoanController::class, 'export'])->name('loans.export');

        // Kiosk
        Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
        Route::post('/kiosk/check-in', [KioskController::class, 'checkIn'])->name('kiosk.check-in');

        // Visits
        Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');
    });

    Route::middleware('role:murid')->group(function () {
        Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
    });

    // Shared Authenticated Routes (Guarded internally by Policies)
    Route::get('/books', [BookController::class, 'index'])->name('books.index');

    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');

    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
});
