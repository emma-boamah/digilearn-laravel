@extends('settings.layout')

@section('title', 'Credit Wallet')
@section('breadcrumb', 'Wallet')

@section('content')
<div class="page-header">
    <h1 class="page-title">Credit Wallet</h1>
    <p class="page-description">Top up your credits to book personalised 1-on-1 tutor sessions.</p>
</div>

<!-- Balance Card -->
<div class="wallet-balance-card">
    <div class="wallet-balance-inner">
        <div class="wallet-balance-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="wallet-balance-info">
            <div class="wallet-balance-label">Available Balance</div>
            <div class="wallet-balance-amount">{{ number_format($creditBalance, 2) }}</div>
            <div class="wallet-balance-sub">Credits · 1 Credit = GHS 1.00</div>
        </div>
    </div>
</div>

<!-- Top Up Form -->
<div class="wallet-topup-section">
    <h3 class="wallet-section-title">
        <i class="fas fa-plus-circle" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
        Top Up Credits
    </h3>

    <div class="wallet-preset-grid">
        <button type="button" class="wallet-preset-btn" data-amount="20" onclick="selectPreset(this)">
            <span class="wallet-preset-amount">GHS 20</span>
            <span class="wallet-preset-credits">20 Credits</span>
        </button>
        <button type="button" class="wallet-preset-btn" data-amount="50" onclick="selectPreset(this)">
            <span class="wallet-preset-amount">GHS 50</span>
            <span class="wallet-preset-credits">50 Credits</span>
        </button>
        <button type="button" class="wallet-preset-btn wallet-preset-popular" data-amount="100" onclick="selectPreset(this)">
            <span class="wallet-preset-badge">Most Popular</span>
            <span class="wallet-preset-amount">GHS 100</span>
            <span class="wallet-preset-credits">100 Credits</span>
        </button>
        <button type="button" class="wallet-preset-btn" data-amount="200" onclick="selectPreset(this)">
            <span class="wallet-preset-amount">GHS 200</span>
            <span class="wallet-preset-credits">200 Credits</span>
        </button>
        <button type="button" class="wallet-preset-btn" data-amount="500" onclick="selectPreset(this)">
            <span class="wallet-preset-amount">GHS 500</span>
            <span class="wallet-preset-credits">500 Credits</span>
        </button>
    </div>

    <div class="wallet-custom-amount">
        <label for="custom_amount" class="wallet-custom-label">Or enter a custom amount (GHS 10 – 5,000)</label>
        <div class="wallet-custom-input-group">
            <span class="wallet-custom-prefix">GHS</span>
            <input type="number" id="custom_amount" name="amount" min="10" max="5000" step="1" placeholder="0.00" class="wallet-custom-input" oninput="clearPresetSelection()">
        </div>
    </div>

    <button type="button" id="topupBtn" class="wallet-topup-btn" onclick="initiateTopup()" disabled>
        <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>
        <span id="topupBtnText">Select an amount to continue</span>
    </button>

    <p class="wallet-secure-notice">
        <i class="fas fa-shield-alt"></i>
        Payments are securely processed by Paystack. We never store your card or mobile money details.
    </p>
</div>

<!-- How Credits Work -->
<div class="wallet-info-section">
    <h3 class="wallet-section-title">
        <i class="fas fa-info-circle" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
        How Credits Work
    </h3>
    <div class="wallet-info-grid">
        <div class="wallet-info-item">
            <div class="wallet-info-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <strong>1 Credit = GHS 1.00</strong>
                <p>Credits map directly to Ghana Cedis at a 1:1 ratio.</p>
            </div>
        </div>
        <div class="wallet-info-item">
            <div class="wallet-info-icon" style="background: #dcfce7; color: #16a34a;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <strong>Book Tutor Sessions</strong>
                <p>Credits are deducted when you book a 1-on-1 session with a tutor.</p>
            </div>
        </div>
        <div class="wallet-info-item">
            <div class="wallet-info-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <strong>Secure Escrow</strong>
                <p>Credits are held in escrow until your session is marked complete.</p>
            </div>
        </div>
        <div class="wallet-info-item">
            <div class="wallet-info-icon" style="background: #f3e8ff; color: #9333ea;">
                <i class="fas fa-infinity"></i>
            </div>
            <div>
                <strong>No Expiry</strong>
                <p>Your unused credits never expire. Use them whenever you're ready.</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Top-Up History -->
