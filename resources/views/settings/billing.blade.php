@extends('settings.layout')

@section('title', 'Billing & Subscriptions')
@section('breadcrumb', 'Billing')

@section('content')
<div class="page-header">
    <h1 class="page-title">Billing & Subscriptions</h1>
    <p class="page-description">Manage your subscription plan, payment methods, and billing history.</p>
</div>

<!-- Current Plan -->
<div class="bg-card rounded-2xl p-6 border mb-8 shadow-sm">
    <div class="flex justify-between items-start mb-8">
        <div class="flex gap-6 items-center">
            <div class="w-14 h-14 bg-blue-50 text-primary rounded-2xl flex items-center justify-center text-2xl">
                <i class="fas fa-gem"></i>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-xl font-bold text-main">
                        {{ $user->currentSubscription ? $user->currentSubscription->pricingPlan->name : 'No Active Plan' }}
                    </h2>
                    @if($user->currentSubscription && $user->currentSubscription->isActive())
                        <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full font-semibold text-xs uppercase">Active</span>
                    @endif
                </div>
                <div class="text-secondary text-sm">
                    @if($user->currentSubscription)
                        Billed {{ strtolower($user->currentSubscription->billing_cycle) }}. Next payment due on {{ $user->currentSubscription->expires_at ? $user->currentSubscription->expires_at->format('M d, Y') : 'N/A' }}.
                        <div class="text-primary font-semibold mt-1">
                            GHS {{ number_format($user->currentSubscription->pricingPlan->price, 2) }} / month
                        </div>
                    @else
                        You are currently on the free version. Upgrade below to access premium features.
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($user->currentSubscription)
        <div>
            <div class="flex justify-between items-center mb-2 text-sm font-semibold text-main">
                <span>Subscription Usage</span>
                <span>{{ $user->currentSubscription->days_remaining }} days remaining</span>
            </div>
            <div class="h-2 w-full bg-body rounded-full overflow-hidden">
                @php
                    $percent = 0;
                    if ($user->currentSubscription && $user->currentSubscription->expires_at) {
                        $totalDays = max(1, $user->currentSubscription->started_at->diffInDays($user->currentSubscription->expires_at));
                        $remaining = $user->currentSubscription->days_remaining;
                        $percent = max(0, min(100, (1 - ($remaining / $totalDays)) * 100));
                    }
                @endphp
                <div id="subscription-progress" class="h-full bg-primary rounded-full transition-all"></div>
            </div>
        </div>
    @endif
</div>

<!-- Available Plans -->
<div class="mb-8">
    <div class="flex justify-between items-end mb-6">
        <h3 class="text-xl font-bold text-main">Available Plans</h3>
    </div>

    <div class="pricing-grid grid gap-6">
        @foreach($availablePlans as $plan)
            <div class="bg-card rounded-2xl p-6 border relative max-w-full {{ $user->currentSubscription && $user->currentSubscription->pricing_plan_id == $plan->id ? 'border-primary' : '' }} {{ $plan->name == 'Essential Plus' ? 'essential-plus-shadow' : '' }}">
                <div class="text-center mb-8">
                    <div class="inline-block bg-primary text-white text-xs font-extrabold px-3 py-1 rounded-full mb-4 uppercase tracking-wider">
                        {{ $plan->name }}
                    </div>
                    <p class="text-sm text-secondary description-box">{{ $plan->description }}</p>
                    <div class="my-6">
                        <span class="text-4xl font-extrabold text-main">GHS {{ number_format($plan->price, 2) }}</span>
                        <span class="text-muted text-sm">/ month</span>
                    </div>
                </div>

                <ul class="list-none mb-8">
                    @php $features = $plan->features ?? []; @endphp
                    @foreach($features as $feature)
                        <li class="flex items-center gap-3 mb-3 text-sm text-secondary">
                            <i class="fas fa-check-circle text-primary text-lg"></i>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @if($user->currentSubscription && $user->currentSubscription->pricing_plan_id == $plan->id)
                    <button disabled class="w-full p-3 rounded-lg border-none bg-body text-muted font-bold cursor-not-allowed">
                        Current Plan
                    </button>
                @else
                    <a href="{{ route('pricing-details', ['planId' => \App\Services\UrlObfuscator::encode($plan->id)]) }}" class="block w-full p-3 rounded-lg border btn-outline-primary bg-white text-primary font-bold cursor-pointer transition-all text-center no-underline hover-primary-bg">
                        {{ $user->currentSubscription && $plan->price > $user->currentSubscription->pricingPlan->price ? 'Upgrade' : 'Switch' }} to {{ explode(' ', $plan->name)[1] ?? $plan->name }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    @media (max-width: 1024px) {
        .pricing-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important;
        }
    }
    .btn-outline-primary { border: 1px solid var(--primary-color) !important; }
    .border-primary { border: 2px solid var(--primary-color); }
    .description-box { height: 3rem; overflow: hidden; }
    .hover-primary-bg:hover { background-color: var(--primary-color); color: white; }
    .bg-primary { background-color: var(--primary-color); }
    .text-white { color: white; }
    .tracking-wider { letter-spacing: 0.05em; }
    .my-6 { margin-top: 1.5rem; margin-bottom: 1.5rem; }
    .list-none { list-style: none; }
    #subscription-progress { width: {{ isset($percent) ? $percent : 0 }}%; }
    .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .essential-plus-shadow { box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1); }
    .min-w-600 { min-width: 600px; }
    .billing-grid-override { grid-template-columns: 2fr 1fr; gap: 2rem; }
