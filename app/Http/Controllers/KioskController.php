<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * Fast AJAX check-in endpoint for kiosk.
     * Secure AJAX check-in endpoint for kiosk (Anti-Titip Absen via password verification).
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'nis' => ['required', 'string'],
            'password' => ['required', 'string'],
            'purpose' => ['required', 'string', 'max:50'],
        ], [
            'nis.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi untuk verifikasi kehadiran.',
            'purpose.required' => 'Pilih tujuan kunjungan Anda di perpustakaan.',
        ]);

        $nis = trim($request->input('nis'));
        $password = $request->input('password');

        $user = User::where('nis_nip', $nis)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "NIS '{$nis}' tidak ditemukan dalam database perpustakaan.",
                'message' => "Nomor NIS '{$nis}' tidak terdaftar dalam database perpustakaan.",
            ], 404);
        }

        // Verifikasi kata sandi akun untuk mencegah titip absen
        if (! Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi salah! Demi mencegah titip absen, presensi wajib dilakukan oleh pemilik akun langsung.',
            ], 401);
        }

        $today = Carbon::today()->toDateString();

        // Cek apakah siswa sudah presensi hari ini
        $existingVisitToday = Visit::where('user_id', $user->id)
            ->whereDate('visit_date', $today)
            ->latest('id')
            ->first();

        if ($existingVisitToday) {
            $existingVisitToday->update([
                'purpose' => $request->input('purpose'),
                'notes' => 'Presensi Kiosk Terverifikasi (Update Aktivitas)',
            ]);

            $todayVisitsCount = Visit::whereDate('visit_date', $today)->count();

            return response()->json([
                'success' => true,
                'is_update' => true,
                'message' => "Halo kembali, {$user->name}! Presensi kehadiran Anda hari ini telah diperbarui.",
                'data' => [
                    'id' => $existingVisitToday->id,
                    'name' => $user->name,
                    'nis' => $user->nis_nip,
                    'purpose' => $existingVisitToday->purpose,
                    'time' => $existingVisitToday->updated_at->format('H:i'),
                    'total_today' => $todayVisitsCount,
                ],
            ]);
        }

        $visit = Visit::create([
            'user_id' => $user->id,
            'visit_date' => $today,
            'purpose' => $request->input('purpose'),
            'notes' => 'Presensi Mandiri Kiosk LAN',
            'notes' => 'Presensi Kiosk Terverifikasi (Anti-Titip Absen)',
        ]);

        $todayVisitsCount = Visit::whereDate('visit_date', $today)->count();

        return response()->json([
            'success' => true,
            'message' => "Selamat datang, {$user->name}! Presensi berhasil dicatat.",
            'is_update' => false,
            'message' => "Presensi Terverifikasi! Selamat datang di perpustakaan, {$user->name}.",
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