@if($recentTopups->count() > 0)
<div class="wallet-history-section">
    <h3 class="wallet-section-title">
        <i class="fas fa-history" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
        Recent Top-Ups
    </h3>
    <div class="bg-card rounded-2xl border overflow-hidden">
        <table class="wallet-history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTopups as $topup)
                    <tr>
                        <td>{{ $topup->paid_at ? $topup->paid_at->format('M d, Y · H:i') : $topup->created_at->format('M d, Y · H:i') }}</td>
                        <td style="font-family: monospace; font-size: 0.8rem;">{{ Str::limit($topup->reference, 20) }}</td>
                        <td><strong>GHS {{ number_format($topup->amount, 2) }}</strong></td>
                        <td><span class="wallet-status-badge wallet-status-success">Completed</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .wallet-balance-card {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        border-radius: 1.25rem;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
    }
    .wallet-balance-inner {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .wallet-balance-icon {
        width: 4rem;
        height: 4rem;
        background: rgba(255,255,255,0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }
    .wallet-balance-label {
        font-size: 0.875rem;
        opacity: 0.85;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .wallet-balance-amount {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .wallet-balance-sub {
        font-size: 0.8rem;
        opacity: 0.7;
        margin-top: 0.25rem;
    }

    .wallet-topup-section, .wallet-info-section, .wallet-history-section {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 1.25rem;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
    }
    .wallet-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
    }

    .wallet-preset-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .wallet-preset-btn {
        position: relative;
        background: var(--bg-body);
        border: 2px solid var(--border-color);
        border-radius: 0.875rem;
        padding: 1.25rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        font-family: inherit;
    }
    .wallet-preset-btn:hover {
        border-color: var(--primary-color);
        background: #eff6ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }
    .wallet-preset-btn.selected {
        border-color: var(--primary-color);
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .wallet-preset-amount {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-main);
    }
    .wallet-preset-credits {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .wallet-preset-popular {
        border-color: var(--primary-color);
    }
    .wallet-preset-badge {
        position: absolute;
        top: -0.6rem;
        background: var(--primary-color);
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .wallet-custom-amount {
        margin-bottom: 1.5rem;
    }
    .wallet-custom-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary, #4b5563);
        margin-bottom: 0.5rem;
    }
    .wallet-custom-input-group {
        display: flex;
        align-items: center;
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .wallet-custom-input-group:focus-within {
        border-color: var(--primary-color);
    }
    .wallet-custom-prefix {
        padding: 0.75rem 1rem;
        background: var(--bg-body);
        color: var(--text-secondary, #4b5563);
        font-weight: 700;
        font-size: 0.9rem;
        border-right: 2px solid var(--border-color);
    }
    .wallet-custom-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: none;
        outline: none;
        font-size: 1rem;
        font-weight: 600;
        background: var(--bg-card);
        color: var(--text-main);
        font-family: inherit;
    }
    .wallet-custom-input::placeholder {
        color: var(--text-muted);
    }

    .wallet-topup-btn {
        width: 100%;
        padding: 1rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 0.875rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: inherit;
    }
    .wallet-topup-btn:hover:not(:disabled) {
        background: var(--primary-hover, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .wallet-topup-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .wallet-topup-btn.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .wallet-secure-notice {
        text-align: center;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 1rem;
    }
    .wallet-secure-notice i {
        margin-right: 0.3rem;
        color: #10b981;
    }

    .wallet-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
    }
    .wallet-info-item {
        display: flex;
        gap: 0.875rem;
        align-items: flex-start;
        padding: 1rem;
        background: var(--bg-body);
        border-radius: 0.875rem;
    }
    .wallet-info-item strong {
        display: block;
        font-size: 0.875rem;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }
    .wallet-info-item p {
        font-size: 0.8rem;
        color: var(--text-secondary, #4b5563);
        margin: 0;
        line-height: 1.4;
    }
    .wallet-info-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .wallet-history-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.875rem;
    }
    .wallet-history-table th {
        padding: 0.875rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        background: var(--bg-body);
        border-bottom: 1px solid var(--border-color);
    }
    .wallet-history-table td {
        padding: 0.875rem 1.25rem;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
    }
    .wallet-status-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .wallet-status-success {
        background: #dcfce7;
        color: #16a34a;
    }

    @media (max-width: 640px) {
        .wallet-balance-amount { font-size: 2rem; }
        .wallet-balance-icon { width: 3rem; height: 3rem; font-size: 1.25rem; }
        .wallet-preset-grid { grid-template-columns: repeat(2, 1fr); }
        .wallet-info-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    let selectedAmount = null;

    function selectPreset(btn) {
        document.querySelectorAll('.wallet-preset-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        selectedAmount = parseFloat(btn.dataset.amount);
        document.getElementById('custom_amount').value = '';
        updateTopupButton();
    }

    function clearPresetSelection() {
        document.querySelectorAll('.wallet-preset-btn').forEach(b => b.classList.remove('selected'));
        const customVal = parseFloat(document.getElementById('custom_amount').value);
        selectedAmount = (customVal >= 10 && customVal <= 5000) ? customVal : null;
        updateTopupButton();
    }

    function updateTopupButton() {
        const btn = document.getElementById('topupBtn');
        const text = document.getElementById('topupBtnText');
        if (selectedAmount && selectedAmount >= 10) {
            btn.disabled = false;
            text.textContent = 'Top Up GHS ' + selectedAmount.toFixed(2);
        } else {
            btn.disabled = true;
            text.textContent = 'Select an amount to continue';
        }
    }

    function initiateTopup() {
        if (!selectedAmount || selectedAmount < 10) return;

        const btn = document.getElementById('topupBtn');
        const text = document.getElementById('topupBtnText');
        btn.classList.add('loading');
        text.textContent = 'Initializing payment...';

        fetch('{{ route("wallet.topup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ amount: selectedAmount }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.authorization_url) {
                window.location.href = data.authorization_url;
            } else {
                alert(data.message || 'Payment initialization failed. Please try again.');
                btn.classList.remove('loading');
                text.textContent = 'Top Up GHS ' + selectedAmount.toFixed(2);
            }
        })
        .catch(err => {
            console.error('Top-up error:', err);
            alert('An error occurred. Please try again.');
            btn.classList.remove('loading');
            text.textContent = 'Top Up GHS ' + selectedAmount.toFixed(2);
        });
    }
</script>
@endsection
