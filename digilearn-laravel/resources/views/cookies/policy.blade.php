@extends('layouts.app')

@section('title', 'Cookie Policy - DigiLearn')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="text-center">
                <svg class="w-16 h-16 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Cookie Policy</h1>
                <p class="text-gray-600">Learn about how we use cookies and how you can control them</p>
                <p class="text-sm text-gray-500 mt-2">Last updated: {{ now()->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Introduction -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">What are Cookies?</h2>
            <p class="text-gray-600 mb-4 leading-relaxed">
                Cookies are small text files that are stored on your device when you visit our website.
                They help us provide you with a better browsing experience by remembering your preferences
                and understanding how you use our site.
            </p>
            <p class="text-gray-600 leading-relaxed">
                This cookie policy explains what cookies we use, why we use them, and how you can control
                your cookie preferences.
            </p>
        </div>

        <!-- Cookie Categories -->
        <div class="space-y-8">
            @foreach($categories as $key => $description)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ ucfirst($key) }} Cookies</h3>

                    @if($key === 'preference')
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <p class="text-blue-800 font-medium">Required Cookies</p>
                            <p class="text-blue-700 text-sm mt-1">
                                These cookies are essential for the website to function properly and cannot be disabled.
                            </p>
                        </div>
                    @elseif($key === 'analytics')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <p class="text-yellow-800 font-medium">Optional Analytics Cookies</p>
                            <p class="text-yellow-700 text-sm mt-1">
                                These cookies help us understand how visitors use our website by collecting
                                information anonymously. You can choose to disable these cookies.
                            </p>
                        </div>
                    @elseif($key === 'consent')
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                            <p class="text-green-800 font-medium">Consent Management Cookies</p>
                            <p class="text-green-700 text-sm mt-1">
                                These cookies store your cookie preferences and cannot be disabled.
                            </p>
                        </div>
                    @endif

                    <p class="text-gray-600 mb-4">{{ $description }}</p>

                    <h4 class="font-semibold text-gray-900 mb-2">Purpose:</h4>
                    <ul class="list-disc list-inside text-gray-600 space-y-1 mb-4">
                        @if($key === 'preference')
                            <li>Remember your login status</li>
                            <li>Maintain your session security</li>
                            <li>Store essential website preferences</li>
                        @elseif($key === 'analytics')
                            <li>Track page views and user interactions</li>
                            <li>Understand which content is most popular</li>
                            <li>Identify technical issues and improve performance</li>
                        @elseif($key === 'consent')
                            <li>Remember your cookie preferences</li>
                            <li>Ensure compliance with privacy regulations</li>
                        @endif
                    </ul>

                    @if($key !== 'preference' && $key !== 'consent')
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <strong>Note:</strong> You can change your preferences for {{ $key }} cookies at any time
                                by visiting our <a href="{{ route('cookies.settings') }}" class="text-blue-600 hover:underline">cookie settings page</a>.
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Third-party Cookies -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Third-party Cookies</h2>
            <p class="text-gray-600 mb-4">
                We may use third-party services that set their own cookies. These include:
            </p>
            <ul class="list-disc list-inside text-gray-600 space-y-2">
                <li><strong>Google Analytics:</strong> For website analytics and performance monitoring</li>
                <li><strong>Cloudflare:</strong> For website security and performance optimization</li>
                <li><strong>Social Media Platforms:</strong> For social sharing functionality</li>
            </ul>
        </div>

        <!-- Managing Cookies -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Managing Your Cookies</h2>
            <p class="text-gray-600 mb-4">
                You have several options for managing cookies:
            </p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Cookie Settings</h3>
                    <p class="text-gray-600">
                        Visit our <a href="{{ route('cookies.settings') }}" class="text-blue-600 hover:underline">cookie settings page</a>
                        to customize your cookie preferences.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Browser Settings</h3>
                    <p class="text-gray-600">
                        You can also control cookies through your browser settings. Most browsers allow you to:
                    </p>
                    <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                        <li>View what cookies are stored</li>
                        <li>Delete existing cookies</li>
                        <li>Block cookies from specific sites</li>
                        <li>Block all cookies</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Opt-out Links</h3>
                    <p class="text-gray-600">
                        For Google Analytics, you can opt-out by visiting:
                        <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" class="text-blue-600 hover:underline">
                            Google Analytics Opt-out
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Us</h2>
            <p class="text-gray-600 mb-4">
                If you have any questions about our cookie policy or how we use cookies, please contact us:
            </p>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900"><strong>Email:</strong> privacy@digilearn.com</p>
                <p class="text-gray-900"><strong>Phone:</strong> +233-207-646-203</p>
                <p class="text-gray-900"><strong>Address:</strong> Accra, Ghana</p>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="text-center mt-8">
            <a href="{{ route('cookies.settings') }}"
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Manage Cookie Settings
            </a>
        </div>
    </div>
</div>
@endsection