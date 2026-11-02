<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Show self-service kiosk screen for Student Client PC station.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();
        $visitCountToday = Visit::whereDate('visit_date', $today)->count();

        $availableBooks = Book::with('category:id,name,code')
            ->available()
            ->orderBy('title')
            ->get()
            ->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author ?? 'Anonim',
                'publisher' => $b->publisher ?? '-',
                'publication_year' => $b->publication_year ?? '-',
                'category_name' => $b->category?->name ?? 'Umum',
                'category_code' => $b->category?->code ?? '-',
                'rack_location' => $b->rack_location ?? '-',
                'total_stock' => $b->total_stock,
                'available_stock' => $b->available_stock,
                'isbn' => $b->isbn,
                'cover_url' => $b->cover_url,
            ]);

        $categories = Category::has('books')->orderBy('name')->get(['id', 'name', 'code']);

        return view('kiosk.index', compact('availableBooks', 'categories', 'visitCountToday'));
    }

    /**
     * Fast AJAX check-in endpoint for kiosk.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'nis' => ['required', 'string'],
            'purpose' => ['required', 'string', 'max:50'],
        ]);

        $nis = trim($request->input('nis'));
        $user = User::where('nis_nip', $nis)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "NIS '{$nis}' tidak ditemukan dalam database perpustakaan.",
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        $visit = Visit::create([
            'user_id' => $user->id,
            'visit_date' => $today,
            'purpose' => $request->input('purpose'),
            'notes' => 'Presensi Mandiri Kiosk LAN',
        ]);

        $todayVisitsCount = Visit::whereDate('visit_date', $today)->count();

        return response()->json([
            'success' => true,
            'message' => "Selamat datang, {$user->name}! Presensi berhasil dicatat.",
            'data' => [
                'id' => $visit->id,
                'name' => $user->name,
                'nis' => $user->nis_nip,
                'purpose' => $visit->purpose,
                'time' => $visit->created_at->format('H:i'),
                'total_today' => $todayVisitsCount,
            ],
        ]);
    }
}
