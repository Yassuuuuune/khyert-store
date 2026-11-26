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

class Destination extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'name_ar', 'name_en',
        'description', 'description_ar', 'description_en', 'main_image',
        'city', 'region', 'latitude', 'longitude',
        'best_period', 'tourist_info', 'facilities',
        'is_featured', 'is_active', 'views',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
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

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('sort_order');
    }

    public function tourismCategories(): BelongsToMany
    {
        return $this->belongsToMany(TourismCategory::class, 'destination_category');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getMainImageUrlAttribute(): string
    {
        return Image::resolve(
            $this->main_image,
            'Travel photograph of ' . $this->name . ' in ' . $this->city . ', southern Morocco, '
                . 'wide landscape, golden hour light, cinematic travel photography',
            'landscape_16_9'
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
        return $this->description;
    }

    public function getFacilitiesArrayAttribute(): array
    {
        return $this->facilities ? json_decode($this->facilities, true) : [];
    }
}
