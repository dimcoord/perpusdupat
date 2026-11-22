# Perpus Dupat — Panduan Penggunaan dan Belajar Proyek

Perpus Dupat adalah aplikasi perpustakaan sekolah untuk mengelola katalog buku, peminjaman, pengembalian, kunjungan, dan laporan Excel. Aplikasi juga menyediakan kios presensi tanpa login serta API untuk integrasi scanner ISBN.

Dokumentasi fitur ini disusun dari kode repositori pada 28 September 2026. Pada 5 Oktober 2026, ditambahkan [panduan menjalankan web dari awal sampai selesai](PANDUAN-MENJALANKAN.md), termasuk instalasi Herd, fork, konfigurasi lokal, login, menutup server, menjalankan kembali, dan penyelesaian masalah yang dialami bersama.

**Untuk mulai menjalankan aplikasi, baca [PANDUAN-MENJALANKAN.md](PANDUAN-MENJALANKAN.md).** Panduan tersebut juga mencatat hasil pemeriksaan PHP 8.3, pemblokiran PHP 8.4 oleh Windows, serta penanganan OPcache tanpa mematikan Smart App Control. Versi di bawah berasal dari berkas lock, bukan hasil pemeriksaan paket terpasang.

Untuk demo dari scanner Flutter di HP sampai buku muncul di web, ikuti [Panduan demo web dan scanner ISBN](PANDUAN-DEMO-WEB-SCANNER.md).

## 1. Gambaran fitur dan pengguna

| Pengguna | Fitur utama |
| --- | --- |
| Pustakawan | Dashboard statistik, tambah/edit/hapus buku, impor katalog, catat pinjaman, setujui pengajuan, proses pengembalian, kelola kunjungan, ekspor laporan |
| Murid | Dashboard pribadi, telusuri katalog, ajukan pinjaman, lihat pinjaman dan kunjungan pribadi, catat kunjungan |
| Pengunjung kios | Masukkan NIS yang sudah terdaftar dan tujuan kunjungan tanpa login |
| Perangkat scanner | Kirim data ISBN melalui API bertoken untuk membuat buku atau menambah stok |

Belum tersedia halaman registrasi, pengelolaan akun murid, perubahan password, atau pengelolaan kategori khusus pada route yang ada. Akun awal dan kategori contoh dibuat oleh seeder. Penambahan akun operasional memerlukan penanganan oleh pengembang; jangan mencari menu pengguna yang belum diimplementasikan.

## 2. Teknologi

| Bagian | Teknologi |
| --- | --- |
| Backend | PHP dengan persyaratan proyek `^8.3`; Laravel `v13.33.0` di `composer.lock` |
| Antarmuka | Blade untuk HTML, Alpine.js 3 untuk interaksi, Tailwind CSS 4 untuk tampilan |
| Build aset | Vite `8.3.1` di `package-lock.json` |
| Database bawaan | SQLite sesuai `.env.example` |
| Autentikasi API | Laravel Sanctum `v4.3.3` di lock |
| Excel | PhpSpreadsheet `5.10.0`, FastExcel `v5.17.0` di lock |
| Pengujian | Pest; konfigurasi database pengujian SQLite dalam memori |

Composer akan memeriksa kecocokan versi PHP dan ekstensi terhadap seluruh dependensi. Vite yang terkunci mensyaratkan Node.js `^20.19.0` atau `>=22.12.0`.

## 3. Instalasi lokal di Windows / PowerShell

Langkah berikut untuk salinan proyek baru dan database lokal untuk belajar. Jalankan dari folder proyek. Pastikan PHP, Composer, Node.js, dan npm sudah tersedia di PATH.

```powershell
Set-Location D:\perpus24\perpusdupat
php --version
composer --version
node --version
npm --version
composer install
```

Siapkan konfigurasi dan berkas SQLite tanpa menimpa yang sudah ada:

```powershell
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite
}
```

Dalam `.env`, gunakan `DB_CONNECTION=sqlite` seperti contoh bawaan. Session dan cache juga memakai database sehingga migrasi diperlukan. Jika memilih database lain, sesuaikan koneksi sebelum migrasi.

Untuk instalasi baru dengan `APP_KEY` kosong, jalankan:

```powershell
php artisan key:generate --no-interaction
php artisan migrate --no-interaction
php artisan db:seed --no-interaction
php artisan storage:link --no-interaction
npm ci --ignore-scripts
npm run build
php artisan serve --no-interaction
```

