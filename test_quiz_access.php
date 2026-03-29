<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$user = User::where('email', 'testgoogleuser_1770171221@example.com')->first();
Auth::login($user);

echo "Simulating request to /quiz for User 4 ({$user->email})\n";
echo "Is Superuser: " . ($user->is_superuser ? 'YES' : 'NO') . "\n";
echo "Has Active Sub: " . ($user->hasActiveSubscription() ? 'YES' : 'NO') . "\n";

$request = Request::create('/quiz', 'GET');
$response = $kernel->handle($request);

echo "Response Status: " . $response->getStatusCode() . "\n";
if ($response->isRedirect()) {
    echo "Redirect URL: " . $response->getTargetUrl() . "\n";
}
