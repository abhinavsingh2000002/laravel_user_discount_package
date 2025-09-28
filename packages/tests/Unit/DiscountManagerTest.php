<?php

namespace Abhinav\Discounts\Tests\Unit;

use Abhinav\Discounts\Managers\DiscountManager;
use Abhinav\Discounts\Models\{Discount, UserDiscount};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DiscountManagerTest extends TestCase
{
    use RefreshDatabase;

    protected DiscountManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new DiscountManager();
    }

    /** @test */
    public function it_applies_discounts_correctly_with_usage_caps()
    {
        $userId = 1;

        // Create a percentage discount
        $percentage = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
            'expires_at' => Carbon::tomorrow(),
            'per_user_cap' => 2
        ]);

        // Create a fixed discount
        $fixed = Discount::create([
            'name' => '50 fixed off',
            'type' => 'fixed',
            'value' => 50,
            'active' => true,
            'expires_at' => Carbon::tomorrow(),
            'per_user_cap' => 1
        ]);

        // Assign discounts to user
        $this->manager->assign($userId, $percentage->id);
        $this->manager->assign($userId, $fixed->id);

        // Apply discounts to amount
        $amount = 500;
        $result = $this->manager->apply($userId, $amount);

        // Assert final amount calculation
        $this->assertLessThan($amount, $result['final']);
        $this->assertCount(2, $result['discounts']); // Both applied
        $this->assertEquals($amount - ($amount*0.1 + 50), $result['final']);

        // Apply again (usage caps limit)
        $result2 = $this->manager->apply($userId, $amount);

        $this->assertEquals($amount - ($amount*0.1), $result2['final']); // Fixed discount exhausted
    }
}
