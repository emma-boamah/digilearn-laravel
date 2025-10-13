@props(['categories' => null])

@php
    $cookieManager = app(\App\Services\CookieManager::class);
    $hasConsent = $cookieManager->hasConsent();
    $consent = $cookieManager->getConsent();
    $categories = $categories ?? $cookieManager->getCategories();
@endphp

@if(!$hasConsent)
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Cookie Banner Styles */
    #cookie-consent-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: var(--white);
        border-top: 1px solid var(--gray-300);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        padding: 1.5rem 1rem;
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .cookie-banner-container {
        max-width: var(--container-max-width);
        margin: 0 auto;
    }

    .cookie-banner-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    @media (min-width: 1024px) {
        .cookie-banner-content {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
        }
    }

    .cookie-banner-main {
        flex: 1;
    }

    .cookie-banner-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .cookie-banner-icon {
        width: 1.5rem;
        height: 1.5rem;
        color: var(--secondary-blue);
        margin-right: 0.75rem;
        flex-shrink: 0;
    }

    .cookie-banner-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .cookie-banner-description {
        color: var(--gray-600);
        line-height: 1.6;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .cookie-categories {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .cookie-category {
        display: flex;
        align-items: flex-start;
    }

    .cookie-checkbox {
        margin-top: 0.25rem;
        width: 1rem;
        height: 1rem;
        color: var(--secondary-blue);
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        flex-shrink: 0;
    }

    .cookie-checkbox:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .cookie-checkbox:focus {
        outline: 2px solid var(--secondary-blue);
        outline-offset: 2px;
    }

    .cookie-category-info {
        margin-left: 0.75rem;
        flex: 1;
    }

    .cookie-category-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-900);
        cursor: pointer;
        display: block;
        margin-bottom: 0.125rem;
    }

    .cookie-category-required {
        font-size: 0.75rem;
        color: var(--gray-500);
        font-weight: 400;
    }

    .cookie-category-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    .cookie-banner-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-width: 200px;
    }

    @media (min-width: 640px) {
        .cookie-banner-actions {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }

    @media (min-width: 1024px) {
        .cookie-banner-actions {
            flex-direction: column;
            margin-left: 1.5rem;
        }
    }

    .cookie-btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: var(--border-radius-lg);
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        white-space: nowrap;
    }

    .cookie-btn-primary {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .cookie-btn-primary:hover {
        background-color: var(--secondary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.3);
    }

    .cookie-btn-success {
        background-color: #16a34a;
        color: var(--white);
    }

    .cookie-btn-success:hover {
        background-color: #15803d;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .cookie-btn-secondary {
        background-color: var(--gray-600);
        color: var(--white);
    }

    .cookie-btn-secondary:hover {
        background-color: var(--gray-900);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 85, 99, 0.3);
    }

    .cookie-btn-outline {
        background-color: var(--white);
        color: var(--gray-600);
        border: 1px solid var(--gray-300);
    }

    .cookie-btn-outline:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-600);
    }

    @media (max-width: 640px) {
        #cookie-consent-banner {
            padding: 1rem;
        }

        .cookie-banner-title {
            font-size: 1rem;
        }

        .cookie-banner-description {
            font-size: 0.875rem;
        }

        .cookie-btn {
            width: 100%;
        }
    }
</style>

<div id="cookie-consent-banner"
     x-data="cookieConsentBanner()"
     x-show="showBanner">

    <div class="cookie-banner-container">
        <div class="cookie-banner-content">

            <!-- Main Content -->
            <div class="cookie-banner-main">
                <div class="cookie-banner-header">
                    <svg class="cookie-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h3 class="cookie-banner-title">Cookie Preferences</h3>
                </div>

                <p class="cookie-banner-description">
                    We use cookies to enhance your experience, analyze site usage, and assist in our marketing efforts.
                    You can choose which types of cookies to accept.
                </p>

                <!-- Cookie Categories -->
                <div class="cookie-categories">
                    @foreach($categories as $key => $description)
                        <div class="cookie-category">
                            <input type="checkbox"
                                   id="cookie-{{ $key }}"
                                   class="cookie-checkbox"
                                   x-model="selectedCookies.{{ $key }}"
                                   @if($key === 'preference' || $key === 'consent') disabled checked @endif>
                            <div class="cookie-category-info">
                                <label for="cookie-{{ $key }}" class="cookie-category-label">
                                    {{ ucfirst($key) }} Cookies
                                    @if($key === 'preference' || $key === 'consent')
                                        <span class="cookie-category-required">(Required)</span>
                                    @endif
                                </label>
                                <p class="cookie-category-description">{{ $description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="cookie-banner-actions">
                <button @click="acceptSelected()" class="cookie-btn cookie-btn-primary">
                    Accept Selected
                </button>
                <button @click="acceptAll()" class="cookie-btn cookie-btn-success">
                    Accept All
                </button>
                <button @click="rejectAll()" class="cookie-btn cookie-btn-secondary">
                    Reject Non-Essential
                </button>
                <a href="{{ route('cookies.settings') }}" class="cookie-btn cookie-btn-outline">
                    Cookie Settings
                </a>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function cookieConsentBanner() {
    return {
        showBanner: false,
        selectedCookies: {
            preference: true,
            analytics: false,
            consent: true
        },
        locationData: {},

        init() {
            setTimeout(() => {
                this.showBanner = true;
                this.requestLocation();
            }, 500);
        },

        requestLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.locationData = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };
                        this.reverseGeocode(position.coords.latitude, position.coords.longitude);
                    },
                    (error) => {
                        console.log('Location access denied or unavailable:', error.message);
                        // Continue without location data
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000 // 5 minutes
                    }
                );
            }
        },

        reverseGeocode(lat, lng) {
            // Simple reverse geocoding using a free API (you might want to use a paid service for production)
            fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
                .then(response => response.json())
                .then(data => {
                    this.locationData = {
                        ...this.locationData,
                        country: data.countryName || null,
                        city: data.city || null,
                        region: data.principalSubdivision || null
                    };
                })
                .catch(error => {
                    console.log('Reverse geocoding failed:', error);
                    // Continue with just coordinates
                });
        },

        acceptAll() {
            this.selectedCookies = {
                preference: true,
                analytics: true,
                consent: true
            };
            this.submitConsent('/cookies/accept-all');
        },

        acceptSelected() {
            this.submitConsent();
        },

        rejectAll() {
            this.selectedCookies = {
                preference: true,
                analytics: false,
                consent: true
            };
            this.submitConsent('/cookies/reject-all');
        },

        submitConsent(url = '/cookies/consent') {
            const consentData = {
                ...this.selectedCookies,
                ...this.locationData
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(consentData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showBanner = false;
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error setting cookie consent:', error);
            });
        }
    }
}
</script>
@endif