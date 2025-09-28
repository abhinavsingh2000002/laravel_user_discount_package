<?php

namespace Abhinav\Discounts\Events;

use Illuminate\Queue\SerializesModels;
use Abhinav\Discounts\Models\Discount;

class DiscountApplied
{
    use SerializesModels;

    public $discount;
    public $userId;
    public $beforeAmount;
    public $afterAmount;

    public function __construct(Discount $discount, int $userId, float $beforeAmount, float $afterAmount)
    {
        $this->discount = $discount;
        $this->userId = $userId;
        $this->beforeAmount = $beforeAmount;
        $this->afterAmount = $afterAmount;
    }
}
