@extends('layouts.auth')

@section('title', 'Website Temporarily Unavailable')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full security-card">
        <div class="p-10">
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                    <i class="fas fa-lock text-yellow-600 text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Website Maintenance</h1>
                <p class="text-gray-600">We're currently performing scheduled maintenance</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Temporary Unavailability
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>The website is temporarily locked for maintenance. Please check back later or contact support if you need immediate assistance.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-4">Expected features during maintenance:</p>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            System updates and improvements
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Security enhancements
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Performance optimizations
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    We'll be back online shortly
                </p>
            </div>
        </div>
    </div>
</div>
@endsection