<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('kiosk screen can be rendered by librarian session', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);

    $response = $this->actingAs($librarian)->get(route('kiosk.index'));
    $response->assertStatus(200);
    $response->assertSee('Kiosk Presensi Mandiri');
});

test('kiosk check-in requires email and password', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'email' => '',
        'password' => '',
        'purpose' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email', 'password', 'purpose']);
});

test('kiosk check-in rejects incorrect password', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'nis_nip' => '9999002',
        'email' => 'siswa@perpusdupat.sch.id',
        'role' => 'murid',
        'password' => Hash::make('correctpassword'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'email' => 'siswa@perpusdupat.sch.id',
        'password' => 'wrongpassword',
        'purpose' => 'Presensi',
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'Email atau kata sandi salah.',
    ]);
});

test('kiosk check-in succeeds with valid password for Presensi without session login', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'email' => 'siswa1@perpusdupat.sch.id',
        'nis_nip' => '9999003',
        'role' => 'murid',
        'password' => Hash::make('studentpass'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'email' => 'siswa1@perpusdupat.sch.id',
        'password' => 'studentpass',
        'purpose' => 'Presensi',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'redirect' => null,
        'data' => [
            'nis' => '9999003',
            'purpose' => 'Presensi',
        ],
    ]);

    $this->assertDatabaseHas('visits', [
        'user_id' => $student->id,
        'purpose' => 'Presensi',
    ]);
});

test('kiosk check-in for Pinjam Buku logs student in and redirects to catalog', function () {
    $librarian = User::factory()->create(['nis_nip' => 'LIB001', 'role' => 'pustakawan']);
    $student = User::factory()->create([
        'email' => 'siswa2@perpusdupat.sch.id',
        'nis_nip' => '9999004',
        'role' => 'murid',
        'password' => Hash::make('studentpass'),
    ]);

    $response = $this->actingAs($librarian)->postJson(route('kiosk.check-in'), [
        'email' => 'siswa2@perpusdupat.sch.id',
        'password' => 'studentpass',
        'purpose' => 'Pinjam Buku',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'redirect' => route('books.index'),
        'data' => [
            'nis' => '9999004',
            'purpose' => 'Pinjam Buku',
        ],
    ]);

    $this->assertAuthenticatedAs($student);
});