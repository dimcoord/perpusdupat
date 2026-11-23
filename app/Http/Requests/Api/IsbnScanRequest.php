<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IsbnScanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorized via Sanctum token middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'isbn' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:'.(date('Y') + 1)],
            'jumlah_halaman' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'sinopsis' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:1', 'max:1000'],
            'cover_image' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Handle a failed validation attempt with JSON envelope.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi data pemindaian ISBN gagal.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