Buka alamat yang dicetak oleh perintah server. Dokumentasi ini tidak menetapkan alamat server aktif karena layanan belum dijalankan atau diperiksa. Jangan mengganti `APP_KEY` instalasi lama hanya untuk mengulang panduan.

`db:seed` mengisi akun demo, kategori DDC, delapan judul buku, contoh kunjungan dan pinjaman, sampul SVG lokal, serta token scanner. Simpan token dari keluaran terminal jika diperlukan.

**Seeder hanya untuk inisialisasi/demo:** menjalankannya ulang menambahkan kunjungan dan pinjaman contoh serta mencabut token scanner lama. Sebagian akun dan buku memakai `firstOrCreate`, tetapi keseluruhan seeder tidak aman dianggap sebagai proses reset yang dapat diulang tanpa efek tambahan.

Proyek menyediakan `library:init`, yang memanggil seeder. Opsi `--fresh` menghapus seluruh tabel lalu migrasi ulang; jangan gunakan pada database berisi data yang ingin dipertahankan. Panduan ini memakai `db:seed` langsung untuk inisialisasi demo.

### Saat mengembangkan tampilan

Jalankan `php artisan serve --no-interaction` di terminal pertama dan `npm run dev` di terminal kedua. `npm run build` menghasilkan aset untuk pemakaian tanpa server Vite. Script `composer run dev` juga tersedia di `composer.json`, tetapi belum diverifikasi pada lingkungan ini.

### Pemakaian uji coba di jaringan LAN

Setelah aset dibangun, server pengembangan dapat dijalankan dengan:

```powershell
php artisan serve --host=0.0.0.0 --port=8000 --no-interaction
```

Perangkat klien menggunakan IP LAN komputer server, port tersebut, lalu path `/login` atau `/kiosk`. Sesuaikan `APP_URL` dengan alamat server yang benar dan izinkan port tersebut pada jaringan privat bila diblokir firewall. Server ini untuk pengembangan/uji coba; dokumentasi ini belum memvalidasi deployment operasional.

## 4. Akun demo dan login

Akun berikut dibuat oleh `DatabaseSeeder`, bukan jaminan bahwa akun sudah ada pada database yang belum diinisialisasi.

| Peran | NIS/NIP atau login | Password demo |
| --- | --- | --- |
| Administrator | `admin` | `password` |
| Pustakawan | `198501012010011001` | `password` |
| Ahmad Pratama | `2024101` | `password` |
| Siti Nurhaliza | `2024102` | `password` |
| Budi Santoso | `2024103` | `password` |

Form login menerima NIS/NIP atau email akun. Setelah login, pustakawan masuk ke dashboard pustakawan dan murid ke dashboard murid. Lima percobaan gagal pada kombinasi login dan IP akan memicu pembatasan login sementara. Akun demo menggunakan password publik; siapkan kredensial berbeda sebelum pemakaian nyata melalui pengelolaan akun oleh pengembang.

## 5. Panduan pustakawan

### Mengelola buku

1. Login sebagai `admin`, lalu buka katalog buku (`/books`).
2. Gunakan pencarian, filter kategori, atau filter ketersediaan.
3. Buka tambah buku. Isi kategori, ISBN unik, judul, dan jumlah stok; lengkapi penulis, penerbit, tahun, serta lokasi rak bila diperlukan.
4. Jika mengunggah sampul, gunakan JPEG/JPG/PNG/WebP maksimal 2 MB.
5. Simpan. Buku baru memiliki stok tersedia sama dengan jumlah stok total.
6. Gunakan edit untuk memperbarui metadata dan stok. Perubahan stok total juga menyesuaikan stok tersedia berdasarkan selisihnya.

Buku dengan pinjaman berstatus `dipinjam`, `terlambat`, atau `menunggu_persetujuan` tidak dapat dihapus. `total_stock` adalah jumlah eksemplar keseluruhan; `available_stock` adalah yang tersedia untuk dipinjam.

### Mengimpor katalog

1. Buka fitur impor di katalog dan unduh template aplikasi.
2. Hapus atau ganti baris contoh template sebelum memasukkan koleksi sendiri.
3. Pertahankan header berikut:

| ISBN | Judul | Penulis | Penerbit | Tahun | Kode_Kategori | Lokasi_Rak | Jumlah |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 9786020332956 | Bumi Manusia | Pramoedya Ananta Toer | Hasta Mitra | 2005 | 800 | Rak-A1 | 2 |

