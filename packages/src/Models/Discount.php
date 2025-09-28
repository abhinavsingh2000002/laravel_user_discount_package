<?php

namespace Abhinav\Discounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'value', 'active', 'expires_at', 'per_user_cap'];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'per_user_cap' => 'integer',
        'value' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DiscountAudit::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->active && !$this->isExpired();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('revoked', false);
        });
    }
}
