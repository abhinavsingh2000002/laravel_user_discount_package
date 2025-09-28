<?php

namespace Abhinav\Discounts\Http\Controllers;

use Abhinav\Discounts\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::all();

        return view('discounts::admin.index', compact('discounts'));
    }

    public function create()
    {
        return view('discounts::admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'active' => 'boolean',
            'expires_at' => 'nullable|date',
            'per_user_cap' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active') ? (bool) $request->active : false;

        Discount::create($data);

        return redirect()->route('discounts.index')->with('success', 'Discount created successfully!');
    }

    public function edit(Discount $discount)
    {
        return view('discounts::admin.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'active' => 'boolean',
            'expires_at' => 'nullable|date',
            'per_user_cap' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['active'] = $request->has('active') ? (bool) $request->active : false;

        $discount->update($data);

        return redirect()->route('discounts.index')->with('success', 'Discount updated successfully!');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('discounts.index')->with('success', 'Discount deleted successfully!');
    }
}
