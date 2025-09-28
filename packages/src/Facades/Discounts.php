<?php

namespace Abhinav\Discounts\Facades;

use Illuminate\Support\Facades\Facade;
use Abhinav\Discounts\Managers\DiscountManager;

class Discounts extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DiscountManager::class;
    }
}
