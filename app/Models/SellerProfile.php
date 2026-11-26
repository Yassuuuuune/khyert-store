<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SellerProfile extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id', 'shop_name', 'slug', 'description', 'logo', 'banner',
        'city', 'region', 'phone', 'whatsapp', 'commission_rate',
        'status', 'rejection_reason', 'total_sales', 'balance',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('shop_name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id', 'user_id');
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class, 'seller_id', 'user_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return str_starts_with($this->logo, 'http')
            ? $this->logo
            : asset('storage/' . $this->logo);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner) {
            return null;
        }

        return str_starts_with($this->banner, 'http')
            ? $this->banner
            : asset('storage/' . $this->banner);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->shop_name));

        return strtoupper(mb_substr($parts[0] ?? 'S', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
