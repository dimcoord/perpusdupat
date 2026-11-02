<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Rap2hpoutre\FastExcel\FastExcel;

class ExcelReportService
{
    /**
     * Generate print-ready visit report (A4 Landscape/Portrait fit).
     */
    public function generateVisitReport(?string $startDate = null, ?string $endDate = null): string
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?: Carbon::now()->toDateString();

        $visits = Visit::with('user')
            ->dateRange($startDate, $endDate)
            ->orderBy('visit_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Kunjungan');

        // Page setup for printing
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);

        // Header: Kop Sekolah
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'PEMERINTAH DAERAH PROVINSI - DINAS PENDIDIKAN');
        $sheet->getStyle('A1')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'PERPUSTAKAAN DIGITAL SMAN / SMKN DUPAT');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Alamat: Jl. Pendidikan No. 4, Jaringan LAN Terpadu | Telp: (021) 8876543');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Divider border
        $sheet->getStyle('A3:G3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // Report Title
        $sheet->mergeCells('A5:G5');
        $sheet->setCellValue('A5', 'LAPORAN REKAPITULASI KUNJUNGAN PERPUSTAKAAN');
        $sheet->getStyle('A5')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A6:G6');
        $periodeStr = Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y');
        $sheet->setCellValue('A6', "Periode: {$periodeStr}");
        $sheet->getStyle('A6')->getFont()->setSize(10);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header
        $headers = ['No', 'Tanggal', 'NIS / NIP', 'Nama Pengunjung', 'Peran', 'Tujuan', 'Catatan'];
        $row = 8;
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue("{$col}{$row}", $header);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Deep Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Content Table Rows
        $row++;
        $no = 1;
        $startDataRow = $row;

        foreach ($visits as $visit) {
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", Carbon::parse($visit->visit_date)->format('d/m/Y'));
            $sheet->setCellValueExplicit("C{$row}", (string) ($visit->user?->nis_nip ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $visit->user?->name ?? 'Tamu');
            $sheet->setCellValue("E{$row}", ucfirst($visit->user?->role ?? '-'));
            $sheet->setCellValue("F{$row}", $visit->purpose);
            $sheet->setCellValue("G{$row}", $visit->notes ?? '-');

            // Zebra styling
            if ($no % 2 === 0) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
            $no++;
        }

        $endDataRow = max($row - 1, $startDataRow);

        // Borders and alignment for data cells
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A{$startDataRow}:G{$endDataRow}")->applyFromArray($dataBorderStyle);
        $sheet->getStyle("A{$startDataRow}:A{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B{$startDataRow}:B{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$startDataRow}:C{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E{$startDataRow}:E{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Total Row
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL KUNJUNGAN');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("F{$row}", count($visits).' Siswa/Pengunjung');
        $sheet->getStyle("F{$row}")->getFont()->setBold(true);
        $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
        ]);
        
        $row += 3;

        // Footer: Signatures
        $reportDateStr = Carbon::now()->translatedFormat('d F Y');
        $sheet->setCellValue("B{$row}", 'Mengetahui,');
        $sheet->setCellValue("F{$row}", "Ditetapkan di: Sekolah\nPada tanggal: {$reportDateStr}");
        $row++;
        $sheet->setCellValue("B{$row}", 'Kepala Sekolah,');
        $sheet->setCellValue("F{$row}", 'Kepala Perpustakaan,');

        $row += 4;
        $sheet->setCellValue("B{$row}", '_________________________');
        $sheet->setCellValue("F{$row}", '_________________________');
        $row++;
        $sheet->setCellValue("B{$row}", 'NIP. ....................................');
        $sheet->setCellValue("F{$row}", 'NIP. ....................................');

        $sheet->getStyle('B'.($row - 6).":B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F'.($row - 6).":F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto width
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = storage_path('app/temp_laporan_kunjungan_'.uniqid().'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Generate print-ready loan report (A4 Landscape fit).
     */
    public function generateLoanReport(?string $status = null, ?string $startDate = null, ?string $endDate = null): string
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?: Carbon::now()->toDateString();

        $query = Loan::with(['user', 'book'])
            ->whereDate('loan_date', '>=', $startDate)
            ->whereDate('loan_date', '<=', $endDate);

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        $loans = $query->orderBy('loan_date', 'asc')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Peminjaman');

        // Page setup for printing
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);

        // Header: Kop Sekolah
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'PEMERINTAH DAERAH PROVINSI - DINAS PENDIDIKAN');
        $sheet->getStyle('A1')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'PERPUSTAKAAN DIGITAL SMAN / SMKN DUPAT');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', 'Alamat: Jl. Pendidikan No. 4, Jaringan LAN Terpadu | Telp: (021) 8876543');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Divider
        $sheet->getStyle('A3:J3')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // Report Title
        $sheet->mergeCells('A5:J5');
        $sheet->setCellValue('A5', 'LAPORAN SIRKULASI & PEMINJAMAN BUKU');
        $sheet->getStyle('A5')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A6:J6');
        $periodeStr = Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y');
        $statusStr = $status ? ' | Status: '.strtoupper(str_replace('_', ' ', $status)) : '';
        $sheet->setCellValue('A6', "Periode: {$periodeStr}{$statusStr}");
        $sheet->getStyle('A6')->getFont()->setSize(10);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header (A - J)
        $headers = ['No', 'ID Pinjam', 'NIS', 'Nama Peminjam', 'Judul Buku', 'ISBN', 'Tgl Pinjam', 'Jatuh Tempo', 'Tgl Kembali', 'Status'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $row = 8;

        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue("{$col}{$row}", $header);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'], // Deep Teal
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Data Rows
        $row++;
        $no = 1;
        $startDataRow = $row;

        foreach ($loans as $loan) {

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", '#LN-'.str_pad($loan->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValueExplicit("C{$row}", (string) ($loan->user?->nis_nip ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $loan->user?->name ?? '-');
            $sheet->setCellValue("E{$row}", $loan->book?->title ?? '-');
            $sheet->setCellValueExplicit("F{$row}", (string) ($loan->book?->isbn ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", Carbon::parse($loan->loan_date)->format('d/m/Y'));
            $sheet->setCellValue("H{$row}", Carbon::parse($loan->due_date)->format('d/m/Y'));
            $sheet->setCellValue("I{$row}", $loan->return_date ? Carbon::parse($loan->return_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("J{$row}", strtoupper(str_replace('_', ' ', $loan->status)));

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
            $no++;
        }

        $endDataRow = max($row - 1, $startDataRow);

        // Data styling
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A{$startDataRow}:J{$endDataRow}")->applyFromArray($dataBorderStyle);
        $sheet->getStyle("A{$startDataRow}:C{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F{$startDataRow}:J{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Spacing 2 row kosong setelah tabel sebelum tanda tangan
        $row += 3;

        // Footer signatures
        $reportDateStr = Carbon::now()->translatedFormat('d F Y');
        $sheet->setCellValue("C{$row}", 'Mengetahui,');
        $sheet->setCellValue("I{$row}", "Ditetapkan di: Sekolah\nPada tanggal: {$reportDateStr}");
        $row++;
        $sheet->setCellValue("C{$row}", 'Kepala Sekolah,');
        $sheet->setCellValue("I{$row}", 'Kepala Perpustakaan,');

        $row += 4;
        $sheet->setCellValue("C{$row}", '_________________________');
        $sheet->setCellValue("I{$row}", '_________________________');
        $row++;
        $sheet->setCellValue("C{$row}", 'NIP. ....................................');
        $sheet->setCellValue("I{$row}", 'NIP. ....................................');

        $sheet->getStyle('C'.($row - 6).":C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I'.($row - 6).":I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto width
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = storage_path('app/temp_laporan_peminjaman_'.uniqid().'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Generate template for book imports (books_import_template.xlsx).
     * Strict columns: [ISBN, Judul, Penulis, Penerbit, Tahun, Kode_Kategori, Lokasi_Rak, Jumlah]
     */
    public function generateImportTemplate(): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Buku');

        $headers = ['ISBN', 'Judul', 'Penulis', 'Penerbit', 'Tahun', 'Kode_Kategori', 'Lokasi_Rak', 'Jumlah'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue("{$cols[$index]}1", $header);
        }

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Sample rows to demonstrate formatting
        $sampleData = [
            ['9786020332956', 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 2005, '813', 'Rak-A1', 5],
            ['9789792219081', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '813', 'Rak-A2', 3],
            ['9786020822600', 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 2008, '813', 'Rak-B1', 4],
        ];

        $row = 2;
        foreach ($sampleData as $item) {
            foreach ($item as $colIdx => $val) {
                if ($colIdx === 0 || $colIdx === 5) {
                    $sheet->setCellValueExplicit("{$cols[$colIdx]}{$row}", (string) $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue("{$cols[$colIdx]}{$row}", $val);
                }
            }
            $row++;
        }

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = storage_path('app/books_import_template.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Chunked streaming book catalog import with inline error handling.
     * Prevents out-of-memory errors on 4GB RAM server.
     *
     * @return array{imported: int, updated: int, failed: int, errors: list<array{row: int, isbn: string, message: string}>}
     */
    public function importBooksChunked(string $filePath): array
    {
        $importedCount = 0;
        $updatedCount = 0;
        $failedCount = 0;
        $errors = [];

        // Preload categories keyed by code and name (lowercase) to avoid per-row DB lookups
        $categories = Category::all()->keyBy(fn ($c) => strtolower($c->code));
        $categoryFallback = Category::firstOrCreate(['code' => '000'], ['name' => 'Karya Umum / Umum']);

        $rowIndex = 1;

        // Use FastExcel for stream-reading without loading whole sheet to RAM
        (new FastExcel)->import($filePath, function ($line) use (
            &$importedCount,
            &$updatedCount,
            &$failedCount,
            &$errors,
            &$rowIndex,
            &$categories,
            $categoryFallback
        ) {
            $rowIndex++;

            // Handle potential variation in header casing
            $isbn = trim((string) ($line['ISBN'] ?? $line['isbn'] ?? ''));
            $title = trim((string) ($line['Judul'] ?? $line['judul'] ?? $line['Title'] ?? ''));
            $author = trim((string) ($line['Penulis'] ?? $line['penulis'] ?? $line['Author'] ?? ''));
            $publisher = trim((string) ($line['Penerbit'] ?? $line['penerbit'] ?? $line['Publisher'] ?? ''));
            $year = (int) ($line['Tahun'] ?? $line['tahun'] ?? $line['Year'] ?? 0);
            $categoryCode = strtolower(trim((string) ($line['Kode_Kategori'] ?? $line['kode_kategori'] ?? $line['Category'] ?? '')));
            $rackLocation = trim((string) ($line['Lokasi_Rak'] ?? $line['lokasi_rak'] ?? $line['Rack'] ?? ''));
            $quantity = (int) ($line['Jumlah'] ?? $line['jumlah'] ?? $line['Qty'] ?? $line['Stock'] ?? 1);

            // Validation checks
            if (empty($isbn)) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'isbn' => '-',
                    'message' => 'Kolom ISBN tidak boleh kosong.',
                ];

                return;
            }

            if (empty($title)) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'isbn' => $isbn,
                    'message' => "Buku dengan ISBN {$isbn} tidak memiliki Judul.",
                ];

                return;
            }

            if ($quantity < 0) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'isbn' => $isbn,
                    'message' => 'Jumlah stok tidak boleh negatif.',
                ];

                return;
            }

            // Find or associate category
            $category = $categories->get($categoryCode);
            if (! $category) {
                if (! empty($categoryCode)) {
                    $category = Category::create([
                        'code' => strtoupper($categoryCode),
                        'name' => 'Kategori '.strtoupper($categoryCode),
                    ]);
                    $categories->put($categoryCode, $category);
                } else {
                    $category = $categoryFallback;
                }
            }

            // Database insertion/update in transaction
            try {
                DB::transaction(function () use (
                    $isbn,
                    $title,
                    $author,
                    $publisher,
                    $year,
                    $category,
                    $rackLocation,
                    $quantity,
                    &$importedCount,
                    &$updatedCount
                ) {
                    $existing = Book::where('isbn', $isbn)->lockForUpdate()->first();

                    if ($existing) {
                        $existing->total_stock += $quantity;
                        $existing->available_stock += $quantity;
                        $existing->title = $title ?: $existing->title;
                        $existing->author = $author ?: $existing->author;
                        $existing->publisher = $publisher ?: $existing->publisher;
                        if ($year > 0) {
                            $existing->publication_year = $year;
                        }
                        if (! empty($rackLocation)) {
                            $existing->rack_location = $rackLocation;
                        }
                        $existing->category_id = $category->id;
                        $existing->save();

                        $updatedCount++;
                    } else {
                        Book::create([
                            'isbn' => $isbn,
                            'title' => $title,
                            'author' => $author ?: 'Anonim',
                            'publisher' => $publisher ?: '-',
                            'publication_year' => $year > 0 ? $year : date('Y'),
                            'category_id' => $category->id,
                            'rack_location' => $rackLocation ?: '-',
                            'total_stock' => $quantity,
                            'available_stock' => $quantity,
                        ]);

                        $importedCount++;
                    }
                });
            } catch (\Throwable $e) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'isbn' => $isbn,
                    'message' => 'Database error: '.$e->getMessage(),
                ];
            }
        });

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }
}