<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * Product types supported for Phase 2 discovery. Values are the stored
     * `product_type`; commerce/learning behaviour per type is out of scope.
     */
    public const TYPES = [
        'course' => 'Course',
        'webinar' => 'Webinar',
        'workshop' => 'Workshop',
        'bootcamp' => 'Bootcamp',
        'mentoring' => 'Mentoring',
        'digital-product' => 'Digital Product',
    ];

    public const LEVELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'all-levels' => 'All Levels',
    ];

    /** Badge variant per product type — the badge component only offers these three. */
    private const TYPE_VARIANTS = [
        'course' => 'neutral',
        'webinar' => 'neutral',
        'workshop' => 'accent',
        'bootcamp' => 'accent',
        'mentoring' => 'lavender',
        'digital-product' => 'lavender',
    ];

    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'product_type',
        'level',
        'duration',
        'price',
        'thumbnail',
        'status',
        'featured',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'featured' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Public route-model binding only ever resolves published products —
     * an unpublished or nonexistent slug falls through to Laravel's normal 404.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->published()->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Apply the shared catalog filters (search, category, type, level, price).
     * Used by both the catalog and category pages to avoid duplicated query logic.
     *
     * @param  array{search?: ?string, category?: ?string, type?: ?string, level?: ?string, price?: ?string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('instructor', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $q, string $slug) => $q->whereHas(
                'category', fn (Builder $q) => $q->where('slug', $slug)
            ))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('product_type', $type))
            ->when($filters['level'] ?? null, fn (Builder $q, string $level) => $q->where('level', $level))
            ->when($filters['price'] ?? null, function (Builder $q, string $price) {
                match ($price) {
                    'free' => $q->where('price', 0),
                    'paid' => $q->where('price', '>', 0),
                    default => null,
                };
            });
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'newest' => $query->orderByDesc('created_at'),
            'price-low' => $query->orderBy('price')->orderByDesc('created_at'),
            'price-high' => $query->orderByDesc('price')->orderByDesc('created_at'),
            default => $query->orderByDesc('featured')->orderByDesc('created_at'),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->product_type] ?? Str::headline($this->product_type);
    }

    public function getTypeVariantAttribute(): string
    {
        return self::TYPE_VARIANTS[$this->product_type] ?? 'neutral';
    }

    public function getLevelLabelAttribute(): ?string
    {
        if (! $this->level) {
            return null;
        }

        return self::LEVELS[$this->level] ?? Str::headline($this->level);
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->price === 0
            ? 'Free'
            : 'Rp'.number_format($this->price, 0, ',', '.');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail ?: 'https://placehold.co/640x400/eaedff/00668a?text='.urlencode($this->title);
    }

    public function getCtaLabelAttribute(): string
    {
        return match ($this->product_type) {
            'webinar', 'workshop' => 'Register',
            'mentoring' => 'Book a Session',
            'digital-product' => 'Get Access',
            default => 'Enroll Now',
        };
    }
}