4. Simpan ISBN dan kode kategori sebagai teks agar angka nol di depan tidak hilang.
5. Unggah berkas, lalu periksa jumlah baris berhasil, diperbarui, dan gagal.

Gunakan `.xlsx` dari template sebagai format utama. Validasi upload mengizinkan `.xlsx`, `.xls`, dan `.csv` maksimal 10 MB; kompatibilitas pembacaan `.xls` belum diuji dalam sesi ini.

ISBN dan judul wajib diisi; jumlah tidak boleh negatif. Kode kategori baru akan dibuat dengan nama generik, misalnya `Kategori 813`; kode kosong memakai kategori `000`.

**Jumlah merupakan stok tambahan untuk ISBN yang sudah ada.** Jika stok total awal 5 dan diimpor jumlah 2, stok total menjadi 7. Mengunggah ulang file yang sama menambah stok lagi. Metadata buku juga dapat diperbarui dari baris impor. Impor diproses per baris sehingga kegagalan sebagian baris tidak membatalkan baris yang sudah berhasil.

### Mencatat dan menyetujui peminjaman

- Untuk pengajuan murid: buka peminjaman (`/loans`), cari status menunggu persetujuan, lalu setujui saat buku diserahkan. Stok tersedia berkurang satu; tanggal pinjam menjadi hari persetujuan dan jatuh tempo tujuh hari sesudahnya.
- Untuk pencatatan langsung: buka form peminjaman baru, isi NIS peminjam, pilih buku, atur tanggal pinjam/jatuh tempo, lalu catat peminjaman. Status langsung `dipinjam` dan stok berkurang satu.

Stok harus tersedia. Saat membuat pinjaman, kode memeriksa pinjaman berstatus `dipinjam` yang telah lewat jatuh tempo. Pemeriksaan ini belum mencakup semua variasi status keterlambatan; lihat catatan implementasi di bagian 10.

### Memproses pengembalian

1. Cari transaksi aktif di daftar peminjaman.
2. Buka aksi pengembalian.
3. Periksa tanggal pengembalian, denda, dan catatan pustakawan.
4. Konfirmasikan penyelesaian. Status menjadi `kembali` dan stok tersedia bertambah satu.

Tarif bawaan adalah Rp1.000 per hari setelah jatuh tempo. Backend menghitung otomatis bila nilai denda tidak dikirim; nilai denda yang diisi pada form dipakai sebagai nilai akhir. Periksa nominal sebelum konfirmasi. Belum ada modul pembayaran atau penanda pelunasan denda terpisah.

### Kunjungan dan laporan

Buka kunjungan (`/visits`) untuk melihat presensi, mencatat kunjungan berdasarkan NIS, memfilter tanggal, atau menghapus catatan. Ekspor kunjungan menggunakan rentang tanggal. Ekspor peminjaman menggunakan status dan rentang tanggal pinjam. Hasil berupa `.xlsx`; kolom pencarian nama/judul tidak diteruskan sebagai filter ekspor.

## 6. Panduan murid

1. Login memakai NIS dan password.
2. Buka katalog, cari buku, lalu periksa ketersediaan dan lokasi rak.
3. Pilih **Ajukan Pinjam** dan konfirmasikan pengajuan.
4. Lihat pengajuan pada daftar pinjaman pribadi. Status awal `menunggu_persetujuan`.
5. Temui pustakawan untuk persetujuan dan pengambilan buku.
6. Kembalikan buku melalui pustakawan sebelum jatuh tempo; murid tidak mengonfirmasi pengembalian sendiri.
7. Gunakan halaman kunjungan untuk presensi dan riwayat kunjungan pribadi.

Pengajuan belum mengurangi stok atau menjamin reservasi eksemplar. Dashboard murid merangkum pinjaman aktif, pinjaman selesai, kunjungan, dan nominal denda yang tersimpan.

## 7. Kios kunjungan mandiri

Buka path `/kiosk` dari komputer presensi. Halaman ini tidak membutuhkan login.

1. Masukkan NIS yang sudah terdaftar.
2. Pilih tujuan kunjungan.
3. Kirim presensi dan tunggu pesan berhasil beserta nama pengunjung.

