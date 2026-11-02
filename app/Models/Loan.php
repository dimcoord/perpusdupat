<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    /**
     * Default fine per overdue day in IDR.
     */
    public const DEFAULT_FINE_PER_DAY = 1000;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'librarian_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }

    /**
     * User who borrowed the book.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Book being borrowed.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope for active loans (borrowed or overdue).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['dipinjam', 'terlambat', 'menunggu_persetujuan']);
    }

    /**
     * Scope for overdue loans.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'terlambat')
            ->orWhere(function (Builder $q) {
                $q->where('status', 'dipinjam')
                    ->whereDate('due_date', '<', Carbon::today());
            });
    }

    /**
     * Calculate overdue days and fine amount.
     */
    public function calculateFine(int $finePerDay = self::DEFAULT_FINE_PER_DAY): array
    {
        $dueDate = Carbon::parse($this->due_date)->startOfDay();
        $comparisonDate = $this->return_date
            ? Carbon::parse($this->return_date)->startOfDay()
            : Carbon::today()->startOfDay();

        if ($comparisonDate->greaterThan($dueDate)) {
            $daysOverdue = (int) $dueDate->diffInDays($comparisonDate);
            $fine = $daysOverdue * $finePerDay;

            return [
                'days_overdue' => $daysOverdue,
                'fine' => $fine,
            ];
        }

        return [
            'days_overdue' => 0,
            'fine' => 0,
        ];
    }
}
