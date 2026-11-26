<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Image;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TourismCategory extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'name_ar', 'name_en', 'description', 'icon', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

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

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'destination_category');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): string
    {
        return Image::resolve(
            $this->image,
            'Travel photograph representing ' . $this->name . ' in southern Morocco, editorial travel photography',
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
}
