@php
    $hasActiveSubscription = $hasActiveSubscription ?? (auth()->check() && auth()->user()->hasActiveSubscription());
@endphp
<div class="modal-backdrop hidden" id="upgradeModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Upgrade Your Plan</h2>
            <button type="button" class="modal-close" id="closeUpgradeModalBtn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <!-- Pricing card for Essential Plus -->
            <div class="pricing-card featured">
                <div class="pricing-badge">Essential Plus</div>
                <div class="pricing-card-content">
                    <p class="pricing-description">
                        Unlock the full potential of SHS content with the Essential Plus plan.
                    </p>
                    <div class="pricing-price">{{-- Price will be loaded dynamically --}}</div>
                    <ul class="pricing-features">
                        {{-- Features will be loaded dynamically --}}
                    </ul>
                    <a href="{{-- Subscription link will be loaded dynamically --}}" class="pricing-btn">{{ $hasActiveSubscription ? 'Upgrade Now' : 'Get Started' }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .modal-container {
        background: white;
        border-radius: 20px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    .modal-header {
        padding: 1.5rem 1.5rem;
        text-align: center;
        position: relative;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }

    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.5rem;
        color: #94a3b8;
        border: none;
        background: none;
        cursor: pointer;
        border-radius: 8px;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .modal-body {
        padding: 0;
        background-color: transparent;
    }

    /* Embedded Pricing Card Styles */
    .pricing-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        position: relative;
        box-shadow: 0 4px 6px rgb(105, 158, 236);
        transition: transform 0.3s ease;
        width: 100%;
        max-width: 370px;
        margin: 0 auto;
    }

    .pricing-card:hover {
        transform: translateY(-5px);
    }

    .pricing-badge {
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.75rem 2rem;
        background-color: var(--secondary-blue, #2677B8);
        color: var(--white, #ffffff);
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(38, 138, 220, 0.25);
        z-index: 10;
        min-width: 120px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pricing-card-content {
        margin-top: 2rem;
    }

    .pricing-description {
        color: var(--gray-600, #4b5563);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .pricing-price {
        font-size: 2rem;
        font-weight: bold;
        color: var(--gray-900, #111827);
        margin-bottom: 2rem;
    }

    .pricing-features {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        text-align: left;
    }

    .pricing-features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        color: var(--gray-700, #374151);
        font-size: 0.875rem;
    }

    .feature-disabled {
        color: #b0b0b0 !important;
        text-decoration: line-through;
        opacity: 0.7;
    }

    .pricing-features svg {
        color: var(--secondary-blue, #2677B8);
        flex-shrink: 0;
        width: 16px;
        height: 16px;
    }

    .pricing-btn {
        width: 100%;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #bfdbfe;
        color: #1e40af;
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.15rem;
        letter-spacing: 0.05em;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-block;
    }

    .pricing-btn:hover {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    /* CSP compliance: classes to replace inline styles */
    .hidden {
        display: none !important;
    }
    
    .loading-plan-container {
        padding: 4rem 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .modal-loading-spinner {
        border-top-color: var(--secondary-blue, #2677B8);
        width: 40px;
        height: 40px;
    }

    .pricing-card-reset {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
    }

    .error-plan-container {
        padding: 3rem 2rem;
        text-align: center;
    }

    .text-error-red {
        color: #dc2626;
        margin-bottom: 1.5rem;
    }

    .flex-display {
        display: flex !important;
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    (function() {
        const hasActiveSubscription = {{ $hasActiveSubscription ? 'true' : 'false' }};
        const upgradeModal = document.getElementById('upgradeModal');
        const closeBtn = document.getElementById('closeUpgradeModalBtn');

        window.openUpgradeModal = function(planSlug) {
            console.log('Opening upgrade modal for:', planSlug);
            // Show loading state in modal
            const pricingCard = upgradeModal.querySelector('.pricing-card');
            if(!pricingCard) return;

            const originalContent = pricingCard.innerHTML;
            pricingCard.innerHTML = '<div class="loading-plan-container"><div class="loading-spinner modal-loading-spinner"></div><p>Loading plan details...</p></div>';
            upgradeModal.classList.remove('hidden');
            upgradeModal.classList.add('flex-display');
            document.body.style.overflow = 'hidden';

            // Fetch plan details
            fetch(`/pricing-plan/${planSlug}`)
                .then(response => {
                    if (!response.ok) throw new Error('Plan not found');
                    return response.json();
                })
                .then(data => {
                    const btnText = hasActiveSubscription ? 'Upgrade Now' : 'Get Started';
                    pricingCard.innerHTML = `
                        <div class="pricing-card pricing-card-reset">
                            <div class="pricing-badge">${data.name || planSlug}</div>
                            <div class="pricing-card-content">
                                <p class="pricing-description">${data.description || 'Comprehensive learning package with access to platform features.'}</p>
                                <div class="pricing-price">${data.currency || 'GHS'} ${parseFloat(data.price || 0).toFixed(2)}</div>
                                <ul class="pricing-features">
                                    ${Array.isArray(data.features) && data.features.length > 0 
                                        ? data.features.map(feature => `<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>${feature}</li>`).join('') 
                                        : `<li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>Access to ${data.name || planSlug} features</li>`}
                                </ul>
                                <a href="/pricing/pricing-details?planId=${data.obfuscated_id}" class="pricing-btn">${btnText}</a>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching pricing plan:', error);
                    pricingCard.innerHTML = `
                        <div class="error-plan-container">
                            <p class="text-error-red">Sorry, we could not load the plan details. Please try again later.</p>
                            <a href="/pricing" class="pricing-btn">View All Pricing Plans</a>
                        </div>
                    `;
                });
        };

        window.closeUpgradeModal = function() {
            upgradeModal.classList.add('hidden');
            upgradeModal.classList.remove('flex-display');
            document.body.style.overflow = '';
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', window.closeUpgradeModal);
        }

        // Close on backdrop click
        upgradeModal.addEventListener('click', function(e) {
            if (e.target === upgradeModal) {
                window.closeUpgradeModal();
            }
        });
    })();
</script>
