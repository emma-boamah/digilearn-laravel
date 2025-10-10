@extends('layouts.app')

@section('title', 'Cookie Settings - DigiLearn')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .settings-container {
        min-height: 100vh;
        background-color: var(--gray-50);
        padding: 3rem 0;
    }

    .settings-wrapper {
        max-width: 56rem;
        margin: 0 auto;
        padding: 0 1rem;
    }

    @media (min-width: 640px) {
        .settings-wrapper {
            padding: 0 1.5rem;
        }
    }

    @media (min-width: 1024px) {
        .settings-wrapper {
            padding: 0 2rem;
        }
    }

    .settings-card {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-300);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .settings-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .settings-icon {
        width: 2rem;
        height: 2rem;
        color: var(--secondary-blue);
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .settings-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .settings-subtitle {
        color: var(--gray-600);
        margin-top: 0.25rem;
        font-size: 0.95rem;
    }

    .status-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .status-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .status-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-lg);
    }

    .status-indicator {
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .status-indicator.enabled {
        background-color: #16a34a;
    }

    .status-indicator.disabled {
        background-color: var(--gray-300);
    }

    .status-info {
        margin-left: 0.75rem;
    }

    .status-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-900);
        margin: 0;
    }

    .status-label {
        font-size: 0.75rem;
        color: var(--gray-500);
        margin: 0;
    }

    .category-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .category-card {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-300);
        padding: 1.5rem;
    }

    .category-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .category-content {
        flex: 1;
    }

    .category-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .category-required {
        font-size: 0.875rem;
        font-weight: 400;
        color: var(--gray-500);
    }

    .category-description {
        color: var(--gray-600);
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .category-info-box {
        border-radius: var(--border-radius-lg);
        padding: 1rem;
        font-size: 0.875rem;
    }

    .info-box-blue {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .info-box-yellow {
        background-color: #fefce8;
        border: 1px solid #fde047;
        color: #a16207;
    }

    .info-box-green {
        background-color: #f0fdf4;
        border: 1px solid #86efac;
        color: #166534;
    }

    .category-toggle-wrapper {
        margin-left: 1.5rem;
        flex-shrink: 0;
    }

    .always-enabled-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        background-color: #d1fae5;
        color: #065f46;
    }

    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .toggle-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        width: 2.75rem;
        height: 1.5rem;
        background-color: var(--gray-300);
        border-radius: 9999px;
        position: relative;
        transition: background-color 0.2s;
    }

    .toggle-slider::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 1.25rem;
        height: 1.25rem;
        background-color: var(--white);
        border-radius: 50%;
        transition: transform 0.2s;
    }

    .toggle-input:checked + .toggle-slider {
        background-color: var(--secondary-blue);
    }

    .toggle-input:checked + .toggle-slider::after {
        transform: translateX(1.25rem);
    }

    .toggle-input:focus + .toggle-slider {
        outline: 2px solid var(--secondary-blue);
        outline-offset: 2px;
    }

    .actions-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        justify-content: flex-end;
    }

    @media (min-width: 640px) {
        .actions-card {
            flex-direction: row;
        }
    }

    .btn {
        padding: 0.5rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: var(--border-radius-lg);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }

    .btn-success {
        background-color: #16a34a;
        color: var(--white);
    }

    .btn-success:hover {
        background-color: #15803d;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .btn-secondary {
        background-color: var(--gray-600);
        color: var(--white);
    }

    .btn-secondary:hover {
        background-color: var(--gray-900);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 85, 99, 0.3);
    }

    .btn-primary {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .btn-primary:hover {
        background-color: var(--secondary-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.3);
    }
</style>

<div class="settings-container">
    <div class="settings-wrapper">
         Header 
        <div class="settings-card">
            <div class="settings-header">
                <svg class="settings-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <div>
                    <h1 class="settings-title">Cookie Settings</h1>
                    <p class="settings-subtitle">Manage your cookie preferences</p>
                </div>
            </div>
        </div>

         Current Consent Status 
        <div class="settings-card">
            <h2 class="settings-title" style="font-size: 1.25rem; margin-bottom: 1rem;">Current Status</h2>
            <div class="status-grid">
                @foreach($categories as $key => $description)
                    <div class="status-item">
                        <div class="status-indicator {{ ($consent[$key] ?? false) ? 'enabled' : 'disabled' }}"></div>
                        <div class="status-info">
                            <p class="status-name">{{ ucfirst($key) }}</p>
                            <p class="status-label">{{ $consent[$key] ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

         Cookie Categories 
        <div class="category-list">
            @foreach($categories as $key => $description)
                <div class="category-card">
                    <div class="category-header">
                        <div class="category-content">
                            <h3 class="category-title">
                                {{ ucfirst($key) }} Cookies
                                @if($key === 'preference' || $key === 'consent')
                                    <span class="category-required">(Required)</span>
                                @endif
                            </h3>
                            <p class="category-description">{{ $description }}</p>

                            @if($key === 'preference')
                                <div class="category-info-box info-box-blue">
                                    <p>
                                        <strong>Essential cookies</strong> are required for the website to function properly.
                                        These cannot be disabled.
                                    </p>
                                </div>
                            @elseif($key === 'analytics')
                                <div class="category-info-box info-box-yellow">
                                    <p>
                                        <strong>Analytics cookies</strong> help us understand how visitors interact with our website
                                        by collecting and reporting information anonymously.
                                    </p>
                                </div>
                            @elseif($key === 'consent')
                                <div class="category-info-box info-box-green">
                                    <p>
                                        <strong>Consent management cookies</strong> store your cookie preferences.
                                        These cannot be disabled.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="category-toggle-wrapper">
                            @if($key === 'preference' || $key === 'consent')
                                <span class="always-enabled-badge">
                                    Always Enabled
                                </span>
                            @else
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           id="cookie-toggle-{{ $key }}"
                                           {{ ($consent[$key] ?? false) ? 'checked' : '' }}
                                           class="toggle-input"
                                           onchange="updateCookiePreference('{{ $key }}', this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

         Actions 
        <div class="settings-card">
            <div class="actions-card">
                <button onclick="acceptAllCookies()" class="btn btn-success">
                    Accept All Cookies
                </button>
                <button onclick="rejectNonEssential()" class="btn btn-secondary">
                    Reject Non-Essential
                </button>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    Save & Return
                </a>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function updateCookiePreference(type, enabled) {
        const consent = {};
        consent[type] = enabled;

        fetch('/cookies/consent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(consent)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error updating cookie preference:', error);
            alert('Failed to update cookie preference. Please try again.');
        });
    }

    function acceptAllCookies() {
        fetch('/cookies/accept-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("home") }}';
            }
        })
        .catch(error => {
            console.error('Error accepting all cookies:', error);
            alert('Failed to accept cookies. Please try again.');
        });
    }

    function rejectNonEssential() {
        fetch('/cookies/reject-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("home") }}';
            }
        })
        .catch(error => {
            console.error('Error rejecting cookies:', error);
            alert('Failed to reject cookies. Please try again.');
        });
    }
</script>
@endsection