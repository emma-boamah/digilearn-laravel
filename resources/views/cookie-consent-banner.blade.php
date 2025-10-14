@props(['categories' => null])

@php
    $cookieManager = app(\App\Services\CookieManager::class);
    $hasConsent = $cookieManager->hasConsent();
    $consent = $cookieManager->getConsent();
    $categories = $categories ?? $cookieManager->getCategories();
@endphp

@if(!$hasConsent)
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Modern Cookie Banner Styles */
    #cookie-consent-banner {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background-color: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        z-index: 1000;
        max-width: 420px;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .cookie-banner-header {
        padding: 1.25rem 1.25rem 0.75rem;
        border-bottom: 1px solid var(--gray-100);
    }

    .cookie-banner-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .cookie-banner-title-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cookie-banner-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--secondary-blue);
        flex-shrink: 0;
    }

    .cookie-banner-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .cookie-banner-description {
        color: var(--gray-600);
        line-height: 1.5;
        font-size: 0.875rem;
        margin: 0;
    }

    .cookie-banner-body {
        padding: 1rem 1.25rem;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .cookie-banner-body.expanded {
        max-height: 400px;
        overflow-y: auto;
    }

    .cookie-categories {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .cookie-category {
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
    }

    .cookie-checkbox {
        margin-top: 0.125rem;
        width: 1rem;
        height: 1rem;
        color: var(--secondary-blue);
        border: 1px solid var(--gray-300);
        border-radius: 4px;
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
        font-size: 0.8125rem;
        color: var(--gray-600);
        line-height: 1.4;
        margin-top: 0.125rem;
    }

    .cookie-banner-footer {
        padding: 0.75rem 1.25rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .cookie-btn-group {
        display: flex;
        gap: 0.5rem;
    }

    .cookie-btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        white-space: nowrap;
        flex: 1;
    }

    .cookie-btn-primary {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .cookie-btn-primary:hover {
        background-color: var(--secondary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.25);
    }

    .cookie-btn-secondary {
        background-color: var(--gray-100);
        color: var(--gray-700);
    }

    .cookie-btn-secondary:hover {
        background-color: var(--gray-200);
    }

    .cookie-btn-text {
        background: none;
        color: var(--gray-600);
        padding: 0.5rem;
        font-size: 0.8125rem;
        text-align: center;
        width: 100%;
    }

    .cookie-btn-text:hover {
        color: var(--secondary-blue);
        background: none;
    }

    .cookie-toggle-btn {
        background: none;
        border: none;
        color: var(--secondary-blue);
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .cookie-toggle-btn:hover {
        color: var(--secondary-blue-hover);
    }

    .location-notice {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.8125rem;
        color: #1e40af;
        line-height: 1.4;
    }

    @media (max-width: 640px) {
        #cookie-consent-banner {
            bottom: 0;
            right: 0;
            left: 0;
            max-width: 100%;
            border-radius: 12px 12px 0 0;
            margin: 0;
        }

        .cookie-banner-body.expanded {
            max-height: 300px;
        }
    }
</style>

<div id="cookie-consent-banner"
     x-data="cookieConsentBanner()"
     x-show="showBanner">

    <!-- Header -->
    <div class="cookie-banner-header">
        <div class="cookie-banner-title-row">
            <div class="cookie-banner-title-group">
                <svg class="cookie-banner-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 class="cookie-banner-title">Cookie Preferences</h3>
            </div>
        </div>
        <p class="cookie-banner-description">
            We use cookies to enhance your experience. 
            <button @click="toggleDetails()" class="cookie-toggle-btn" type="button">
                <span x-text="showDetails ? 'Hide details' : 'Customize'"></span>
            </button>
        </p>
    </div>

    <!-- Expandable Body -->
    <div class="cookie-banner-body" :class="{ 'expanded': showDetails }">
        <!-- Location Permission Notice -->
        <div x-show="showLocationPrompt" x-transition class="location-notice">
            <strong>Location Permission:</strong> Your browser may ask to share your location for better localized services (optional).
        </div>

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
                            {{ ucfirst($key) }}
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

    <!-- Footer Actions -->
    <div class="cookie-banner-footer">
        <div class="cookie-btn-group">
            <button @click="acceptAll()" class="cookie-btn cookie-btn-primary">
                Accept All
            </button>
            <button @click="showDetails ? acceptSelected() : rejectAll()" class="cookie-btn cookie-btn-secondary">
                <span x-text="showDetails ? 'Save' : 'Reject'"></span>
            </button>
        </div>
        <a href="{{ route('cookies.settings') }}" class="cookie-btn cookie-btn-text">
            Cookie Settings
        </a>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function cookieConsentBanner() {
    return {
        showBanner: false,
        showDetails: false,
        selectedCookies: {
            preference: true,
            analytics: false,
            consent: true
        },
        locationData: {},
        showLocationPrompt: false,

        init() {
            setTimeout(() => {
                this.showBanner = true;
                this.requestLocationPermission();
            }, 500);
        },

        toggleDetails() {
            this.showDetails = !this.showDetails;
        },

        requestLocationPermission() {
            if (navigator.permissions && navigator.permissions.query) {
                navigator.permissions.query({name: 'geolocation'}).then((result) => {
                    if (result.state === 'granted') {
                        this.requestLocation();
                    } else if (result.state === 'prompt') {
                        this.showLocationPrompt = true;
                        this.requestLocation();
                    } else {
                        this.locationData = {};
                    }
                });
            } else {
                this.requestLocation();
            }
        },

        requestLocation() {
            if (navigator.geolocation) {
                console.log('Requesting location permission...');

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        console.log('Location obtained successfully');
                        this.locationData = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        };
                        this.reverseGeocode(position.coords.latitude, position.coords.longitude);
                        this.showLocationPrompt = false;
                    },
                    (error) => {
                        console.log('Location access denied or unavailable:', error.message);
                        this.locationData = {};
                        this.showLocationPrompt = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 300000
                    }
                );
            } else {
                console.log('Geolocation is not supported by this browser');
                this.locationData = {};
                this.showLocationPrompt = false;
            }
        },

        reverseGeocode(lat, lng) {
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

            console.log('Submitting consent data:', consentData);
            console.log('CSRF token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(consentData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    console.log('Consent successful, hiding banner');
                    this.showBanner = false;
                    setTimeout(() => {
                        console.log('Reloading page...');
                        window.location.reload();
                    }, 300);
                } else {
                    console.error('Consent failed:', data);
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