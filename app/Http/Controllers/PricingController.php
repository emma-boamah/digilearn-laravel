<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricingPlan;

class PricingController extends Controller
{
    public function index()
    {
        $pricingPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('pricing', compact('pricingPlans'));
    }

    public function show()
    {
        // Get only featured pricing plans
        $featuredPlans = PricingPlan::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        // If no featured plans, fall back to all active plans
        if ($featuredPlans->isEmpty()) {
            $featuredPlans = PricingPlan::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get();
        }

        // Transform to the expected array format for the view
        $pricingPlans = [];
        foreach ($featuredPlans as $plan) {
            $pricingPlans[$plan->slug] = [
                'name' => $plan->name,
                'price' => $plan->currency . ' ' . number_format($plan->price, 2),
                'period' => '7 days free trial',
                'features' => $plan->features ?? ['Access to ' . $plan->name . ' features']
            ];
        }

        return view('pricing-details', compact('pricingPlans'));
    }
}
