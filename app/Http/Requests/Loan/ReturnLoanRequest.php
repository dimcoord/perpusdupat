<?php

namespace App\Http\Requests\Loan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReturnLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $loan = $this->route('loan');

        return $this->user()?->can('returnBook', $loan) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'return_date' => ['nullable', 'date'],
            'librarian_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'return_date' => 'Tanggal Pengembalian',
            'librarian_notes' => 'Catatan Pustakawan',
        ];
    }
}
