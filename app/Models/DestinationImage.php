<?php

namespace App\Models;

use App\Support\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationImage extends Model
{
    protected $fillable = ['destination_id', 'path', 'caption', 'sort_order'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function getUrlAttribute(): string
    {
        return Image::resolve(
            $this->path,
            'Travel photograph of ' . ($this->caption ?: ($this->destination?->name ?? 'southern Morocco'))
                . ', southern Morocco, vibrant colours, travel editorial photography',
            'landscape_4_3'
        );
    }
}
