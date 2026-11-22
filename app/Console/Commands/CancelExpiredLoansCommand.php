<?php

namespace App\Console\Commands;

use App\Models\LoanReceipt;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('loans:cancel-expired')]
#[Description('Membatalkan otomatis pengajuan peminjaman yang berstatus menunggu persetujuan lebih dari 2 hari.')]
class CancelExpiredLoansCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa pengajuan peminjaman yang kedaluwarsa (> 2 hari)...');

        $cancelledCount = LoanReceipt::cancelExpiredPendingReceipts();

        if ($cancelledCount > 0) {
            $this->info("Berhasil membatalkan {$cancelledCount} pengajuan peminjaman yang kedaluwarsa.");
        } else {
            $this->info('Tidak ada pengajuan peminjaman yang kedaluwarsa.');
        }

        return self::SUCCESS;
    }
}