</style>
@endpush

<div class="billing-grid grid-1-col grid billing-grid-override">
    <!-- Billing History -->
    <div class="min-w-0">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-main">Billing History</h3>
            <a href="{{ route('settings.billing-history') }}" class="text-primary text-sm font-semibold no-underline">View All</a>
        </div>
        <div class="bg-card rounded-2xl border overflow-hidden">
            <div class="table-responsive">
                <table class="w-full border-collapse text-left min-w-600">
                    <thead class="bg-body">
                        <tr>
                            <th class="p-4 text-xs font-semibold text-muted uppercase">Invoice</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase">Date</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase">Amount</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-bottom">
                                <td class="p-4 text-sm font-semibold text-main">INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="p-4 text-sm text-secondary">{{ $payment->created_at->format('M d, Y') }}</td>
                                <td class="p-4 text-sm font-semibold text-main">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                <td class="p-4">
                                    @if($payment->status === 'success')
                                        <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full font-semibold text-xs">Paid</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="bg-orange-50 text-orange-700 px-3 py-1 rounded-full font-semibold text-xs">Pending</span>
                                    @else
                                        <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full font-semibold text-xs">{{ ucfirst($payment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-muted">No billing history available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Summary -->
            @if($payments->hasPages())
                <div class="p-4 border-top flex justify-center items-center">
                    <div class="custom-pagination">
                        {{ $payments->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .custom-pagination nav {
            display: flex;
            gap: 0.5rem;
        }
        .custom-pagination span, .custom-pagination a {
            padding: 0.4rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            background-color: white;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .custom-pagination .active span {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .custom-pagination a:hover {
            background-color: var(--bg-body);
            color: var(--primary-color);
        }
        .custom-pagination svg {
            width: 1rem;
            height: 1rem;
        }
        .border-bottom { border-bottom: 1px solid var(--border-color); }
        .border-top { border-top: 1px solid var(--border-color); }
        .p-8 { padding: 2rem; }
    </style>
    @endpush

    <!-- Payment Method -->
    <div class="min-w-0">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-main">Payment Method</h3>
        </div>
        
        <div class="bg-card rounded-2xl border p-5 mb-4 max-w-full box-border">
            <!-- Paystack Notice (Enhanced) -->
            <div class="paystack-notice stack-mobile p-5 bg-blue-50 rounded-xl border-blue flex gap-4 items-start max-w-full">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs">
                    <i class="fas fa-shield-alt text-primary text-xl"></i>
                </div>
                <div class="text-wrap text-sm text-blue-800 line-height-1-6 flex-1 min-w-0">
                    <span class="font-bold block mb-1 text-lg text-blue-900">Securely managed by Paystack</span>
                    Your payment information is never stored on our servers. All transactions and payment methods (Cards, Mobile Money, Bank Transfers) are securely handled and encrypted by **Paystack**. You can update your details during your next renewal.
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .border-blue { border: 1px solid #bfdbfe; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .line-height-1-6 { line-height: 1.6; }
    .box-border { box-sizing: border-box; }
    .flex-1 { flex: 1; }
    .min-w-0 { min-width: 0; }
</style>
@endpush
@endsection
