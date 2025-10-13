@extends('layouts.auth')

@section('title', 'Unlock Website')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full security-card">
        <div class="p-10">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h1>
                <p class="text-gray-600">Sign in to unlock your website</p>
            </div>
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="text-red-700">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <form class="space-y-6" action="{{ route('unlock.submit') }}" method="POST">
                @csrf
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Security Key</label>
                    <input 
                        name="email"
                        type="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Enter your email address"
                        value="{{ old('email') }}">
                </div>
                
                <div class="space-y-2" id="secondary-key-section">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Security Key</label>
                    <input 
                        name="password"
                        type="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Enter your password">
                </div>

                <div class="text-center mt-4">
                    <button type="button" 
                            id="show-recovery"
                            class="text-sm text-primary hover:text-primary-dark font-medium">
                        <i class="fas fa-key mr-1"></i>Use Recovery Code
                    </button>
                </div>

                <div id="recovery-section" class="hidden mt-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recovery Code</label>
                        <input 
                            name="recovery_code"
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Enter your recovery code">
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" 
                                id="hide-recovery"
                                class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Password
                        </button>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg font-medium btn-primary">
                        Unlock Website
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i> 
                    Protected by 256-bit encryption
                </p>
            </div>
        </div>
    </div>
</div>
@endsection