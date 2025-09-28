<?php

namespace Abhinav\Discounts;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Abhinav\Discounts\Managers\DiscountManager;
use Abhinav\Discounts\Listeners\LogDiscountActivity;
use Abhinav\Discounts\Events\DiscountAssigned;
use Abhinav\Discounts\Events\DiscountRevoked;
use Abhinav\Discounts\Events\DiscountApplied;

class DiscountsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/discounts.php', 'discounts');

        $this->app->singleton(DiscountManager::class, function ($app) {
            return new DiscountManager();
        });

        // Load helpers
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/discounts.php' => config_path('discounts.php'),
        ], 'discounts-config');

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'discounts');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Register event listeners
        Event::listen(DiscountAssigned::class, [LogDiscountActivity::class, 'handleDiscountAssigned']);
        Event::listen(DiscountRevoked::class, [LogDiscountActivity::class, 'handleDiscountRevoked']);
        Event::listen(DiscountApplied::class, [LogDiscountActivity::class, 'handleDiscountApplied']);
    }
}
