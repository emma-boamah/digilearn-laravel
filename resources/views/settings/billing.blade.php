@extends('settings.layout')

@section('title', 'Billing & Subscriptions')
@section('breadcrumb', 'Billing')

@section('content')
<div class="page-header">
    <h1 class="page-title">Billing & Subscriptions</h1>
    <p class="page-description">Manage your subscription plan, payment methods, and billing history.</p>
</div>

<!-- Current Plan -->
<div style="background-color: var(--bg-card); border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 2rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <div style="width: 56px; height: 56px; background-color: #eff6ff; color: var(--primary-color); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fas fa-gem"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">
                        {{ $user->currentSubscription ? $user->currentSubscription->pricingPlan->name : 'No Active Plan' }}
                    </h2>
                    @if($user->currentSubscription && $user->currentSubscription->isActive())
                        <span style="background-color: #dcfce7; color: #166534; padding: 0.125rem 0.625rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">Active</span>
                    @endif
                </div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">
                    @if($user->currentSubscription)
                        Billed {{ strtolower($user->currentSubscription->billing_cycle) }}. Next payment due on {{ $user->currentSubscription->expires_at ? $user->currentSubscription->expires_at->format('M d, Y') : 'N/A' }}.
                        <div style="color: var(--primary-color); font-weight: 600; margin-top: 0.25rem;">
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--text-main);">
                <span>Subscription Usage</span>
                <span>{{ $user->currentSubscription->days_remaining }} days remaining</span>
            </div>
            <div style="height: 8px; width: 100%; background-color: var(--bg-body); border-radius: 9999px; overflow: hidden;">
                @php
                    $percent = 0;
                    if ($user->currentSubscription && $user->currentSubscription->expires_at) {
                        $totalDays = max(1, $user->currentSubscription->started_at->diffInDays($user->currentSubscription->expires_at));
                        $remaining = $user->currentSubscription->days_remaining;
                        $percent = max(0, min(100, (1 - ($remaining / $totalDays)) * 100));
                    }
                @endphp
                <div style="width: {{ $percent }}%; height: 100%; background-color: var(--primary-color); border-radius: 9999px;"></div>
            </div>
        </div>
    @endif
</div>

<!-- Available Plans -->
<div style="margin-bottom: 3rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Available Plans</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        @foreach($availablePlans as $plan)
            <div style="background-color: var(--bg-card); border-radius: 1rem; border: {{ $user->currentSubscription && $user->currentSubscription->pricing_plan_id == $plan->id ? '2px solid var(--primary-color)' : '1px solid var(--border-color)' }}; padding: 1.5rem; position: relative; {{ $plan->name == 'Essential Plus' ? 'box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1);' : '' }}">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="display: inline-block; background-color: var(--primary-color); color: white; font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $plan->name }}
                    </div>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); height: 3rem; overflow: hidden;">{{ $plan->description }}</p>
                    <div style="margin: 1.5rem 0;">
                        <span style="font-size: 2.25rem; font-weight: 800; color: var(--text-main);">GHS {{ number_format($plan->price, 2) }}</span>
                        <span style="color: var(--text-muted); font-size: 0.875rem;">/ month</span>
                    </div>
                </div>

                <ul style="list-style: none; margin-bottom: 2rem;">
                    @php $features = $plan->features ?? []; @endphp
                    @foreach($features as $feature)
                        <li style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                            <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1rem;"></i>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                @if($user->currentSubscription && $user->currentSubscription->pricing_plan_id == $plan->id)
                    <button disabled style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: none; background-color: var(--bg-body); color: var(--text-muted); font-weight: 700; cursor: not-allowed;">
                        Current Plan
                    </button>
                @else
                    <a href="{{ route('pricing-details', ['planId' => \App\Services\UrlObfuscator::encode($plan->id)]) }}" style="display: block; width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--primary-color); background-color: white; color: var(--primary-color); font-weight: 700; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none;">
                        {{ $user->currentSubscription && $plan->price > $user->currentSubscription->pricingPlan->price ? 'Upgrade' : 'Switch' }} to {{ explode(' ', $plan->name)[1] ?? $plan->name }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Billing History -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main);">Billing History</h3>
            <a href="{{ route('settings.billing-history') }}" style="color: var(--primary-color); font-size: 0.875rem; font-weight: 600; text-decoration: none;">View All</a>
        </div>
        <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: var(--bg-body);">
                    <tr>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Invoice</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Date</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Amount</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: var(--text-main);">INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td style="padding: 1rem; font-size: 0.875rem; color: var(--text-secondary);">{{ $payment->created_at->format('M d, Y') }}</td>
                            <td style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: var(--text-main);">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            <td style="padding: 1rem;">
                                <span style="background-color: #dcfce7; color: #166534; padding: 0.125rem 0.625rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">Paid</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">No billing history available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Method -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main);">Payment Method</h3>
        </div>
        
        <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); padding: 1.5rem; margin-bottom: 1rem;">
            <!-- Paystack Notice (Enhanced) -->
            <div style="padding: 1.25rem; background-color: #eff6ff; border-radius: 0.75rem; border: 1px solid #bfdbfe; display: flex; gap: 1rem; align-items: flex-start;">
                <div style="width: 40px; height: 40px; background-color: white; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="fas fa-shield-alt" style="color: var(--primary-color); font-size: 1.25rem;"></i>
                </div>
                <div style="font-size: 0.875rem; color: #1e40af; line-height: 1.6;">
                    <span style="font-weight: 700; display: block; margin-bottom: 0.25rem; font-size: 1rem; color: #1e3a8a;">Securely managed by Paystack</span>
                    Your payment information is never stored on our servers. All transactions and payment methods (Cards, Mobile Money, Bank Transfers) are securely handled and encrypted by **Paystack**. You can update your details during your next renewal.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
