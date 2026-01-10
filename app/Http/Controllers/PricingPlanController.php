<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\PricingPlan;

class PricingPlanController extends Controller
{
    /**
     * Display a listing of pricing plans for admin.
     */
    public function index(Request $request)
    {
        $query = PricingPlan::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }

        // Filter by status
        if ($request->filled('is_active') && $request->is_active != '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by period
        if ($request->filled('period') && $request->period != '') {
            $query->where('period', $request->period);
        }

        $pricingPlans = $query->orderBy('sort_order')->orderBy('price')->paginate(10);

        // Statistics
        $totalPlans = PricingPlan::count();
        $activePlans = PricingPlan::where('is_active', true)->count();
        $inactivePlans = PricingPlan::where('is_active', false)->count();

        return view('admin.pricing.index', compact(
            'pricingPlans',
            'totalPlans',
            'activePlans',
            'inactivePlans'
        ));
    }

    /**
     * Show the form for creating a new pricing plan.
     */
    public function create()
    {
        return view('admin.pricing.create');
    }

    /**
     * Store a newly created pricing plan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans',
            'price' => 'required|numeric|min:0|unique:pricing_plans',
            'currency' => 'required|string|max:3',
            'period' => 'required|in:monthly,yearly,lifetime,one-time',
            'description' => 'nullable|string',
            'features' => 'required|array',
            'features.*' => 'string|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        PricingPlan::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'currency' => $request->currency,
            'period' => $request->period,
            'description' => $request->description,
            'features' => $request->features,
            'discount_tiers' => $request->discount_tiers,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing plan created successfully!');
    }

    /**
     * Display the specified pricing plan.
     */
    public function show(PricingPlan $pricingPlan)
    {
        return view('admin.pricing.show', compact('pricingPlan'));
    }

    /**
     * Show the form for editing the pricing plan.
     */
    public function edit(PricingPlan $pricingPlan)
    {
        return view('admin.pricing.edit', compact('pricingPlan'));
    }

    /**
     * Update the specified pricing plan.
     */
    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans,slug,' . $pricingPlan->id,
            'price' => 'required|numeric|min:0|unique:pricing_plans,price,' . $pricingPlan->id,
            'currency' => 'required|string|max:3',
            'period' => 'required|in:monthly,yearly,lifetime,one-time',
            'description' => 'nullable|string',
            'features' => 'required|array',
            'features.*' => 'string|max:255',
            'discount_tiers' => 'nullable|array',
            'discount_tiers.*.duration_months' => 'required_with:discount_tiers|integer|min:1',
            'discount_tiers.*.discount_percentage' => 'required_with:discount_tiers|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $pricingPlan->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'currency' => $request->currency,
            'period' => $request->period,
            'description' => $request->description,
            'features' => $request->features,
            'discount_tiers' => $request->discount_tiers,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing plan updated successfully!');
    }

    /**
     * Remove the specified pricing plan.
     */
    public function destroy(PricingPlan $pricingPlan)
    {
        $pricingPlan->delete();

        return redirect()->route('admin.pricing.index')->with('success', 'Pricing plan deleted successfully!');
    }

    /**
     * Toggle pricing plan active status.
     */
    public function toggleActive(PricingPlan $pricingPlan)
    {
        $pricingPlan->is_active = !$pricingPlan->is_active;
        $pricingPlan->save();

        return back()->with('success', 'Pricing plan status updated.');
    }

    /**
     * Toggle pricing plan featured status.
     */
    public function toggleFeatured(PricingPlan $pricingPlan)
    {
        $pricingPlan->is_featured = !$pricingPlan->is_featured;
        $pricingPlan->save();

        return back()->with('success', 'Pricing plan featured status updated.');
    }

    /**
     * Update sort order of pricing plans.
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|integer|exists:pricing_plans,id',
            'plans.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->plans as $planData) {
            PricingPlan::where('id', $planData['id'])->update(['sort_order' => $planData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Sort order updated successfully.']);
    }
}