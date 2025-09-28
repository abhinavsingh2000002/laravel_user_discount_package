<?php

namespace Abhinav\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountAudit extends Model
{
    protected $fillable = ['user_id', 'discount_id', 'amount_before', 'amount_after', 'applied_at'];

    protected $casts = [
        'amount_before' => 'decimal:2',
        'amount_after' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->amount_before - $this->amount_after;
    }
}
