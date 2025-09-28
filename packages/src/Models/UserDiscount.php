<?php

namespace Abhinav\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiscount extends Model
{
    protected $fillable = ['user_id', 'discount_id', 'usage_count', 'revoked'];

    protected $casts = [
        'revoked' => 'boolean',
        'usage_count' => 'integer',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function hasReachedCap(): bool
    {
        return $this->discount->per_user_cap &&
               $this->usage_count >= $this->discount->per_user_cap;
    }

    public function canUse(): bool
    {
        return !$this->isRevoked() &&
               !$this->hasReachedCap() &&
               $this->discount->isActive();
    }
}
