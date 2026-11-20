<?php

use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('kiosk screen can be rendered by librarian session', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);

    $response = $this->actingAs($librarian)->get(route('kiosk.index'));
    $response->assertStatus(200);
    $response->assertSee('Anti-Titip Absen');
});

test('kiosk check-in requires password to prevent titip absen', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'nis_nip' => '9999001',
        'role' => 'murid',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'nis' => '9999001',
        'purpose' => 'Membaca Buku',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

test('kiosk check-in rejects incorrect password', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'nis_nip' => '9999002',
        'role' => 'murid',
        'password' => Hash::make('correctpassword'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'nis' => '9999002',
        'password' => 'wrongpassword',
        'purpose' => 'Pinjam / Kembali Buku',
    ]);

    $response->assertStatus(401);
    $response->assertJson([
        'success' => false,
        'message' => 'Kata sandi salah! Demi mencegah titip absen, presensi wajib dilakukan oleh pemilik akun langsung.',
    ]);
});

test('kiosk check-in succeeds with valid password and records activity purpose', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'nis_nip' => '9999003',
        'role' => 'murid',
        'password' => Hash::make('studentpass'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'nis' => '9999003',
        'password' => 'studentpass',
        'purpose' => 'Mengerjakan Tugas',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'nis' => '9999003',
            'purpose' => 'Mengerjakan Tugas',
        ],
    ]);

    $this->assertDatabaseHas('visits', [
        'user_id' => $student->id,
        'purpose' => 'Mengerjakan Tugas',
    ]);
});