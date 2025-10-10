@extends('layouts.app')

@section('title', 'Cookie Settings - DigiLearn')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-blue-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Cookie Settings</h1>
                    <p class="text-gray-600 mt-1">Manage your cookie preferences</p>
                </div>
            </div>
        </div>

        <!-- Current Consent Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($categories as $key => $description)
                    <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                        <div class="flex-shrink-0">
                            @if($consent[$key] ?? false)
                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            @else
                                <div class="w-4 h-4 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($key) }}</p>
                            <p class="text-xs text-gray-500">{{ $consent[$key] ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cookie Categories -->
        <div class="space-y-6">
            @foreach($categories as $key => $description)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ ucfirst($key) }} Cookies
                                @if($key === 'preference' || $key === 'consent')
                                    <span class="text-sm font-normal text-gray-500">(Required)</span>
                                @endif
                            </h3>
                            <p class="text-gray-600 mb-4">{{ $description }}</p>

                            @if($key === 'preference')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-sm text-blue-800">
                                        <strong>Essential cookies</strong> are required for the website to function properly.
                                        These cannot be disabled.
                                    </p>
                                </div>
                            @elseif($key === 'analytics')
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Analytics cookies</strong> help us understand how visitors interact with our website
                                        by collecting and reporting information anonymously.
                                    </p>
                                </div>
                            @elseif($key === 'consent')
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p class="text-sm text-green-800">
                                        <strong>Consent management cookies</strong> store your cookie preferences.
                                        These cannot be disabled.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="ml-6 flex-shrink-0">
                            @if($key === 'preference' || $key === 'consent')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Always Enabled
                                </span>
                            @else
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           id="cookie-toggle-{{ $key }}"
                                           {{ ($consent[$key] ?? false) ? 'checked' : '' }}
                                           class="sr-only peer"
                                           onchange="updateCookiePreference('{{ $key }}', this.checked)">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-8">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <button onclick="acceptAllCookies()"
                        class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Accept All Cookies
                </button>
                <button onclick="rejectNonEssential()"
                        class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Reject Non-Essential
                </button>
                <a href="{{ route('home') }}"
                   class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors text-center">
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
            // Update UI
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