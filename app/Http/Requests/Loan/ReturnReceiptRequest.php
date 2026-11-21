<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class ReturnReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Gate in the controller.
    }

    public function rules(): array
    {
        return [
            'return_date' => ['nullable', 'date'],
            'librarian_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
