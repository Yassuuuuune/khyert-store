<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable attribution record linking one order to the affiliate that
 * generated it. The (order_id, affiliate_id) pair is unique at the database
 * level, which structurally prevents duplicate commission attribution.
 */
class AffiliateReferral extends Model
{
    protected $fillable = ['affiliate_id', 'order_id', 'customer_id', 'referral_code'];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
