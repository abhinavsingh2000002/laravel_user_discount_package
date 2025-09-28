<?php

namespace Abhinav\Discounts\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Abhinav\Discounts\Models\Discount;
use Abhinav\Discounts\Models\UserDiscount;
use Abhinav\Discounts\Facades\Discounts;

class UserDiscountController extends Controller
{
    public function index()
    {
        $userDiscounts = UserDiscount::with(['discount'])
            ->where('user_id', auth()->id())
            ->get();

        // Get all users for dropdown (if admin)
        $users = \App\Models\User::all();

        return view('discounts::user.index', compact('userDiscounts', 'users'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'discount_id' => 'required|integer|exists:discounts,id',
        ]);

        try {
            $userDiscount = Discounts::assign($request->user_id, $request->discount_id);

            return response()->json([
                'success' => true,
                'message' => 'Discount assigned successfully!',
                'data' => $userDiscount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign discount: ' . $e->getMessage()
            ], 400);
        }
    }

    public function revoke(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'discount_id' => 'required|integer|exists:discounts,id',
        ]);

        try {
            $revoked = Discounts::revoke($request->user_id, $request->discount_id);

            if ($revoked) {
                return response()->json([
                    'success' => true,
                    'message' => 'Discount revoked successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount not found or already revoked'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke discount: ' . $e->getMessage()
            ], 400);
        }
    }

    public function eligible(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        try {
            $eligible = Discounts::eligibleFor($userId);

            return response()->json([
                'success' => true,
                'data' => $eligible
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get eligible discounts: ' . $e->getMessage()
            ], 400);
        }
    }

    public function apply(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = Discounts::apply($request->user_id, $request->amount);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply discounts: ' . $e->getMessage()
            ], 400);
        }
    }

    public function history(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());

        $audits = \Abhinav\Discounts\Models\DiscountAudit::with('discount')
            ->where('user_id', $userId)
            ->orderBy('applied_at', 'desc')
            ->paginate(20);

        return view('discounts::user.history', compact('audits'));
    }

    public function userDiscounts(Request $request, $userId)
    {
        try {
            $userDiscounts = \Abhinav\Discounts\Models\UserDiscount::with(['discount'])
                ->where('user_id', $userId)
                ->get()
                ->map(function ($userDiscount) {
                    return [
                        'id' => $userDiscount->id,
                        'discount' => $userDiscount->discount,
                        'usage_count' => $userDiscount->usage_count,
                        'revoked' => $userDiscount->revoked,
                        'canUse' => $userDiscount->canUse(),
                        'isRevoked' => $userDiscount->isRevoked(),
                        'hasReachedCap' => $userDiscount->hasReachedCap(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $userDiscounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user discounts: ' . $e->getMessage()
            ], 400);
        }
    }

    public function workflow()
    {
        // // Get all users for dropdown
        // $users = \App\Models\User::all();

        return view('discounts::user.complete-workflow');
    }

}
