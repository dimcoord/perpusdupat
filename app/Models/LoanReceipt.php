<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanReceipt extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'menunggu_persetujuan';
    public const STATUS_APPROVED = 'disetujui';
    public const STATUS_RETURNED = 'dikembalikan';

    protected $fillable = [
        'receipt_number',
        'user_id',
        'processed_by',
        'loan_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
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

    /**
     * Format: LN-20261117-00042
     */
    public static function numberFor(self $receipt): string
    {
        return 'LN-'.$receipt->created_at->format('Ymd').'-'.str_pad((string) $receipt->id, 5, '0', STR_PAD_LEFT);
    }
}