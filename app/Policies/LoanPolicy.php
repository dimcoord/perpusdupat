<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Loan $loan): bool
    {
        return $user->isPustakawan() || $loan->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Loan $loan): bool
    {
        return $user->isPustakawan();
    }

    /**
     * Determine whether the user can approve the loan.
     */
    public function approve(User $user, Loan $loan): bool
    {
        return $user->isPustakawan() && $loan->status === 'menunggu_persetujuan';
    }

    /**
     * Determine whether the user can reject the loan.
     */
    public function reject(User $user, Loan $loan): bool
    {
        return $user->isPustakawan() && $loan->status === 'menunggu_persetujuan';
    }

    /**
     * Determine whether the user can process book return.
     */
    public function returnBook(User $user, Loan $loan): bool
    {
        return $user->isPustakawan() && in_array($loan->status, ['dipinjam', 'terlambat']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Loan $loan): bool
    {
        return $user->isPustakawan();
    }

    /**
     * Determine whether the user can export loan reports.
     */
    public function export(User $user): bool
    {
        return $user->isPustakawan();
    }
}
