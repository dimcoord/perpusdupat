<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('registration screen can be rendered for guests', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('Daftar Akun Siswa Baru');
});

test('authenticated student is redirected away from registration screen', function () {
    $student = User::factory()->create(['nis_nip' => 'STD999', 'role' => 'murid']);

    $response = $this->actingAs($student)->get('/register');

    $response->assertRedirect('/');
});

test('new student can register with email and valid data', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'nis_nip' => '2024109',
        'email' => 'budi.santoso@gmail.com',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success', 'Pendaftaran berhasil! Akun Anda telah aktif, silakan masuk dengan NIS atau Email dan kata sandi Anda.');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'nis_nip' => '2024109',
        'email' => 'budi.santoso@gmail.com',
        'phone' => '081234567890',
        'role' => 'murid',
    ]);

    $user = User::where('email', 'budi.santoso@gmail.com')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('registration fails if nis is already registered', function () {
    User::factory()->create([
        'nis_nip' => '2024101',
        'email' => 'existing@gmail.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'Siswa Baru',
        'nis_nip' => '2024101',
        'email' => 'new@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['nis_nip']);
    $this->assertDatabaseMissing('users', [
        'email' => 'new@gmail.com',
    ]);
});

test('registration fails if email is already registered', function () {
    User::factory()->create([
        'nis_nip' => '2024101',
        'email' => 'same.email@gmail.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'Siswa Baru',
        'nis_nip' => '2024102',
        'email' => 'same.email@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('registration fails if password confirmation does not match', function () {
    $response = $this->post('/register', [
        'name' => 'Siswa Baru',
        'nis_nip' => '2024103',
        'email' => 'test@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'mismatched-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});
