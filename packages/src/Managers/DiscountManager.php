<?php

namespace Abhinav\Discounts\Managers;

use Abhinav\Discounts\Models\{Discount, UserDiscount, DiscountAudit};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class DiscountManager
{
    public function assign(int $userId, int $discountId): UserDiscount
    {
        return DB::transaction(function () use ($userId, $discountId) {
            $record = UserDiscount::firstOrCreate(
                ['user_id' => $userId, 'discount_id' => $discountId],
                ['usage_count' => 0, 'revoked' => false]
            );

            event(new \Abhinav\Discounts\Events\DiscountAssigned($record));
            return $record;
        });
    }

    public function revoke(int $userId, int $discountId): bool
    {
        $ud = UserDiscount::where('user_id', $userId)
            ->where('discount_id', $discountId)
            ->first();

        if (!$ud) {
            return false;
        }

        $ud->revoked = true;
        $ud->save();

        event(new \Abhinav\Discounts\Events\DiscountRevoked($ud));
        return true;
    }

    public function eligibleFor(int $userId)
    {
        return Discount::active()
            ->forUser($userId)
            ->with(['users' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('revoked', false);
            }])
            ->get()
            ->filter(function ($discount) use ($userId) {
                $userDiscount = $discount->users->first();
                return $userDiscount && $userDiscount->canUse();
            });
    }

    public function apply(int $userId, float $amount): array
    {
        return DB::transaction(function () use ($userId, $amount) {
            $eligible = $this->eligibleFor($userId);

            if ($eligible->isEmpty()) {
                return [
                    'original' => $amount,
                    'final' => $amount,
                    'discounts' => [],
                    'total_discount' => 0
                ];
            }

            $stacking = config('discounts.stacking_order', 'percentage_first');
            $cap = config('discounts.max_percentage_cap', 50);
            $round = config('discounts.rounding', 'floor');

            $applied = [];
            $totalDiscountAmount = 0;
            $currentAmount = $amount;

            // Deterministic sorting for consistent application order
            $eligible = $eligible->sortBy(function ($discount) use ($stacking) {
                if ($stacking === 'percentage_first') {
                    return $discount->type === 'percentage' ? 0 : 1;
                } else {
                    return $discount->type === 'fixed' ? 0 : 1;
                }
            })->sortBy('id'); // Secondary sort by ID for deterministic order

            foreach ($eligible as $discount) {
                // Lock the user discount record to prevent concurrent usage
                $userDiscount = UserDiscount::where('user_id', $userId)
                    ->where('discount_id', $discount->id)
                    ->lockForUpdate()
                    ->first();

                if (!$userDiscount || !$userDiscount->canUse()) {
                    continue;
                }

                // Calculate discount value
                $discountValue = $discount->type === 'percentage'
                    ? $currentAmount * ($discount->value / 100)
                    : min($discount->value, $currentAmount); // Don't exceed current amount

                if ($discountValue <= 0) {
                    continue;
                }

                // Apply the discount
                $totalDiscountAmount += $discountValue;
                $currentAmount -= $discountValue;

                // Update usage count atomically
                $userDiscount->increment('usage_count');

                // Create audit record
                DiscountAudit::create([
                    'user_id' => $userId,
                    'discount_id' => $discount->id,
                    'amount_before' => $amount,
                    'amount_after' => $currentAmount,
                    'applied_at' => now(),
                ]);

                $applied[] = [
                    'discount' => $discount,
                    'amount' => $discountValue
                ];

                // Fire event
                event(new \Abhinav\Discounts\Events\DiscountApplied($discount, $userId, $amount, $currentAmount));

                // Stop if amount is fully discounted
                if ($currentAmount <= 0) {
                    break;
                }
            }

            // Apply percentage cap if configured
            $maxDiscount = $amount * ($cap / 100);
            if ($totalDiscountAmount > $maxDiscount) {
                $currentAmount = $amount - $maxDiscount;
                $totalDiscountAmount = $maxDiscount;
            }

            // Apply rounding
            $final = match ($round) {
                'round' => round($currentAmount, 2),
                'ceil' => ceil($currentAmount * 100) / 100,
                'floor' => floor($currentAmount * 100) / 100,
                default => $currentAmount
            };

            return [
                'original' => $amount,
                'final' => $final,
                'discounts' => $applied,
                'total_discount' => $totalDiscountAmount
            ];
        });
    }
}
