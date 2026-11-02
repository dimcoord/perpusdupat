<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. DDC Categories
        $categoriesData = [
            ['code' => '000', 'name' => 'Karya Umum & Komputer'],
            ['code' => '100', 'name' => 'Filsafat & Psikologi'],
            ['code' => '200', 'name' => 'Agama & Kepercayaan'],
            ['code' => '300', 'name' => 'Ilmu Sosial & Pendidikan'],
            ['code' => '400', 'name' => 'Bahasa & Linguistik'],
            ['code' => '500', 'name' => 'Sains Murni & Matematika'],
            ['code' => '600', 'name' => 'Teknologi & Ilmu Terapan'],
            ['code' => '700', 'name' => 'Kesenian & Rekreasi'],
            ['code' => '800', 'name' => 'Sastra & Fiksi'],
            ['code' => '900', 'name' => 'Sejarah & Geografi'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['code']] = Category::firstOrCreate(['code' => $cat['code']], $cat);
        }

        // 2. Default Librarian Account
        $librarian = User::firstOrCreate(
            ['nis_nip' => '198501012010011001'],
            [
                'name' => 'Ibu Sri Wahyuni, S.I.Pust.',
                'email' => 'pustakawan@perpusdupat.sch.id',
                'password' => Hash::make('password'),
                'role' => 'pustakawan',
                'phone' => '081234567890',
            ]
        );

        // Also create a quick-login username for convenience
        User::firstOrCreate(
            ['nis_nip' => 'admin'],
            [
                'name' => 'Administrator Perpustakaan',
                'email' => 'admin@perpusdupat.sch.id',
                'password' => Hash::make('password'),
                'role' => 'pustakawan',
                'phone' => '081234567891',
            ]
        );

        // 3. Dedicated Scanner Device Service User for Mobile App Node
        $scannerDevice = User::firstOrCreate(
            ['nis_nip' => 'SCANNER-DEVICE-01'],
            [
                'name' => 'scanner-device',
                'email' => 'scanner@lan.local',
                'password' => Hash::make('ScannerSecretPass2026!'),
                'role' => 'pustakawan',
                'phone' => null,
            ]
        );

        // Delete previous tokens and issue a fixed known or persistent token
        $scannerDevice->tokens()->delete();
        $token = $scannerDevice->createToken('mobile-scanner-token');

        // 4. Default Student Accounts
        $studentAhmad = User::firstOrCreate(
            ['nis_nip' => '2024101'],
            [
                'name' => 'Ahmad Pratama',
                'email' => 'ahmad@siswa.perpusdupat.sch.id',
                'password' => Hash::make('password'),
                'role' => 'murid',
                'phone' => '082199887766',
            ]
        );

        $studentSiti = User::firstOrCreate(
            ['nis_nip' => '2024102'],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@siswa.perpusdupat.sch.id',
                'password' => Hash::make('password'),
                'role' => 'murid',
                'phone' => '082199887767',
            ]
        );

        $studentBudi = User::firstOrCreate(
            ['nis_nip' => '2024103'],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@siswa.perpusdupat.sch.id',
                'password' => Hash::make('password'),
                'role' => 'murid',
                'phone' => '082199887768',
            ]
        );

        // 5. Initial Books Catalog
        $booksData = [
            [
                'isbn' => '9786020332956',
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'publisher' => 'Hasta Mitra / Gramedia',
                'publication_year' => 2005,
                'category_id' => $categories['800']->id,
                'rack_location' => 'Rak-A1',
                'total_stock' => 5,
                'available_stock' => 4,
                'cover_image' => $this->generateCoverSvg('cover_bumi_manusia.svg', 'Bumi Manusia', 'Pramoedya Ananta Toer', '800', '#78350f', '#451a03'),
            ],
            [
                'isbn' => '9789792219081',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'publication_year' => 2005,
                'category_id' => $categories['800']->id,
                'rack_location' => 'Rak-A2',
                'total_stock' => 6,
                'available_stock' => 5,
                'cover_image' => $this->generateCoverSvg('cover_laskar_pelangi.svg', 'Laskar Pelangi', 'Andrea Hirata', '800', '#1e1b4b', '#0891b2'),
            ],
            [
                'isbn' => '9786020822600',
                'title' => 'Ayat-Ayat Cinta',
                'author' => 'Habiburrahman El Shirazy',
                'publisher' => 'Republika',
                'publication_year' => 2008,
                'category_id' => $categories['800']->id,
                'rack_location' => 'Rak-A3',
                'total_stock' => 4,
                'available_stock' => 4,
                'cover_image' => $this->generateCoverSvg('cover_ayat_ayat_cinta.svg', 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', '800', '#064e3b', '#0d9488'),
            ],
            [
                'isbn' => '9789793062792',
                'title' => 'Pengantar Algoritma dan Pemrograman',
                'author' => 'Rinaldi Munir',
                'publisher' => 'Informatika Bandung',
                'publication_year' => 2016,
                'category_id' => $categories['000']->id,
                'rack_location' => 'Rak-B1',
                'total_stock' => 8,
                'available_stock' => 7,
                'cover_image' => $this->generateCoverSvg('cover_algoritma.svg', 'Pengantar Algoritma', 'Rinaldi Munir', '000', '#0f172a', '#0284c7'),
            ],
            [
                'isbn' => '9786024444587',
                'title' => 'Fisika Dasar untuk Sains dan Teknik',
                'author' => 'Halliday & Resnick',
                'publisher' => 'Erlangga',
                'publication_year' => 2018,
                'category_id' => $categories['500']->id,
                'rack_location' => 'Rak-C1',
                'total_stock' => 5,
                'available_stock' => 5,
                'cover_image' => $this->generateCoverSvg('cover_fisika.svg', 'Fisika Dasar Sains', 'Halliday & Resnick', '500', '#3b0764', '#7c3aed'),
            ],
            [
                'isbn' => '9789790615564',
                'title' => 'Pengantar Akuntansi Dasar',
                'author' => 'Warren & Reeve',
                'publisher' => 'Salemba Empat',
                'publication_year' => 2017,
                'category_id' => $categories['300']->id,
                'rack_location' => 'Rak-D2',
                'total_stock' => 3,
                'available_stock' => 3,
                'cover_image' => $this->generateCoverSvg('cover_akuntansi.svg', 'Pengantar Akuntansi', 'Warren & Reeve', '300', '#172554', '#2563eb'),
            ],
            [
                'isbn' => '9786022912828',
                'title' => 'Negeri 5 Menara',
                'author' => 'Ahmad Fuadi',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2009,
                'category_id' => $categories['800']->id,
                'rack_location' => 'Rak-A4',
                'total_stock' => 4,
                'available_stock' => 4,
                'cover_image' => $this->generateCoverSvg('cover_negeri_5_menara.svg', 'Negeri 5 Menara', 'Ahmad Fuadi', '800', '#14532d', '#16a34a'),
            ],
            [
                'isbn' => '9789791684781',
                'title' => 'Sejarah Indonesia Modern 1200-2008',
                'author' => 'M.C. Ricklefs',
                'publisher' => 'Serambi Ilmu Semesta',
                'publication_year' => 2008,
                'category_id' => $categories['900']->id,
                'rack_location' => 'Rak-E1',
                'total_stock' => 3,
                'available_stock' => 3,
                'cover_image' => $this->generateCoverSvg('cover_sejarah_indonesia.svg', 'Sejarah Indonesia Modern', 'M.C. Ricklefs', '900', '#422006', '#854d0e'),
            ],
        ];

        $createdBooks = [];
        foreach ($booksData as $b) {
            $createdBooks[] = Book::firstOrCreate(['isbn' => $b['isbn']], $b);
        }

        // 6. Initial Visits
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        Visit::create([
            'user_id' => $studentAhmad->id,
            'visit_date' => $today,
            'purpose' => 'Membaca',
            'notes' => 'Membaca buku sastra untuk tugas bahasa Indonesia',
        ]);

        Visit::create([
            'user_id' => $studentSiti->id,
            'visit_date' => $today,
            'purpose' => 'Meminjam',
            'notes' => 'Mencari buku referensi fisika dasar',
        ]);

        Visit::create([
            'user_id' => $studentBudi->id,
            'visit_date' => $yesterday,
            'purpose' => 'Tugas',
            'notes' => 'Mengerjakan tugas kelompok sejarah',
        ]);

        // 7. Initial Loans
        // Loan 1: Active borrowed by Ahmad
        Loan::create([
            'user_id' => $studentAhmad->id,
            'book_id' => $createdBooks[0]->id, // Bumi Manusia
            'loan_date' => Carbon::today()->subDays(2)->toDateString(),
            'due_date' => Carbon::today()->addDays(5)->toDateString(),
            'status' => 'dipinjam',
        ]);

        // Loan 2: Pending approval by Siti
        Loan::create([
            'user_id' => $studentSiti->id,
            'book_id' => $createdBooks[1]->id, // Laskar Pelangi
            'loan_date' => Carbon::today()->toDateString(),
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
            'status' => 'menunggu_persetujuan',
        ]);

        // Loan 3: Overdue by Budi
        Loan::create([
            'user_id' => $studentBudi->id,
            'book_id' => $createdBooks[3]->id, // Pemrograman
            'loan_date' => Carbon::today()->subDays(12)->toDateString(),
            'due_date' => Carbon::today()->subDays(5)->toDateString(),
            'status' => 'terlambat',
            'librarian_notes' => 'Terlambat 5 hari dari batas pengembalian',
        ]);

        // Output information to console
        $this->command?->info('===========================================================');
        $this->command?->info('SISTEM PERPUSTAKAAN DIGITAL (PERPUS DUPAT) INITIALIZED');
        $this->command?->info('===========================================================');
        $this->command?->info('Akun Pustakawan:');
        $this->command?->info(' - NIS/NIP: 198501012010011001 (atau: admin)');
        $this->command?->info(' - Password: password');
        $this->command?->info('Akun Siswa:');
        $this->command?->info(' - NIS: 2024101 (Ahmad Pratama) | Password: password');
        $this->command?->info(' - NIS: 2024102 (Siti Nurhaliza)| Password: password');
        $this->command?->info(' - NIS: 2024103 (Budi Santoso)  | Password: password');
        $this->command?->info('Mobile Scanner Node (REST API):');
        $this->command?->info(' - User: scanner-device (SCANNER-DEVICE-01)');
        $this->command?->info(' - Sanctum Bearer Token: '.$token->plainTextToken);
        $this->command?->info('===========================================================');
    }

    /**
     * Generate an elegant vector SVG book cover for offline LAN use.
     */
    private function generateCoverSvg(string $filename, string $title, string $author, string $categoryCode, string $colorStart, string $colorEnd): string
    {
        $dir = storage_path('app/public/books');
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeAuthor = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');
        $safeCategory = htmlspecialchars($categoryCode, ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 420" width="300" height="420">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$colorStart};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$colorEnd};stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="300" height="420" fill="url(#grad)" rx="8" />
    <rect x="14" y="14" width="272" height="392" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.5" rx="6" />

    <!-- Spine shadow -->
    <rect x="0" y="0" width="16" height="420" fill="rgba(0,0,0,0.3)" />
    <line x1="16" y1="0" x2="16" y2="420" stroke="rgba(255,255,255,0.15)" stroke-width="1" />

    <!-- Category Pill -->
    <rect x="32" y="32" width="76" height="22" rx="4" fill="rgba(255,255,255,0.2)" />
    <text x="70" y="47" font-family="system-ui, -apple-system, sans-serif" font-size="10" font-weight="bold" fill="#ffffff" text-anchor="middle">DDC {$safeCategory}</text>

    <!-- Center Icon -->
    <circle cx="150" cy="135" r="32" fill="rgba(255,255,255,0.12)" />
    <path d="M 134 138 Q 150 130 166 138 L 166 150 Q 150 142 134 150 Z" fill="none" stroke="#ffffff" stroke-width="2.2" />
    <path d="M 134 126 Q 150 118 166 126 L 166 138 Q 150 130 134 138 Z" fill="none" stroke="#ffffff" stroke-width="2.2" />
    <line x1="150" y1="122" x2="150" y2="147" stroke="#ffffff" stroke-width="2" />

    <!-- Title -->
    <foreignObject x="28" y="195" width="244" height="110">
        <div xmlns="http://www.w3.org/1999/xhtml" style="color:#ffffff; font-family:system-ui, -apple-system, sans-serif; font-size:18px; font-weight:800; text-align:center; line-height:1.25; text-shadow:0 2px 4px rgba(0,0,0,0.6);">
            {$safeTitle}
        </div>
    </foreignObject>

    <!-- Author & Footer -->
    <text x="150" y="335" font-family="system-ui, -apple-system, sans-serif" font-size="12" font-weight="600" fill="rgba(255,255,255,0.9)" text-anchor="middle">{$safeAuthor}</text>
    <text x="150" y="385" font-family="system-ui, -apple-system, sans-serif" font-size="9" font-weight="bold" letter-spacing="1.5" fill="rgba(255,255,255,0.65)" text-anchor="middle">PERPUS DUPAT</text>
</svg>
SVG;

        file_put_contents("{$dir}/{$filename}", $svg);

        return "books/{$filename}";
    }
}
