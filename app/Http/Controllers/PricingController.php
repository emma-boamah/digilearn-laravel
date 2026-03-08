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

    public function show(Request $request)
    {
        $planId = $request->query('planId');

        if ($planId) {
            // Decode the planId
            $decodedPlanId = \App\Services\UrlObfuscator::decode($planId);
            if ($decodedPlanId) {
                $plan = PricingPlan::where('id', $decodedPlanId)->where('is_active', true)->first();
                if ($plan) {
                    $plans = collect([$plan]);
                } else {
                    // Fallback to featured plans if plan not found
                    $plans = PricingPlan::where('is_active', true)
                        ->where('is_featured', true)
                        ->orderBy('sort_order')
                        ->orderBy('price')
                        ->get();
                    if ($plans->isEmpty()) {
                        $plans = PricingPlan::where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('price')
                            ->get();
                    }
                }
            } else {
                // Fallback if decoding fails
                $plans = PricingPlan::where('is_active', true)
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->orderBy('price')
                    ->get();
                if ($plans->isEmpty()) {
                    $plans = PricingPlan::where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('price')
                        ->get();
                }
            }
        } else {
            // Get only featured pricing plans
            $plans = PricingPlan::where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get();

            // If no featured plans, fall back to all active plans
            if ($plans->isEmpty()) {
                $plans = PricingPlan::where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('price')
                    ->get();
            }
        }

        return view('pricing-details', compact('plans'));
    }

    public function getPlanDetails($slug)
    {
        $plan = PricingPlan::where('slug', $slug)->active()->first();

        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        return response()->json([
            'name' => $plan->name,
            'description' => $plan->description,
            'currency' => $plan->currency,
            'price' => $plan->price,
            'features' => is_array($plan->features) ? $plan->features : [],
            'obfuscated_id' => \App\Services\UrlObfuscator::encode($plan->id)
        ]);
    }
}
