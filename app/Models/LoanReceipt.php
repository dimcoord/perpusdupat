<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LoanReceipt extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'menunggu_persetujuan';

    public const STATUS_APPROVED = 'disetujui';

    public const STATUS_RETURNED = 'dikembalikan';

    public const STATUS_REJECTED = 'ditolak';

    public const EXTENSION_PENDING = 'menunggu_persetujuan';

    public const EXTENSION_APPROVED = 'disetujui';

    public const EXTENSION_REJECTED = 'ditolak';

    protected $fillable = [
        'receipt_number',
        'user_id',
        'processed_by',
        'loan_date',
        'due_date',
        'status',
        'extension_status',
        'extension_count',
        'extension_requested_at',
        'extension_notes',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'extension_requested_at' => 'datetime',
        'extension_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExtensionPending(): bool
    {
        return $this->extension_status === self::EXTENSION_PENDING;
    }

    public function isExtensionApproved(): bool
    {
        return $this->extension_status === self::EXTENSION_APPROVED;
    }

    public function isExtensionRejected(): bool
    {
        return $this->extension_status === self::EXTENSION_REJECTED;
    }

    public function canBeExtended(): bool
    {
        // Harus berstatus disetujui/aktif dipinjam
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        // Tidak boleh sedang menunggu persetujuan perpanjangan
        if ($this->isExtensionPending()) {
            return false;
        }

        // Maksimal 1 kali perpanjangan dan belum pernah ditolak
        if ($this->extension_count >= 1 || $this->extension_status === self::EXTENSION_REJECTED) {
            return false;
        }

        // Hanya boleh sebelum jatuh tempo (bukan saat terlambat)
        if ($this->due_date && $this->due_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Format: LN-20261117-00042
     */
    public static function numberFor(self $receipt): string
    {
        return 'LN-'.$receipt->created_at->format('Ymd').'-'.str_pad((string) $receipt->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-cancel any pending loan receipts older than 2 days (48 hours).
     */
    public static function cancelExpiredPendingReceipts(): int
    {
        $expiredReceipts = static::where('status', self::STATUS_PENDING)
            ->where('created_at', '<=', now()->subHours(48))
            ->get();

        if ($expiredReceipts->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($expiredReceipts as $receipt) {
            DB::transaction(function () use ($receipt, &$count) {
                $lockedReceipt = static::whereKey($receipt->id)->lockForUpdate()->first();
                if ($lockedReceipt && $lockedReceipt->isPending()) {
                    $lockedReceipt->loans()->update([
                        'status' => 'ditolak',
                        'librarian_notes' => 'Otomatis dibatalkan: batas waktu pengambilan loket (2 hari) telah berakhir.',
                    ]);
                    $lockedReceipt->update([
                        'status' => self::STATUS_REJECTED,
                    ]);
                    $count++;
                }
            });
        }

        return $count;
    }
}