NIS yang tidak ditemukan ditolak. Kios menampilkan ringkasan kunjungan hari ini dan hingga 15 kunjungan terbaru. Pengiriman berulang tetap membuat catatan baru; jumlah kunjungan bukan jumlah pengunjung unik. Kios mencatat kunjungan, bukan transaksi peminjaman.

## 8. Integrasi scanner ISBN

Repositori ini menyediakan backend API; aplikasi mobile scanner terpisah tidak ditemukan dalam struktur proyek.

Endpoint: `POST /api/v1/books/isbn-scan` pada server aplikasi. Kirim header:

```text
Authorization: Bearer <TOKEN_SCANNER>
Accept: application/json
Content-Type: application/json
```

Contoh body:

```json
{
  "isbn": "9786020332956",
  "title": "Bumi Manusia",
  "author": "Pramoedya Ananta Toer",
  "stock": 1,
  "rack_location": "Rak-A1"
}
```

`isbn`, `title`, dan `stock` wajib, termasuk ketika ISBN sudah ada. Stok per permintaan antara 1 dan 1000. Field tambahan: `publisher`, `publication_year`, `category_id`, dan `cover_image` berupa string lokasi/URL, bukan upload berkas.

| Respons | Makna |
| --- | --- |
| 201, `data.action=book_created` | ISBN baru dibuat |
| 200, `data.action=stock_incremented` | ISBN lama ditambah stoknya dan metadata diperbarui |
| 401 | Autentikasi tidak sah/tidak tersedia |
| 422 | Validasi payload gagal |

Tanpa `category_id`, API memakai kategori `000`, termasuk saat memperbarui ISBN lama. Kirim ID kategori yang benar jika kategori lama perlu dipertahankan. Pengiriman ulang bukan operasi netral: stok akan bertambah lagi.

Untuk memeriksa keadaan token:

```powershell
php artisan library:token --no-interaction
```

Token lama tidak dapat ditampilkan kembali dalam bentuk asli. Jika perlu pengganti:

```powershell
php artisan library:token --regenerate --no-interaction
```

Regenerasi mencabut token akun scanner yang lama, sehingga konfigurasi perangkat perlu diperbarui. Perlakukan token sebagai kredensial. Route saat ini memakai `auth:sanctum` tanpa pemeriksaan role/ability khusus scanner pada request; jangan menganggap hak token sudah dibatasi hanya untuk satu perangkat.

## 9. Cara memahami kode

Alur umum: **browser → route → middleware → controller/Form Request → model atau service → database → Blade/JSON**. Middleware memeriksa login/peran; Form Request memvalidasi masukan; policy mengatur izin tindakan; controller mengoordinasikan proses; model mewakili data; Blade menampilkan halaman.

| Lokasi | Tanggung jawab |
| --- | --- |
| `routes/web.php` | Halaman web, login, kios, buku, pinjaman, kunjungan |
| `routes/api.php` | API Sanctum dan scanner versi 1 |
| `bootstrap/app.php` | Pendaftaran route, alias middleware `role`, respons exception API |
| `app/Http/Controllers` | Alur dashboard dan operasi aplikasi |
| `app/Http/Requests` | Validasi login, buku, pinjaman, kunjungan, scanner |
| `app/Http/Middleware/EnsureRole.php` | Pembatasan akses berdasarkan peran |
| `app/Policies` | Izin operasi buku, pinjaman, dan kunjungan |
| `app/Models` | Relasi dan query data; perhitungan denda berada di `Loan` |
| `app/Services/ExcelReportService.php` | Impor katalog, template, laporan Excel |
| `resources/views` | Tampilan Blade per fitur |
| `resources/js`, `resources/css` | Aset antarmuka yang dibangun Vite |
| `database/migrations` | Struktur tabel |
| `database/seeders/DatabaseSeeder.php` | Akun dan data demo |
| `app/Console/Commands` | Perintah inisialisasi dan token scanner |
| `tests/Feature` | Pengujian fitur yang sudah tersedia |

Relasi data utama:

- Satu `User` memiliki banyak `Loan` dan `Visit`.
- Satu `Category` memiliki banyak `Book`.
- Satu `Book` memiliki banyak `Loan`.
- Satu `Loan` menghubungkan seorang peminjam dengan satu buku; setiap transaksi mewakili satu eksemplar.

Status pinjaman: `menunggu_persetujuan` → `dipinjam` → `kembali`. Status `terlambat` juga digunakan; keterlambatan dapat dihitung dari jatuh tempo meskipun status tersimpan masih `dipinjam`.

