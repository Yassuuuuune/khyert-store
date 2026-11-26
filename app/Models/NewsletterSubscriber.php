<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newsletter subscription captured from the public homepage footer.
 */
class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
