# Panduan & Spesifikasi Teknis: Auto-Cancel Booking Buku (Tim Backend)

Dokumen ini disusun sebagai panduan teknis bagi tim backend untuk melengkapi fitur **Pengajuan Peminjaman (Booking)** agar sistem dapat membatalkan pengajuan secara otomatis jika buku fisik tidak diambil siswa dalam kurun waktu 2 hari.

---

## 1. Latar Belakang & Aturan Bisnis

1. **Alur Pengajuan Siswa:**
   * Siswa memilih buku di katalog dan menekan tombol *“Ajukan Sekarang”*.
   * Di sisi antarmuka (frontend), siswa telah menyetujui ketentuan: **buku wajib diambil di loket perpustakaan maksimal dalam 2 hari sekolah**.
   * Peminjaman tersimpan dengan status: `menunggu_persetujuan`.
2. **Kebutuhan Sistem:**
   * Jika dalam **2 hari (2x24 jam)** sejak diajukan buku belum diserahkan dan belum disetujui pustakawan, pengajuan harus **otomatis kedaluwarsa/dibatalkan** agar data tidak menggantung.
   * Pustakawan juga membutuhkan tombol manual **"Tolak / Batalkan Booking"** di loket sirkulasi jika siswa batal meminjam di tempat.

---

## 2. Rencana Perubahan Database (Migration)

Saat ini kolom `status` pada tabel `loans` hanya memiliki nilai:
```php
$table->enum('status', ['menunggu_persetujuan', 'dipinjam', 'kembali', 'terlambat']);
```

### Langkah Rekomendasi:
Buat migration baru untuk menambahkan opsi `'dibatalkan'` (atau `'kedaluwarsa'`):
```bash
php artisan make:migration add_dibatalkan_to_loans_status_enum
```

Contoh isi migration:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk MySQL / MariaDB:
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('menunggu_persetujuan', 'dipinjam', 'kembali', 'terlambat', 'dibatalkan') DEFAULT 'menunggu_persetujuan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('menunggu_persetujuan', 'dipinjam', 'kembali', 'terlambat') DEFAULT 'menunggu_persetujuan'");
    }
};
```

---

## 3. Logika Auto-Cancel (Artisan Scheduled Command)

### A. Buat Console Command
Jalankan perintah:
```bash
php artisan make:command CancelExpiredBookings
```

Implementasi di `app/Console/Commands/CancelExpiredBookings.php`:
```php
namespace App\Console\Commands;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    protected $signature = 'loans:cancel-expired';
    protected $description = 'Batalkan otomatis pengajuan booking buku yang tidak diambil lebih dari 2 hari';

    public function handle(): int
    {
        $expiredThreshold = Carbon::now()->subDays(2);

        // Ambil semua booking yang menggantung lebih dari 2 hari
        $cancelledCount = Loan::where('status', 'menunggu_persetujuan')
            ->where('created_at', '<', $expiredThreshold)
            ->update([
                'status' => 'dibatalkan',
                'librarian_notes' => 'Otomatis dibatalkan sistem: Melewati batas waktu pengambilan 2 hari.',
            ]);

        $this->info("Berhasil membatalkan {$cancelledCount} pengajuan peminjaman yang kedaluwarsa.");

        return Command::SUCCESS;
    }
}
```

> **Catatan Pengelolaan Stok:**
> Pada arsitektur `LoanController`, saat pengajuan dibuat (`menunggu_persetujuan`), stok buku **belum dikurangi**. Stok baru dikurangi saat pustakawan menekan tombol `approve`. Oleh karena itu, saat auto-cancel dijalankan, **tidak perlu melakukan increment stok**, cukup mengubah statusnya saja.

### B. Daftarkan di Scheduler (`routes/console.php`)
```php
use Illuminate\Support\Facades\Schedule;

// Jalankan pemeriksaan pembatalan setiap hari pada jam 00:00 (atau tiap jam)
Schedule::command('loans:cancel-expired')->daily();
```

---

## 4. Fitur Manual "Tolak Booking" oleh Pustakawan

Selain otomatisasi background, pustakawan di loket memerlukan opsi untuk menolak atau membatalkan pengajuan secara manual.

### A. Route Baru (`routes/web.php`)
Tambahkan di dalam grup `role:pustakawan`:
```php
Route::post('/loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
```

### B. Method di `LoanController.php`
```php
/**
 * Tolak / Batalkan pengajuan peminjaman oleh pustakawan.
 */
public function reject(Request $request, Loan $loan): RedirectResponse
{
    Gate::authorize('approve', $loan);

    if ($loan->status !== 'menunggu_persetujuan') {
        return back()->with('error', 'Hanya pengajuan yang sedang menunggu persetujuan yang dapat ditolak.');
    }

    $loan->update([
        'status' => 'dibatalkan',
        'librarian_notes' => $request->input('notes', 'Pengajuan ditolak oleh pustakawan loket.'),
    ]);

    return back()->with('success', "Pengajuan peminjaman untuk buku '{$loan->book->title}' berhasil ditolak/dibatalkan.");
}
```

---

## 5. Ringkasan Status untuk Frontend

| Status | Makna di UI | Warna Badge |
|---|---|---|
| `menunggu_persetujuan` | Menunggu Persetujuan (Batas ambil loket maks. 2 hari) | Amber / Kuning |
| `dipinjam` | Buku sedang aktif dipinjam siswa (durasi 7 hari) | Emerald / Hijau |
| `terlambat` | Melewati batas 7 hari pengembalian | Rose / Merah |
| `kembali` | Selesai dikembalikan | Slate / Abu-abu |
| `dibatalkan` *(baru)* | Booking hangus / kedaluwarsa / ditolak | Gray / Merah Pudar |

