<?php

namespace Abhinav\Discounts\Tests\Feature;

use Abhinav\Discounts\Tests\TestCase;
use Abhinav\Discounts\Facades\Discounts;
use Abhinav\Discounts\Models\Discount;
use Abhinav\Discounts\Models\UserDiscount;
use Abhinav\Discounts\Models\DiscountAudit;
use Carbon\Carbon;

class DiscountWorkflowTest extends TestCase
{
    /** @test */
    public function complete_discount_workflow_works_correctly()
    {
        // Create a discount
        $discount = Discount::create([
            'name' => 'Black Friday Sale',
            'type' => 'percentage',
            'value' => 25,
            'active' => true,
            'expires_at' => Carbon::now()->addDays(7),
            'per_user_cap' => 3,
        ]);

        // Assign discount to user
        $userDiscount = Discounts::assign(1, $discount->id);
        $this->assertInstanceOf(UserDiscount::class, $userDiscount);
        $this->assertFalse($userDiscount->revoked);
        $this->assertEquals(0, $userDiscount->usage_count);

        // Check if user is eligible
        $eligible = Discounts::eligibleFor(1);
        $this->assertCount(1, $eligible);
        $this->assertEquals($discount->id, $eligible->first()->id);

        // Apply discount multiple times
        $result1 = Discounts::apply(1, 100);
        $this->assertEquals(75, $result1['final']);
        $this->assertEquals(25, $result1['total_discount']);
        $this->assertCount(1, $result1['discounts']);

        $result2 = Discounts::apply(1, 200);
        $this->assertEquals(150, $result2['final']);
        $this->assertEquals(50, $result2['total_discount']);

        $result3 = Discounts::apply(1, 100);
        $this->assertEquals(75, $result3['final']);

        // Fourth application should not apply (cap reached)
        $result4 = Discounts::apply(1, 100);
        $this->assertEquals(100, $result4['final']);
        $this->assertCount(0, $result4['discounts']);

        // Check audit records
        $audits = DiscountAudit::where('user_id', 1)
            ->where('discount_id', $discount->id)
            ->get();
        $this->assertCount(3, $audits);

        // Check usage count
        $userDiscount->refresh();
        $this->assertEquals(3, $userDiscount->usage_count);
    }

    /** @test */
    public function revoke_discount_prevents_further_usage()
    {
        $discount = Discount::create([
            'name' => 'Limited Time Offer',
            'type' => 'fixed',
            'value' => 50,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        // Apply once
        $result1 = Discounts::apply(1, 100);
        $this->assertEquals(50, $result1['final']);

        // Revoke discount
        $revoked = Discounts::revoke(1, $discount->id);
        $this->assertTrue($revoked);

        // Try to apply again - should not work
        $result2 = Discounts::apply(1, 100);
        $this->assertEquals(100, $result2['final']);
        $this->assertCount(0, $result2['discounts']);

        // Check that user is no longer eligible
        $eligible = Discounts::eligibleFor(1);
        $this->assertCount(0, $eligible);
    }

    /** @test */
    public function complex_stacking_scenario()
    {
        // Create multiple discounts
        $percentageDiscount = Discount::create([
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10,
            'active' => true,
        ]);

        $fixedDiscount = Discount::create([
            'name' => '$20 off',
            'type' => 'fixed',
            'value' => 20,
            'active' => true,
        ]);

        $anotherPercentage = Discount::create([
            'name' => '5% off',
            'type' => 'percentage',
            'value' => 5,
            'active' => true,
        ]);

        // Assign all discounts
        Discounts::assign(1, $percentageDiscount->id);
        Discounts::assign(1, $fixedDiscount->id);
        Discounts::assign(1, $anotherPercentage->id);

        // Apply with percentage_first stacking
        config(['discounts.stacking_order' => 'percentage_first']);
        $result = Discounts::apply(1, 100);

        // Expected: 10% of 100 = 10, then 5% of 90 = 4.5, then $20 off = 20
        // But $20 is more than remaining amount, so only apply what's left
        // 100 - 10 - 4.5 = 85.5, then min(20, 85.5) = 20
        // Final: 100 - 10 - 4.5 - 20 = 65.5
        $this->assertEquals(65.5, $result['final']);
        $this->assertCount(3, $result['discounts']);
    }

    /** @test */
    public function expiration_handling()
    {
        $discount = Discount::create([
            'name' => 'Expiring Soon',
            'type' => 'percentage',
            'value' => 15,
            'active' => true,
            'expires_at' => Carbon::now()->addMinutes(1),
        ]);

        Discounts::assign(1, $discount->id);

        // Should work before expiration
        $result1 = Discounts::apply(1, 100);
        $this->assertEquals(85, $result1['final']);

        // Travel to after expiration
        Carbon::setTestNow(Carbon::now()->addMinutes(2));

        // Should not work after expiration
        $result2 = Discounts::apply(1, 100);
        $this->assertEquals(100, $result2['final']);
        $this->assertCount(0, $result2['discounts']);

        Carbon::setTestNow();
    }

    /** @test */
    public function concurrent_application_safety()
    {
        $discount = Discount::create([
            'name' => 'One Time Use',
            'type' => 'percentage',
            'value' => 50,
            'per_user_cap' => 1,
            'active' => true,
        ]);

        Discounts::assign(1, $discount->id);

        // Simulate concurrent applications
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            $results[] = Discounts::apply(1, 100);
        }

        // Only one should get the discount
        $discountApplied = 0;
        foreach ($results as $result) {
            if ($result['final'] < 100) {
                $discountApplied++;
            }
        }

        $this->assertEquals(1, $discountApplied);

        // Check usage count
        $userDiscount = UserDiscount::where('user_id', 1)
            ->where('discount_id', $discount->id)
            ->first();
        $this->assertEquals(1, $userDiscount->usage_count);
    }

    /** @test */
    public function max_percentage_cap_enforcement()
    {
        config(['discounts.max_percentage_cap' => 30]);

        $discount1 = Discount::create([
            'name' => '20% off',
            'type' => 'percentage',
            'value' => 20,
            'active' => true,
        ]);

        $discount2 = Discount::create([
            'name' => '15% off',
            'type' => 'percentage',
            'value' => 15,
            'active' => true,
        ]);

        Discounts::assign(1, $discount1->id);
        Discounts::assign(1, $discount2->id);

        $result = Discounts::apply(1, 100);

        // Should be capped at 30% total
        $this->assertEquals(70, $result['final']);
        $this->assertEquals(30, $result['total_discount']);
    }
}
