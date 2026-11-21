<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IsbnScanRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookApiController extends Controller
{
    /**
     * Process scanned ISBN payload from Mobile App Scanner node.
     *
     * Behavior:
     * - If ISBN exists: Increment total_stock & available_stock, update metadata.
     * - If ISBN does not exist: Create new book record.
     */
    public function scanIsbn(IsbnScanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $isbn = $validated['isbn'];
        $stockToAdd = (int) $validated['stock'];

        // Ensure category exists or fallback
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId) {
            $defaultCategory = Category::firstOrCreate(
                ['code' => '000'],
                ['name' => 'Karya Umum / Koleksi Utama']
            );
            $categoryId = $defaultCategory->id;
        }

        $existingBook = Book::where('isbn', $isbn)->first();

        if ($existingBook) {
            // Existing book: increment stock and optionally update metadata
            $updatedBook = DB::transaction(function () use ($existingBook, $validated, $stockToAdd, $categoryId) {
                $existingBook->total_stock += $stockToAdd;
                $existingBook->available_stock += $stockToAdd;

                // Update non-empty fields
                if (! empty($validated['title'])) {
                    $existingBook->title = $validated['title'];
                }
                if (! empty($validated['author'])) {
                    $existingBook->author = $validated['author'];
                }
                if (! empty($validated['publisher'])) {
                    $existingBook->publisher = $validated['publisher'];
                }
                if (! empty($validated['publication_year'])) {
                    $existingBook->publication_year = $validated['publication_year'];
                }
                if (! empty($validated['rack_location'])) {
                    $existingBook->rack_location = $validated['rack_location'];
                }
                if (array_key_exists('sinopsis', $validated)) {
                    $existingBook->sinopsis = $validated['sinopsis'];
                }
                if (! empty($validated['cover_image'])) {
                    $existingBook->cover_image = $validated['cover_image'];
                }
                $existingBook->category_id = $categoryId;
                $existingBook->save();

                return $existingBook;
            });

            return response()->json([
                'success' => true,
                'message' => "Stok buku '{$updatedBook->title}' (ISBN: {$isbn}) berhasil ditambahkan sebanyak {$stockToAdd} eks.",
                'data' => [
                    'id' => $updatedBook->id,
                    'isbn' => $updatedBook->isbn,
                    'title' => $updatedBook->title,
                    'author' => $updatedBook->author,
                    'publisher' => $updatedBook->publisher,
                    'publication_year' => $updatedBook->publication_year,
                    'category_id' => $updatedBook->category_id,
                    'rack_location' => $updatedBook->rack_location,
                    'sinopsis' => $updatedBook->sinopsis,
                    'cover_image' => $updatedBook->cover_image,
                    'cover_url' => $updatedBook->cover_url,
                    'total_stock' => $updatedBook->total_stock,
                    'available_stock' => $updatedBook->available_stock,
                    'action' => 'stock_incremented',
                ],
            ], 200);
        }

        // Create new book
        $newBook = DB::transaction(function () use ($validated, $stockToAdd, $categoryId) {
            return Book::create([
                'isbn' => $validated['isbn'],
                'title' => $validated['title'],
                'author' => $validated['author'] ?? 'Anonim',
                'publisher' => $validated['publisher'] ?? '-',
                'publication_year' => $validated['publication_year'] ?? date('Y'),
                'category_id' => $categoryId,
                'rack_location' => $validated['rack_location'] ?? '-',
                'sinopsis' => $validated['sinopsis'] ?? null,
                'cover_image' => $validated['cover_image'] ?? null,
                'total_stock' => $stockToAdd,
                'available_stock' => $stockToAdd,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Buku baru '{$newBook->title}' (ISBN: {$newBook->isbn}) berhasil didaftarkan.",
            'data' => [
                'id' => $newBook->id,
                'isbn' => $newBook->isbn,
                'title' => $newBook->title,
                'author' => $newBook->author,
                'publisher' => $newBook->publisher,
                'publication_year' => $newBook->publication_year,
                'category_id' => $newBook->category_id,
                'rack_location' => $newBook->rack_location,
                'sinopsis' => $newBook->sinopsis,
                'cover_image' => $newBook->cover_image,
                'cover_url' => $newBook->cover_url,
                'total_stock' => $newBook->total_stock,
                'available_stock' => $newBook->available_stock,
                'action' => 'book_created',
            ],
        ], 201);
    }
}
