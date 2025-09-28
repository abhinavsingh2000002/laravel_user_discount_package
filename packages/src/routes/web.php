<?php

use Illuminate\Support\Facades\Route;
use Abhinav\Discounts\Http\Controllers\DiscountController;
use Abhinav\Discounts\Http\Controllers\UserDiscountController;

// Admin Routes
Route::middleware(['web'])
    ->prefix('admin/discounts')
    ->name('discounts.')
    ->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('index');
        Route::get('/create', [DiscountController::class, 'create'])->name('create');
        Route::post('/store', [DiscountController::class, 'store'])->name('store');
        Route::get('/{discount}/edit', [DiscountController::class, 'edit'])->name('edit');
        Route::put('/{discount}/update', [DiscountController::class, 'update'])->name('update');
        Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');

        // User Management
        Route::get('/users', function () {
            return view('discounts::admin.user-management');
        })->name('user-management');
    });

// User Routes
Route::middleware(['web', 'auth'])
    ->prefix('discounts/user')
    ->name('discounts.user.')
    ->group(function () {
        Route::get('/', [UserDiscountController::class, 'index'])->name('index');
        Route::get('/history', [UserDiscountController::class, 'history'])->name('history');
        Route::get('/workflow', [UserDiscountController::class, 'workflow'])->name('workflow');
        Route::get('/business-rules', [UserDiscountController::class, 'businessRules'])->name('business-rules');
    });

// API Routes
Route::middleware(['web'])
    ->prefix('api/discounts')
    ->name('api.discounts.')
    ->group(function () {
        Route::post('/assign', [UserDiscountController::class, 'assign'])->name('assign');
        Route::post('/revoke', [UserDiscountController::class, 'revoke'])->name('revoke');
        Route::get('/eligible', [UserDiscountController::class, 'eligible'])->name('eligible');
        Route::post('/apply', [UserDiscountController::class, 'apply'])->name('apply');
        Route::get('/user/{userId}/discounts', [UserDiscountController::class, 'userDiscounts'])->name('user-discounts');
    });
