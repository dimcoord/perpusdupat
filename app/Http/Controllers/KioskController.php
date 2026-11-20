<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Show self-service kiosk check-in screen for Student Client PC station.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        $todayVisits = Visit::with('user:id,name,nis_nip')
            ->whereDate('visit_date', $today)
            ->latest('id')
            ->take(15)
            ->get();

        $visitCountToday = Visit::whereDate('visit_date', $today)->count();

        return view('kiosk.index', compact('todayVisits', 'visitCountToday'));
    }

    /**
     * Fast AJAX check-in endpoint for kiosk (email + password).
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'purpose' => ['required', Rule::in(['Presensi', 'Pinjam Buku'])],
        ]);

        $email = Str::lower(trim($validated['email']));

        // Batasi percobaan brute force per email + IP kiosk
        $throttleKey = 'kiosk-checkin|' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        // validate() hanya mengecek kredensial, tidak membuat sesi login di PC kiosk
        if (! Auth::validate(['email' => $email, 'password' => $validated['password']])) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi salah.',
            ], 422);
        }

        RateLimiter::clear($throttleKey);

        $user = User::where('email', $email)->firstOrFail();

        $today = Carbon::today()->toDateString();

        $visit = Visit::create([
            'user_id' => $user->id,
            'visit_date' => $today,
            'purpose' => $validated['purpose'],
            'notes' => 'Presensi Mandiri Kiosk LAN',
        ]);

        $todayVisitsCount = Visit::whereDate('visit_date', $today)->count();

        // Pinjam Buku: loginkan siswa agar bisa membuka halaman katalog buku
        $redirect = null;
        if ($validated['purpose'] === 'Pinjam Buku') {
            Auth::login($user);
            $request->session()->regenerate();
            $redirect = route('books.index');
        }

        return response()->json([
            'success' => true,
            'message' => "Selamat datang, {$user->name}! Presensi berhasil dicatat.",
            'redirect' => $redirect,
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