### Urutan belajar yang disarankan

1. Baca `routes/web.php` dan cocokkan path dengan halaman yang dipakai.
2. Ikuti fitur katalog dari `BookController::index()` ke `Book`, lalu `resources/views/books/index.blade.php`.
3. Telusuri `LoanController::store()`, `approve()`, dan `returnBook()` untuk memahami perubahan status/stok.
4. Baca request dan policy terkait untuk membedakan validasi data dengan izin pengguna.
5. Baca migrasi dan seeder untuk memahami asal struktur/data contoh.
6. Baca `tests/Feature/PolicyAndAuthorizationTest.php` sebagai contoh perilaku yang diharapkan.
7. Setelah alur utama dipahami, pelajari service Excel dan API scanner.

Latihan lokal: login sebagai Ahmad, ajukan buku yang tersedia, logout, login sebagai admin, setujui pengajuan, lalu proses pengembalian. Amati perubahan status dan stok pada setiap langkah. Gunakan database demo karena latihan menghasilkan transaksi nyata di database yang dipakai.

## 10. Batasan implementasi yang perlu diketahui

Catatan ini berasal dari pembacaan kode, bukan hasil audit menyeluruh atau pengujian runtime.

- Pemeriksaan peminjam terlambat di `LoanController::store()` hanya mencari status `dipinjam` dengan jatuh tempo lewat; status tersimpan `terlambat` tidak diperiksa oleh kondisi itu. Persetujuan juga tidak mengulang pemeriksaan keterlambatan peminjam.
- Angka denda dashboard merupakan penjumlahan `fine_amount`. Walaupun variabel dashboard murid bernama `unpaid_fines`, belum ada pencatatan pembayaran yang membedakan lunas/belum lunas.
- Seeder memuat transaksi dan stok contoh yang ditentukan langsung, sehingga jangan menganggap data demo sebagai hasil rekonsiliasi stok operasional.
- Zona waktu aplikasi di `config/app.php` masih `UTC`; ini memengaruhi pergantian hari, kunjungan, dan jatuh tempo. Sesuaikan konfigurasi dengan zona sekolah sebelum pemakaian operasional.
- Tampilan memuat font dari layanan eksternal, dan sampul dari URL scanner membutuhkan akses ke sumbernya. Ketersediaan semua aset secara offline belum diverifikasi.
- Belum ada alur penolakan/pembatalan pengajuan atau perpanjangan pinjaman pada route yang tersedia.

## 11. Pemeriksaan dan pemecahan masalah

Setelah dependensi tersedia, periksa versi dan route aktual:

```powershell
composer show --direct
php artisan route:list --except-vendor --no-interaction
php artisan list --no-interaction
```

Pengujian yang tersedia mencakup otorisasi, persetujuan/pengembalian, API scanner, sampul, dan laporan Excel. Untuk menjalankan semuanya:

```powershell
php artisan test --compact --no-interaction
```

| Masalah | Yang diperiksa |
| --- | --- |
| `php` atau `composer` tidak dikenali | Instalasi executable dan PATH terminal |
| `vendor/autoload.php` tidak ditemukan | Jalankan `composer install` |
| SQLite/tabel tidak ditemukan | Berkas database, koneksi `.env`, ekstensi PDO SQLite, migrasi |
| Kunci aplikasi kosong | Untuk instalasi baru, jalankan `key:generate` |
| Vite manifest hilang/tampilan belum berubah | Jalankan `npm run build` atau server `npm run dev` |
| Sampul upload tidak muncul | Periksa `storage:link` dan berkas di `storage/app/public/books` |
| Login demo gagal | Pastikan database yang digunakan memang telah di-seed dan gunakan NIS/email yang benar |
| API scanner 401 | Periksa Bearer token; seeder atau regenerasi mungkin telah mencabut token lama |
| NIS kios tidak ditemukan | Akun belum ada atau NIS tidak cocok |
| Stok melonjak setelah impor | ISBN yang sudah ada ditambah stoknya setiap kali diimpor |

Perubahan dokumentasi ini tidak mengubah kode aplikasi. Tes aplikasi tidak dijalankan pada pembaruan dokumentasi ini. Riwayat diagnosis dan batas verifikasi runtime dijelaskan dalam [panduan menjalankan web](PANDUAN-MENJALANKAN.md).
