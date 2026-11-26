<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Image;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'seller_id', 'category_id', 'name', 'slug', 'name_ar', 'name_en',
        'description', 'description_ar', 'description_en', 'main_image',
        'price', 'sale_price', 'cost_price', 'stock', 'sku', 'weight',
        'affiliate_commission', 'affiliate_enabled', 'status', 'rejection_reason',
        'is_featured', 'views', 'sales_count', 'rating_avg', 'rating_count',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'affiliate_commission' => 'decimal:2',
        'is_featured' => 'boolean',
        'affiliate_enabled' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function sellerProfile(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price && $this->sale_price < $this->price
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->sale_price || $this->sale_price >= $this->price) return 0;
        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getMainImageUrlAttribute(): string
    {
        return Image::resolve(
            $this->main_image,
            'Professional product photograph of ' . $this->name
                . ', authentic Moroccan artisan product from southern Morocco, warm natural lighting, clean neutral background',
            'landscape_4_3'
        );
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->name_ar) return $this->name_ar;
        if ($locale === 'en' && $this->name_en) return $this->name_en;
        return $this->name;
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->description_ar) return $this->description_ar;
        if ($locale === 'en' && $this->description_en) return $this->description_en;
        return $this->description ?? '';
    }

    public function getEffectiveCommissionRateAttribute(): float
    {
        return $this->affiliate_commission
            ?? (float) config('affiliate.default_commission', 10);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }
}
