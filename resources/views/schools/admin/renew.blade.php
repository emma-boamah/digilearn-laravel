@extends('schools.admin.layout')

@section('title', 'Renew Subscription')

@section('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .renew-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
        max-width: 600px;
        margin: 0 auto;
    }

    .renew-header {
        text-align: center;
        margin-bottom: 32px;
    }

    .renew-header h2 {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }

    .renew-header p {
        color: var(--text-muted);
    }

    .summary-box {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 32px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 0.95rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
        padding-top: 12px;
        border-top: 1px solid var(--border);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .info-alert {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 32px;
        display: flex;
        gap: 12px;
    }

    /* Loader for Paystack */
    .btn-loader {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-bottom-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
    <div class="renew-card">
        <div class="renew-header">
            <h2>Renew Your Subscription</h2>
            <p>Ensure uninterrupted access for your students and teachers.</p>
        </div>

        <div class="info-alert">
            <i class="fas fa-info-circle" style="margin-top: 2px;"></i>
            <div>
                Your renewal is calculated based on your current number of enrolled students ({{ $usedSeats }}). 
                If you plan to add more students next term, you may do so after renewal up to your plan limit ({{ $school->max_seats }}).
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-row">
                <span>Plan</span>
                <span>{{ ucfirst($school->plan_tier) }} Plan</span>
            </div>
            <div class="summary-row">
                <span>Active Student Seats</span>
                <span>{{ $usedSeats }}</span>
            </div>
            <div class="summary-row">
                <span>Price per Seat ({{ ucfirst($school->billing_cycle) }})</span>
                <span>GH₵{{ number_format($pricePerSeat, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Amount Due</span>
                <span>GH₵{{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>

        @if($totalAmount > 0)
            <button type="button" id="payButton" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 1.05rem;">
                <span class="btn-loader" id="payLoader"></span>
                Pay GH₵{{ number_format($totalAmount, 2) }} with Paystack
            </button>
            <p style="text-align: center; margin-top: 16px; font-size: 0.8rem; color: var(--text-muted);">
                <i class="fas fa-lock"></i> Secured by Paystack
            </p>
        @else
            <div style="text-align: center; color: var(--text-muted);">
                Cannot renew at this time.
            </div>
        @endif
        
        <div style="text-align: center; margin-top: 24px;">
            <a href="{{ route('school.admin.billing') }}" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none;">
                Cancel and return to billing
            </a>
        </div>
    </div>
@endsection

@section('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.addEventListener('click', function() {
                const btn = this;
                const loader = document.getElementById('payLoader');
                
                btn.disabled = true;
                loader.style.display = 'inline-block';
                
                // For B2B Renewal, we use the dedicated B2B payment endpoint
                fetch('{{ route('payment.b2b.initiate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        duration: '{{ $school->billing_cycle === 'annual' ? '12month' : 'term' }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.authorization_url) {
                        window.location.href = data.authorization_url;
                    } else {
                        throw new Error(data.message || 'Payment initialization failed');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                    btn.disabled = false;
                    loader.style.display = 'none';
                });
            });
        }
    });
</script>
@endsection
