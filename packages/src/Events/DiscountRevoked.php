<?php

namespace Abhinav\Discounts\Events;

use Illuminate\Queue\SerializesModels;
use Abhinav\Discounts\Models\UserDiscount;

class DiscountRevoked
{
    use SerializesModels;

    public $userDiscount;

    public function __construct(UserDiscount $userDiscount)
    {
        $this->userDiscount = $userDiscount;
    }
}
