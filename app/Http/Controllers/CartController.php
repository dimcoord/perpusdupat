<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Session-based loan cart. Holds a unique list of book IDs (one copy per title).
 */
class CartController extends Controller
{
    public const SESSION_KEY = 'loan_cart';

    /** Maximum number of books per checkout. */
    public const MAX_ITEMS = 5;

    public static function ids(Request $request): array
    {
        return array_values(array_unique(array_map('intval', $request->session()->get(self::SESSION_KEY, []))));
    }

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $ids = self::ids($request);
        $books = Book::whereIn('id', $ids)->get();

        if ($request->wantsJson()) {
            return response()->json(['books' => $books, 'count' => $books->count(), 'max' => self::MAX_ITEMS]);
        }

        return view('cart.index', ['books' => $books, 'max' => self::MAX_ITEMS]);
    }

    public function add(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $ids = self::ids($request);

        if (in_array($book->id, $ids, true)) {
            return $this->respond($request, false, "Buku '{$book->title}' sudah ada di keranjang.", count($ids));
        }

        if (count($ids) >= self::MAX_ITEMS) {
            return $this->respond($request, false, 'Keranjang penuh (maksimal '.self::MAX_ITEMS.' buku per peminjaman).', count($ids));
        }

        if ($book->available_stock <= 0) {
            return $this->respond($request, false, "Maaf, stok buku '{$book->title}' sedang kosong.", count($ids));
        }

        $ids[] = $book->id;
        $request->session()->put(self::SESSION_KEY, $ids);

        return $this->respond($request, true, "Buku '{$book->title}' ditambahkan ke keranjang.", count($ids));
    }

    public function remove(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $ids = array_values(array_diff(self::ids($request), [$book->id]));
        $request->session()->put(self::SESSION_KEY, $ids);

        return $this->respond($request, true, "Buku '{$book->title}' dihapus dari keranjang.", count($ids));
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $request->session()->forget(self::SESSION_KEY);

        return $this->respond($request, true, 'Keranjang dikosongkan.', 0);
    }

    private function respond(Request $request, bool $success, string $message, int $count): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message, 'count' => $count], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
