<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const LEVELS = [
        'beginner' => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced' => 'Advanced',
        'all-levels' => 'All Levels',
    ];

    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'outcomes',
        'requirements',
        'audience',
        'product_type',
        'level',
        'price',
        'currency',
        'thumbnail',
        'status',
        'published_at',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'status' => ProductStatus::class,
            'price' => 'integer',
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'outcomes' => 'array',
            'requirements' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Public route-model binding only ever resolves published products —
     * an unpublished (draft/archived) or nonexistent slug falls through to
     * Laravel's normal 404.
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

    public function course(): HasOne
    {
        return $this->hasOne(Course::class);
    }

    public function webinar(): HasOne
    {
        return $this->hasOne(Webinar::class);
    }

    public function workshop(): HasOne
    {
        return $this->hasOne(Workshop::class);
    }

    public function bootcamp(): HasOne
    {
        return $this->hasOne(Bootcamp::class);
    }

    public function mentoring(): HasOne
    {
        return $this->hasOne(Mentoring::class);
    }

    public function digitalProduct(): HasOne
    {
        return $this->hasOne(DigitalProduct::class);
    }

    /** Join link / attendee instructions — only ever rendered for enrolled users. */
    public function access(): HasOne
    {
        return $this->hasOne(ProductAccess::class);
    }

    /** The one subtype relation that actually matches this product's type. */
    public function getTypeDetailsAttribute(): ?Model
    {
        return match ($this->product_type) {
            ProductType::Course => $this->course,
            ProductType::Webinar => $this->webinar,
            ProductType::Workshop => $this->workshop,
            ProductType::Bootcamp => $this->bootcamp,
            ProductType::Mentoring => $this->mentoring,
            ProductType::DigitalProduct => $this->digitalProduct,
        };
    }

    /** Eager-load every subtype relation — cheap (6 constrained queries, not per-row) and avoids N+1. */
    public function scopeWithTypeDetails(Builder $query): Builder
    {
        return $query->with(['course', 'webinar', 'workshop', 'bootcamp', 'mentoring', 'digitalProduct']);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOfType(Builder $query, ProductType|string $type): Builder
    {
        return $query->where('product_type', $type);
    }

    public function scopeForCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
    }

    public function scopeByInstructor(Builder $query, int $instructorId): Builder
    {
        return $query->where('instructor_id', $instructorId);
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
            ->when($filters['category'] ?? null, fn (Builder $q, string $slug) => $q->forCategory($slug))
            ->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->ofType($type))
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
        return $this->product_type->label();
    }

    public function getTypeVariantAttribute(): string
    {
        return $this->product_type->badgeVariant();
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
        return $this->product_type->ctaLabel();
    }

    /** Human-readable duration, derived from whichever subtype is attached. */
    public function getDurationLabelAttribute(): ?string
    {
        return $this->type_details?->durationLabel();
    }

    /** The only purchasability rule in Phase 5: a product must be published. */
    public function isPurchasable(): bool
    {
        return $this->status === ProductStatus::Published;
    }
}
