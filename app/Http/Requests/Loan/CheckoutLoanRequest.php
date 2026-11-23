<?php

namespace App\Http\Requests\Loan;

use App\Http\Controllers\CartController;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Replaces StoreLoanRequest. The books come from the session cart,
 * so only borrower and optional dates are validated here.
 */
class CheckoutLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Gate in the controller.
    }

    public function rules(): array
    {
        return [
            'book_ids' => ['nullable', 'array', 'max:'.CartController::MAX_ITEMS],
            'book_ids.*' => ['integer', 'exists:books,id'],
            'nis_nip' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'loan_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_ids.max' => 'Maksimal '.CartController::MAX_ITEMS.' judul buku dalam sekali pengajuan peminjaman.',
        ];
    }
}
