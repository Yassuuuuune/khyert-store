<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateProfile extends Model
{
    protected $fillable = [
        'user_id', 'website', 'promotion_methods', 'payment_method', 'payment_details',
        'status', 'rejection_reason', 'total_earned', 'total_pending', 'total_paid',
        'balance', 'total_clicks', 'total_conversions',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'affiliate_id', 'user_id');
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class, 'affiliate_id', 'user_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class, 'affiliate_id', 'user_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->total_clicks === 0) return 0;
        return round(($this->total_conversions / $this->total_clicks) * 100, 2);
    }
}
