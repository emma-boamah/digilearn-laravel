@extends('layouts.app')

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .checkout-container {
            max-width: 600px;
            margin: 80px auto;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .checkout-icon {
            width: 60px;
            height: 60px;
            background: rgba(29, 155, 240, 0.1);
            color: var(--secondary-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .checkout-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .checkout-subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .order-summary {
            background: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }

        .summary-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .summary-label {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .summary-value {
            font-weight: 600;
            color: var(--text-main);
        }

        .summary-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary-blue);
        }

        .btn-pay {
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 20px;
        }
    </style>

    <div class="container">
        <div class="checkout-container">
            <div class="checkout-icon">
                <svg width="30" height="30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>

            <h1 class="checkout-title">Complete Your Subscription</h1>
            <p class="checkout-subtitle">Your school profile has been created successfully. Complete your payment to
                activate the dashboard.</p>

            <div class="order-summary">
                <div class="summary-item">
                    <span class="summary-label">School Name</span>
                    <span class="summary-value">{{ $school->name }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Subdomain</span>
                    <span class="summary-value">{{ $school->subdomain }}.shoutoutgh.com</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Plan Selected</span>
                    <span class="summary-value">{{ $plan->name }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Billing Cycle</span>
                    <span class="summary-value" style="text-transform: capitalize;">{{ $plan->billing_cycle }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total to Pay</span>
                    <span class="summary-total">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                </div>
            </div>

            <button id="pay-button" class="btn btn-primary btn-pay">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
                Pay Now
            </button>

            <div class="secure-badge">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                Secured by Paystack
            </div>
        </div>
    </div>

    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const payBtn = document.getElementById('pay-button');

            payBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const originalText = payBtn.innerHTML;
                payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Initializing...';
                payBtn.disabled = true;

                // Use the dedicated B2B payment endpoint
                fetch('{{ route('payment.b2b.initiate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        duration: '{{ $school->billing_cycle === 'annual' ? '12month' : 'term' }}'
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Redirect to paystack checkout URL
                            window.location.href = data.authorization_url;
                        } else {
                            alert('Error: ' + (data.message || 'Payment initialization failed.'));
                            payBtn.innerHTML = originalText;
                            payBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('A network error occurred. Please try again.');
                        payBtn.innerHTML = originalText;
                        payBtn.disabled = false;
                    });
            });
        });
    </script>
@endsection