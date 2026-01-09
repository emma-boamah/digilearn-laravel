<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plan = App\Models\PricingPlan::first();

if ($plan) {
    echo "Plan: " . $plan->name . "\n";
    echo "Price: " . $plan->price . "\n";
    echo "Discount tiers: " . json_encode($plan->discount_tiers) . "\n";
    echo "Price for 1 month: " . $plan->getPriceForDuration('month') . "\n";
    echo "Price for 3 months: " . $plan->getPriceForDuration('3month') . "\n";
    echo "Price for 6 months: " . $plan->getPriceForDuration('6month') . "\n";
    echo "Price for 12 months: " . $plan->getPriceForDuration('12month') . "\n";
} else {
    echo "No plan found\n";
}