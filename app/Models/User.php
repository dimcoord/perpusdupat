<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nis_nip',
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is a librarian (pustakawan).
     */
    public function isPustakawan(): bool
    {
        return $this->role === 'pustakawan';
    }

    /**
     * Check if user is a student (murid).
     */
    public function isMurid(): bool
    {
        return $this->role === 'murid';
    }

    /**
     * Visits relationship.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Loans relationship.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Loan receipts relationship.
     */
    public function loanReceipts(): HasMany
    {
        return $this->hasMany(LoanReceipt::class);
    }

    /**
     * Check if user has active or unfinished loans (pending, approved, borrowed, or overdue).
     */
    public function hasUnfinishedLoans(): bool
    {
        return $this->loanReceipts()
            ->whereIn('status', [LoanReceipt::STATUS_PENDING, LoanReceipt::STATUS_APPROVED])
            ->exists()
            || $this->loans()
                ->whereIn('status', ['menunggu_persetujuan', 'dipinjam', 'terlambat'])
                ->exists();
    }
}
