<?php

namespace Abhinav\Discounts\Listeners;

use Illuminate\Support\Facades\Log;
use Abhinav\Discounts\Events\DiscountAssigned;
use Abhinav\Discounts\Events\DiscountRevoked;
use Abhinav\Discounts\Events\DiscountApplied;

class LogDiscountActivity
{
    public function handleDiscountAssigned(DiscountAssigned $event): void
    {
        Log::info('Discount assigned', [
            'user_id' => $event->userDiscount->user_id,
            'discount_id' => $event->userDiscount->discount_id,
            'discount_name' => $event->userDiscount->discount->name,
        ]);
    }

    public function handleDiscountRevoked(DiscountRevoked $event): void
    {
        Log::info('Discount revoked', [
            'user_id' => $event->userDiscount->user_id,
            'discount_id' => $event->userDiscount->discount_id,
            'discount_name' => $event->userDiscount->discount->name,
            'usage_count' => $event->userDiscount->usage_count,
        ]);
    }

    public function handleDiscountApplied(DiscountApplied $event): void
    {
        Log::info('Discount applied', [
            'user_id' => $event->userId,
            'discount_id' => $event->discount->id,
            'discount_name' => $event->discount->name,
            'before_amount' => $event->beforeAmount,
            'after_amount' => $event->afterAmount,
            'discount_amount' => $event->beforeAmount - $event->afterAmount,
        ]);
    }
}
