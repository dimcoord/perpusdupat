<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'publication_year',
        'jumlah_halaman',
        'rack_location',
        'sinopsis',
        'total_stock',
        'available_stock',
        'cover_image',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'cover_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'jumlah_halaman' => 'integer',
            'total_stock' => 'integer',
            'available_stock' => 'integer',
        ];
    }

    /**
     * Get the resolved public URL for the book cover image.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset('storage/'.$this->cover_image);
    }

    /**
     * Get or set tahun terbit (alias for publication_year).
     */
    public function getTahunTerbitAttribute(): ?int
    {
        return $this->publication_year;
    }

    public function setTahunTerbitAttribute($value): void
    {
        $this->attributes['publication_year'] = $value;
    }

    /**
     * Category of the book.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Loans for the book.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Scope to search books by query term across ISBN, title, author, and rack.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('isbn', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
                ->orWhere('author', 'like', "%{$term}%")
                ->orWhere('sinopsis', 'like', "%{$term}%")
                ->orWhere('rack_location', 'like', "%{$term}%")
                ->orWhereHas('category', function (Builder $cq) use ($term) {
                    $cq->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope to filter available books.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_stock', '>', 0);
    }
}
