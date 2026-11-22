# Panduan demo web Perpus Dupat dan scanner ISBN di HP

Panduan ini untuk menjalankan backend Laravel di laptop Windows, membuka web, menjalankan aplikasi Flutter di HP Android (contoh: realme C55), lalu mengirim buku hasil scan ke inventaris web. Folder yang digunakan di komputer ini adalah `D:\perpus24\perpusdupat` untuk web dan `D:\perpus24\isbn_scanner` untuk Flutter. Jika folder di komputer lain berbeda, sesuaikan perintahnya.

## 1. Persiapan sekali pada instalasi baru

Pastikan PHP 8.3, Composer, Node.js/npm, Flutter SDK, Android SDK, dan Android SDK Platform-Tools sudah terpasang. Periksa di PowerShell:

```powershell
php -v
composer --version
node -v
npm -v
flutter --version
adb version
```

Di komputer ini, jika `adb` belum masuk PATH, gunakan `D:\android\sdk\platform-tools\adb.exe` sebagai pengganti `adb` di semua perintah. Jika PHP/Composer belum dikenali, selesaikan pengaturan Laravel Herd dan buka ulang terminal. Rincian instalasi awal serta kendala PHP dan Windows Security ada di [Panduan Menjalankan](PANDUAN-MENJALANKAN.md).

Siapkan web pada database baru saja:

```powershell
cd D:\perpus24\perpusdupat
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite }
```

Buka `.env` dan pastikan `DB_CONNECTION=sqlite`, lalu lanjutkan:

```powershell
if (Select-String -Path .env -Pattern '^APP_KEY=$' -Quiet) { php artisan key:generate --no-interaction }
php artisan migrate --no-interaction
```

Pada database baru, jalankan **sekali**:

```powershell
php artisan db:seed --no-interaction
```

Terakhir, siapkan sampul dan aset web:

```powershell
php artisan storage:link --no-interaction
npm ci --ignore-scripts
npm run build
```

Mengulang `db:seed` menambah data contoh dan mengganti token scanner. Untuk laptop yang sudah pernah berhasil menjalankan web, lewati seluruh blok instalasi ini.

Siapkan Flutter sekali:

```powershell
cd D:\perpus24\isbn_scanner
flutter pub get
flutter doctor
```

## 2. Persiapan setiap kali demo

1. Sambungkan laptop dan HP ke Wi-Fi yang sama. Matikan VPN jika membuat kedua perangkat tidak bisa saling mengakses. Catat bahwa IP laptop dapat berubah setelah pindah Wi-Fi atau hotspot.
2. Di laptop, jalankan `ipconfig` dan cari **IPv4 Address** pada adaptor Wi-Fi yang sedang dipakai, misalnya `192.168.1.112`. Jangan gunakan alamat adaptor virtual, Bluetooth, atau `127.0.0.1` sebagai alamat laptop untuk HP.
3. Buka PowerShell **terminal 1**, lalu jalankan:

   ```powershell
   cd D:\perpus24\perpusdupat
   php artisan serve --host=0.0.0.0 --port=8000 --no-interaction
   ```

   Biarkan terminal ini terbuka selama demo. `0.0.0.0` berarti server mendengarkan koneksi dari jaringan; **alamat itu bukan alamat yang dibuka di browser**.
4. Di browser laptop buka `http://127.0.0.1:8000`. Di browser HP buka `http://<IP-LAPTOP>:8000`, misalnya `http://192.168.1.112:8000`. Jika halaman login terbuka dari HP, jalur jaringan ke Laravel sudah benar. Jika browser HP tidak bisa membuka web, selesaikan masalah jaringan/firewall sebelum mencoba scan.
5. Login web sebagai pustakawan. Pada database demo hasil seeder, NIS/NIP `admin` dan password `password`. Buka menu **Buku/Katalog**, sehingga penambahan buku bisa segera diperiksa. Akun ini hanya untuk demo lokal.

Jika tampilan web kehilangan CSS/JS, dari terminal lain di folder web jalankan `npm run build` lalu segarkan halaman. Perintah itu tidak perlu diulang bila aset sudah tampil normal.

## 3. Jalankan aplikasi Flutter di realme C55 tanpa kabel

**Jika aplikasi sudah terpasang di HP**, cukup buka ikonnya; koneksi ADB dan `flutter run` tidak diperlukan untuk memakai aplikasi. Laptop tetap harus menjalankan server Laravel pada langkah 2.

Untuk memasang atau menjalankan ulang aplikasi dari source secara nirkabel, HP dan laptop harus berada pada Wi-Fi yang sama:

