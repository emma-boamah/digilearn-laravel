<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if (!$user) die("No user");
Auth::login($user);

// mock request for csp_nonce
request()->attributes->set('csp_nonce', 'test_nonce_123');

$availablePlans = App\Models\PricingPlan::all();
$payments = App\Models\Payment::paginate(5) ?? null;

echo view('settings.billing', compact('user', 'availablePlans', 'payments'))->render();
