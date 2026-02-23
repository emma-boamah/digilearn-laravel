@extends('settings.layout')

@section('title', 'Account Settings')
@section('breadcrumb', 'Profile')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="page-title">Account Settings</h1>
            <p class="page-description">Manage your profile information and account security.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('profile.show') }}" class="bg-card text-main border p-2-5 rounded-md font-medium no-underline transition-all">
                View Profile
            </a>
            <button type="submit" form="profile-form" class="bg-primary text-white border-none p-2-5 rounded-md font-medium cursor-pointer transition-all hover-primary-dark">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Profile Card -->
<div class="bg-card rounded-2xl p-6 border mb-8 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-6">
        <div class="relative">
            <img src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                 alt="Profile" 
                 class="w-20 h-20 rounded-full object-cover">
        </div>
        <div>
            <h2 class="text-xl font-bold text-main mb-1">{{ $user->name }}</h2>
            <div class="text-secondary mb-2">{{ $user->email }}</div>
            <div class="flex items-center gap-3 text-sm">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold text-xs">
                    {{ $user->currentSubscription ? $user->currentSubscription->pricingPlan->name : 'FREE STUDENT' }}
                </span>
                <span class="text-muted">Joined {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>
    <button onclick="document.getElementById('avatar-input').click()" class="flex items-center gap-2 bg-body text-main border p-2 rounded-md font-medium cursor-pointer transition-all">
        <i class="fas fa-pencil-alt text-sm"></i>
        Edit Avatar
    </button>
    <form id="avatar-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        @method('PUT')
        <input type="file" id="avatar-input" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
    </form>
</div>

<div class="summary-grid grid gap-6 mb-8">
    <!-- Notification Summary -->
    <div>
        <h3 class="text-lg font-semibold text-main mb-4">Notification Summary</h3>
        <div class="bg-card rounded-2xl overflow-hidden border shadow-sm h-full">
            <div class="notification-gradient p-8 text-white">
                <i class="fas fa-bell text-4xl opacity-90"></i>
            </div>
            <div class="p-6">
                <h4 class="font-bold mb-2">Email & Push Alerts</h4>
                <p class="text-secondary text-sm mb-6">
                    Get real-time updates on assignments, grades, and campus news.
                </p>
                
                <div class="flex flex-col gap-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-secondary">Course Updates</span>
                        <div class="toggle-switch active">
                            <div class="toggle-dot"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-secondary">New Messages</span>
                        <div class="toggle-switch">
                            <div class="toggle-dot"></div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('settings.notifications') }}" class="block w-full text-center bg-primary text-white p-3 rounded-lg no-underline font-medium transition-all hover-primary-dark">
                    Manage All Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Account Security -->
    <div>
        <h3 class="text-lg font-semibold text-main mb-4">Account Security</h3>
        <div class="bg-card rounded-2xl overflow-hidden border shadow-sm h-full">
            <div class="security-header-bg p-8 text-white">
                <i class="fas fa-shield-alt text-4xl opacity-90"></i>
            </div>
            <div class="p-6">
                <h4 class="font-bold mb-2">Privacy & Access</h4>
                <p class="text-secondary text-sm mb-6">
                    Protect your account with advanced security features and login logs.
                </p>
                
                <div class="flex flex-col gap-4 mb-6">
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="text-success"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <div class="font-bold text-sm">2FA is Active</div>
                                <div class="text-xs text-muted">Protection via Authenticator App</div>
                            </div>
                        </div>
                        <a href="#" class="text-sm font-bold text-primary no-underline">Edit</a>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="text-warning"><i class="fas fa-history"></i></div>
                            <div>
                                <div class="font-bold text-sm">Password Health</div>
                                <div class="text-xs text-muted">Changed 3 months ago</div>
                            </div>
                        </div>
                        <a href="#" class="text-sm font-bold text-primary no-underline">Update</a>
                    </div>
                </div>
                
                <button class="w-full bg-body text-main border p-3 rounded-lg font-medium cursor-pointer">
                    View Login Activity
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Personal Information Form -->
<div class="mb-8">
    <h3 class="text-lg font-semibold text-main mb-4">Personal Information</h3>
    <div class="bg-card rounded-2xl p-8 border shadow-sm">
        <form id="profile-form" action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid grid gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-2">First Name</label>
                    <input type="text" name="first_name" value="{{ explode(' ', $user->name)[0] }}" class="w-full p-3 rounded-lg border bg-body text-main font-inherit">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-2">Last Name</label>
                    <input type="text" name="last_name" value="{{ explode(' ', $user->name, 2)[1] ?? '' }}" class="w-full p-3 rounded-lg border bg-body text-main font-inherit">
                </div>
            </div>

            <div class="form-grid grid gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full p-3 pl-10 rounded-lg border bg-body text-main font-inherit">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-2">Phone Number</label>
                    <div class="relative">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="phone" value="{{ $user->phone }}" class="w-full p-3 pl-10 rounded-lg border bg-body text-main font-inherit">
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-secondary mb-2">Bio</label>
                <textarea name="bio" rows="4" class="w-full p-3 rounded-lg border bg-body text-main font-inherit resize-vertical">{{ $user->bio ?? '' }}</textarea>
                <div class="text-xs text-muted text-right mt-1">Brief description for your profile.</div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .summary-grid { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }
    .form-grid { grid-template-columns: 1fr 1fr; }
    .notification-gradient { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .security-header-bg { background-color: #111827; }
    .toggle-switch { width: 44px; height: 24px; background-color: var(--border-color); border-radius: 9999px; position: relative; }
    .toggle-switch.active { background-color: var(--primary-color); }
    .toggle-dot { width: 20px; height: 20px; background-color: white; border-radius: 50%; position: absolute; top: 2px; transition: all 0.2s; }
    .toggle-switch.active .toggle-dot { right: 2px; }
    .toggle-switch:not(.active) .toggle-dot { left: 2px; }
    .input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .pl-10 { padding-left: 2.5rem; }
    .resize-vertical { resize: vertical; }
    .font-inherit { font-family: inherit; }
    .hover-primary-dark:hover { background-color: var(--primary-hover); }
    .rounded-md { border-radius: 0.375rem; }
    .p-2-5 { padding: 0.5rem 1rem; }
    .w-20 { width: 80px; }
    .h-20 { height: 80px; }
    .bg-blue-100 { background-color: #dbeafe; }
    .hidden { display: none; }
    
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
        .summary-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
