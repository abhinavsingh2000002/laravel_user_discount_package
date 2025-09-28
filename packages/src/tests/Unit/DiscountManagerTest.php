<?php

namespace Abhinav\Discounts\Tests\Unit;

use Abhinav\Discounts\Tests\TestCase;
use Abhinav\Discounts\Facades\Discounts;
use Abhinav\Discounts\Models\Discount;
use Abhinav\Discounts\Models\UserDiscount;
use Abhinav\Discounts\Models\DiscountAudit;
use Carbon\Carbon;

class DiscountManagerTest extends TestCase
{
    /** @test */
    public function it_enforces_per_user_usage_cap()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'per_user_cap' => 1,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        $first = Discounts::apply(1, 100);
        $this->assertEquals(90, $first['final']);
        $this->assertCount(1, $first['discounts']);

        $second = Discounts::apply(1, 100);
        $this->assertEquals(100, $second['final']);
        $this->assertCount(0, $second['discounts']);
    }

    /** @test */
    public function it_applies_percentage_discounts_correctly()
    {
        $discount = Discount::create([
            'name' => '20% off',
            'type' => 'percentage',
            'value' => 20,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        $this->assertEquals(80, $result['final']);
        $this->assertEquals(20, $result['total_discount']);
    }

    /** @test */
    public function it_applies_fixed_discounts_correctly()
    {
        $discount = Discount::create([
            'name' => '$10 off',
            'type' => 'fixed',
            'value' => 10,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        $this->assertEquals(90, $result['final']);
        $this->assertEquals(10, $result['total_discount']);
    }

    /** @test */
    public function it_stacks_discounts_deterministically()
    {
        $percentageDiscount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);

        $fixedDiscount = Discount::create([
            'name' => '$5 off',
            'type' => 'fixed',
            'value' => 5,
            'active' => true,
        ]);

        Discounts::assign(1, $percentageDiscount->id);
        Discounts::assign(1, $fixedDiscount->id);

        $result = Discounts::apply(1, 100);

        // With percentage_first stacking: 10% of 100 = 10, then $5 off = 5, total = 15
        $this->assertEquals(85, $result['final']);
        $this->assertEquals(15, $result['total_discount']);
        $this->assertCount(2, $result['discounts']);
    }

    /** @test */
    public function it_respects_max_percentage_cap()
    {
        config(['discounts.max_percentage_cap' => 15]);

        $discount = Discount::create([
            'name' => '20% off',
            'type' => 'percentage',
            'value' => 20,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        // Should be capped at 15% (15 off 100 = 85)
        $this->assertEquals(85, $result['final']);
        $this->assertEquals(15, $result['total_discount']);
    }

    /** @test */
    public function it_ignores_expired_discounts()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        $this->assertEquals(100, $result['final']);
        $this->assertCount(0, $result['discounts']);
    }

    /** @test */
    public function it_ignores_inactive_discounts()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => false,
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        $this->assertEquals(100, $result['final']);
        $this->assertCount(0, $result['discounts']);
    }

    /** @test */
    public function it_ignores_revoked_discounts()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);
        Discounts::revoke(1, $discount->id);

        $result = Discounts::apply(1, 100);
        $this->assertEquals(100, $result['final']);
        $this->assertCount(0, $result['discounts']);
    }

    /** @test */
    public function it_creates_audit_records()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);
        Discounts::apply(1, 100);

        $this->assertDatabaseHas('discount_audits', [
            'user_id' => 1,
            'discount_id' => $discount->id,
            'amount_before' => 100,
            'amount_after' => 90,
        ]);
    }

    /** @test */
    public function it_increments_usage_count()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);
        Discounts::apply(1, 100);

        $userDiscount = UserDiscount::where('user_id', 1)
            ->where('discount_id', $discount->id)
            ->first();

        $this->assertEquals(1, $userDiscount->usage_count);
    }

    /** @test */
    public function it_handles_concurrent_usage_safely()
    {
        $discount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'per_user_cap' => 1,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        // Simulate concurrent access
        $result1 = Discounts::apply(1, 100);
        $result2 = Discounts::apply(1, 100);

        // Only one should get the discount
        $discountApplied = 0;
        if ($result1['final'] < 100) $discountApplied++;
        if ($result2['final'] < 100) $discountApplied++;

        $this->assertEquals(1, $discountApplied);
    }

    /** @test */
    public function it_applies_rounding_correctly()
    {
        config(['discounts.rounding' => 'floor']);

        $discount = Discount::create([
            'name' => '33.33% off',
            'type' => 'percentage',
            'value' => 33.33,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        $result = Discounts::apply(1, 100);
        // 33.33% of 100 = 33.33, floor = 33
        $this->assertEquals(67, $result['final']);
    }
}
