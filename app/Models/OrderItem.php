<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'seller_id', 'product_name', 'product_image',
        'unit_price', 'quantity', 'subtotal',
        'affiliate_commission_rate', 'affiliate_commission_amount', 'commission_generated',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'affiliate_commission_rate' => 'decimal:2',
        'affiliate_commission_amount' => 'decimal:2',
        'commission_generated' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
