<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isPustakawan()
                ? redirect()->route('librarian.dashboard')
                : redirect()->route('student.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis_nip' => [
                'required',
                'string',
                'max:30',
                'unique:users,nis_nip',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nis_nip.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'nis_nip.unique' => 'NIS ini sudah terdaftar di sistem.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $validated['name'],
            'nis_nip' => trim($validated['nis_nip']),
            'email' => trim($validated['email']),
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'password' => Hash::make($validated['password']),
            'role' => 'murid',
        ]);

        return redirect()->route('login')->with(
            'success',
            'Pendaftaran berhasil! Akun Anda telah aktif, silakan masuk dengan NIS atau Email dan kata sandi Anda.'
        );
    }
}
