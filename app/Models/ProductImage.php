<?php

namespace App\Models;

use App\Support\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'alt', 'sort_order', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Image::resolve(
            $this->path,
            'Close-up product photograph of ' . ($this->alt ?: ($this->product?->name ?? 'Moroccan artisan product'))
                . ', southern Morocco craftsmanship, warm tones',
            'landscape_4_3'
        );
    }
}