1. Di realme C55, buka **Pengaturan → Tentang perangkat → Versi**, ketuk **Nomor bentukan/Build number** beberapa kali hingga opsi pengembang aktif. Nama menu bisa sedikit berbeda menurut versi realme UI.
2. Buka **Pengaturan → Pengaturan tambahan → Opsi pengembang → Debugging nirkabel**, lalu aktifkan. Pilih **Sandingkan perangkat dengan kode penyandingan**. Biarkan layar kode dan alamat IP:port pairing terlihat.
3. Di PowerShell laptop, jalankan `adb pair 'IP-HP:PORT-PAIRING'` setelah mengganti teks di dalam tanda kutip dengan alamat yang terlihat di HP, lalu masukkan kode enam digit dari HP saat diminta.
4. Kembali ke halaman **Debugging nirkabel** di HP. Ambil alamat **IP address & Port** untuk debugging (biasanya port-nya **berbeda** dari port pairing), lalu jalankan `adb connect 'IP-HP:PORT-DEBUGGING'` dengan alamat debugging yang terlihat di HP.
5. Periksa dan jalankan Flutter dari terminal **kedua**:

   ```powershell
   cd D:\perpus24\isbn_scanner
   flutter devices
   flutter run -d 'ID-PERANGKAT'
   ```

   Ganti `<ID-PERANGKAT>` dengan ID yang ditampilkan `flutter devices`. Beri izin kamera ketika aplikasi meminta. Terminal `flutter run` tetap hidup selama sesi debug. Jika aplikasi sudah terpasang dan tidak ada perubahan kode, pada demo berikutnya cukup buka aplikasi di HP.

Port ADB pairing/debugging dipakai untuk memasang atau men-debug aplikasi, **bukan** port API. Aplikasi tetap mengirim data ke port Laravel `8000` di IP laptop. Jika HP tidak muncul di `flutter devices`, periksa kembali Wi-Fi, izin debugging nirkabel, dan status `adb devices`; ulangi `adb connect` memakai port debugging yang sedang tampil di HP.

## 4. Atur tujuan API dan token scanner

Di aplikasi Flutter, buka tab **API Config**. Isi:

| Kolom | Isi untuk HP fisik |
| --- | --- |
| Target API Endpoint URL | `http://<IP-LAPTOP>:8000/api/v1/books/isbn-scan`, contoh `http://192.168.1.112:8000/api/v1/books/isbn-scan` |
| Metode HTTP | `POST` |
| Token Otorisasi (Bearer) | Token scanner Laravel yang **masih aktif**; boleh tempel token mentah karena aplikasi menambahkan `Bearer ` otomatis |
| ID Kategori | ID numerik yang benar-benar ada di tabel `categories` (pada database demo baru, nilai bawaan `8` biasanya tersedia) |
| Lokasi Rak | Contoh `Rak-A1` |
| Stok | Jumlah eksemplar yang benar-benar akan dimasukkan, misalnya `1` |
| Payload Wrapper Key | Kosongkan |

Tekan **Simpan Pengaturan**. Jangan gunakan preset `127.0.0.1` pada HP fisik: itu menunjuk ke HP sendiri. Alamat `10.0.2.2` khusus emulator Android. Jangan memasukkan `0.0.0.0` sebagai endpoint. Google Books API Key bersifat opsional untuk pencarian metadata, bukan token autentikasi Laravel.

Jika belum punya token aktif atau muncul `401 Unauthenticated`, buka **terminal ketiga** di laptop, jangan hentikan server, lalu jalankan:

```powershell
cd D:\perpus24\perpusdupat
php artisan library:token --regenerate --no-interaction
```

Salin token baru yang tampil di terminal ke kolom **Token Otorisasi**, lalu tekan **Simpan Pengaturan**. Token hanya tampil saat dibuat; perintah ini **menghapus token scanner sebelumnya**, sehingga konfigurasi lama di HP lain ikut tidak berlaku. Jangan kirim token atau tangkapan layarnya ke orang lain, dokumen, atau GitHub. Jika perintah menyebut pengguna `SCANNER-DEVICE-01` tidak ada, instalasi database belum lengkap; lihat langkah seeder pada bagian 1 dan pastikan memakai database yang benar. Nilai token bawaan dalam source Flutter mungkin sudah kedaluwarsa, jadi jangan mengandalkannya.

**Jangan tekan “Uji Koneksi” untuk sekadar mengecek jaringan.** Pada versi aplikasi saat ini tombol tersebut melakukan `POST` buku contoh *Bumi Manusia* ke API dan dapat menambah stok inventaris. Uji jaringan cukup dengan membuka web dari browser HP; uji kirim lakukan dengan buku yang memang akan diinventarisasi.

