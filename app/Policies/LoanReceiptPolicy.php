<?php

namespace App\Policies;

use App\Models\LoanReceipt;
use App\Models\User;

class LoanReceiptPolicy
{
    /**
     * Pustakawan can see every receipt; a student only their own.
     */
    public function view(User $user, LoanReceipt $receipt): bool
    {
        return $user->isPustakawan() || $receipt->user_id === $user->id;
    }

    /**
     * Returns are processed for the whole receipt at once.
     */
    public function returnBook(User $user, LoanReceipt $receipt): bool
    {
        return $user->isPustakawan() && $receipt->status === LoanReceipt::STATUS_APPROVED;
    }

    public function approve(User $user, LoanReceipt $receipt): bool
    {
        return $user->isPustakawan() && $receipt->isPending();
    }
}