## 5. Scan dan kirim buku sampai muncul di web

1. Di Flutter pilih **Scan**, arahkan kamera ke barcode ISBN. Aplikasi mencari metadata buku dari layanan online; jika judul/penulis tidak terisi atau keliru, isi atau koreksi secara manual di layar **Review Data Buku**. Pencarian otomatis memerlukan internet, sedangkan kirim ke server lokal memerlukan koneksi HP–laptop.
2. Periksa ISBN, judul, penulis, tahun, **ID kategori**, rak, dan stok. ISBN dan judul wajib; stok harus 1–1000. ID kategori adalah nomor baris kategori di database, **bukan** kode klasifikasi DDC seperti `800`.
3. Tekan **Konfirmasi & Kirim ke Server**. Tunggu dialog berhasil. Jika muncul `book_created` atau HTTP `201`, ISBN baru masuk; jika `stock_incremented` atau HTTP `200`, ISBN sudah ada dan stoknya bertambah. Tombol **Simpan Sebagai Draft Lokal** hanya menyimpan di HP dan **tidak** mengirim ke web.
4. Di browser laptop, segarkan halaman **Buku/Katalog**, cari judul atau ISBN yang sama, lalu pastikan jumlah stok sesuai. Riwayat lokal Flutter dapat menunjukkan status pengiriman, tetapi pemeriksaan di web memastikan data server benar-benar berubah.

Pengiriman ulang ISBN yang sama **menambah stok lagi**. Jika aplikasi mengalami timeout atau Anda ragu respons berhasil, periksa buku dan stok di web **sebelum** menekan **Coba Lagi** agar stok tidak bertambah dua kali.

## 6. Jika pengiriman gagal

| Gejala | Arti dan langkah pemeriksaan |
| --- | --- |
| `Connection refused` dengan endpoint `127.0.0.1` | HP mencoba menghubungi dirinya sendiri. Ganti endpoint dengan IP Wi-Fi laptop, simpan, lalu coba lagi. |
| `Connection refused` atau timeout pada IP laptop | Pastikan terminal `artisan serve` masih menampilkan server aktif, HP dan laptop satu jaringan, IP/port masih benar, serta web bisa dibuka dari browser HP. Bila tetap gagal, izinkan koneksi masuk untuk PHP atau port 8000 pada **jaringan privat** di Windows Firewall. Jangan menonaktifkan seluruh firewall. |
| `401 Unauthenticated` | Server sudah terjangkau, tetapi token kosong/salah/kedaluwarsa. Buat token baru dengan `library:token --regenerate`, tempel ke **API Config**, dan simpan. Jika `db:seed` dijalankan lagi, token lama tidak berlaku. |
| `404 Not Found` | Periksa path lengkap `/api/v1/books/isbn-scan` dan metode `POST`; jangan memakai URL halaman web sebagai endpoint API. |
| `405 Method Not Allowed` | Ubah metode menjadi `POST`. |
| `422` validasi | Baca `errors` pada respons. Periksa ISBN, judul, stok 1–1000, tahun, dan `category_id` yang harus ada di database. `ID Kategori` berbeda dari kode DDC. |
| Buku tersimpan sebagai draft tetapi tak muncul di web | Draft hanya lokal. Buka lagi datanya dan tekan **Konfirmasi & Kirim ke Server** setelah API siap. |
| Scan berhasil, metadata tidak muncul | Layanan pencarian buku memerlukan internet dan mungkin tidak punya data ISBN tersebut. Isi metadata manual lalu kirim; koneksi LAN ke server tetap harus aktif. |

Untuk membedakan masalah jaringan dari token: browser HP berhasil membuka halaman login berarti laptop dapat dijangkau; `401` berarti permintaan sudah sampai ke Laravel namun autentikasinya gagal. **Jangan menempelkan URL API pada browser untuk menguji POST**: browser biasa mengirim GET, sedangkan route ini menerima POST bertoken.

## 7. Mengakhiri dan mengulangi demo

Saat selesai, tekan **Ctrl+C** pada terminal `flutter run` (jika dipakai) dan terminal `php artisan serve`. Data buku yang sudah berhasil dikirim tetap ada di SQLite laptop. Saat membuka laptop lagi, cukup sambungkan kedua perangkat ke Wi-Fi, cek IP laptop, jalankan server pada langkah 2, buka aplikasi HP, dan perbarui endpoint jika IP berubah. Instalasi Composer/npm, migrasi, dan seeder **tidak perlu diulang** untuk penggunaan harian.
