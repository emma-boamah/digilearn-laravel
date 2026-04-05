@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <div class="mb-2">
            <nav class="flex items-center text-sm text-gray-500 space-x-2">
                <a href="{{ route('admin.users') }}" class="hover:text-blue-600 transition-colors">Users</a>
                <span class="text-gray-400">&gt;</span>
                <span class="text-gray-700 font-medium">{{ $user->name }} Profile</span>
            </nav>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                        @if($user->hasRole('super-admin'))
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 uppercase tracking-wider">Super Admin</span>
                        @elseif($user->hasRole('restricted-admin'))
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 uppercase tracking-wider">Admin</span>
                        @else
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 uppercase tracking-wider">Student</span>
                        @endif
                    </div>
                    <p class="text-gray-500 mt-1 flex items-center">
                        <i class="far fa-calendar-alt mr-2"></i>Joined {{ $user->created_at->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    @if($user->suspended_at)
                    <button onclick="toggleUserStatus({{ $user->id }}, 'unsuspend')"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-unlock mr-2"></i>Unsuspend User
                    </button>
                    @else
                    <button onclick="toggleUserStatus({{ $user->id }}, 'suspend')"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-ban mr-2"></i>Suspend User
                    </button>
                    @endif
                    <button onclick="exportUserData()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-user mr-3 text-blue-600"></i>Personal Information
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start space-x-8">
                            <!-- Large Avatar -->
                            <div class="flex-shrink-0 relative">
                                <x-user-avatar :user="$user" :size="90" id="user-avatar" />
                                <form id="avatar-upload-form" enctype="multipart/form-data"
                                    class="absolute bottom-0 right-0">
                                    @csrf
                                    <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                        class="hidden" />
                                    <button type="button" id="avatar-upload-button" title="Change Avatar"
                                        class="bg-gray-700 text-white rounded-full p-1.5 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm">
                                        <i class="fas fa-camera text-xs"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Info Grid -->
                            <div class="flex-1 grid grid-cols-2 gap-x-12 gap-y-5">
                                <!-- Full Name -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Full
                                        Name</label>
                                    <p class="text-base font-medium text-gray-900">{{ $user->name }}</p>
                                </div>
                                <!-- Email Address -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email
                                        Address</label>
                                    <p class="text-base font-medium text-gray-900">{{ $user->email }}</p>
                                </div>
                                <!-- Location -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Location</label>
                                    <p class="text-base font-medium text-gray-900">
                                        @if($user->city && $user->country)
                                        <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>{{ $user->city }}, {{
                                        $user->country }}
                                        @elseif($user->country)
                                        <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>{{ $user->country }}
                                        @else
                                        <span class="text-gray-400">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                                <!-- Education Level -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Education
                                        Level</label>
                                    <p class="text-base font-medium text-gray-900">
                                        {{ $user->education_level ? ucwords(str_replace('-', ' ',
                                        $user->education_level)) : 'Not specified' }}
                                    </p>
                                </div>
                                <!-- Current Grade -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Current
                                        Grade</label>
                                    <p class="text-base font-medium text-gray-900">
                                        {{ $user->grade ? ucwords(str_replace('-', ' ', $user->grade)) : 'Not set' }}
                                    </p>
                                </div>
                                <!-- Phone -->
                                @if($user->phone)
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Phone</label>
                                    <p class="text-base font-medium text-gray-900">{{ $user->phone }}</p>
                                </div>
                                @endif
                                <!-- Age -->
                                @if($user->date_of_birth)
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Age</label>
                                    <p class="text-base font-medium text-gray-900">{{
                                        \Carbon\Carbon::parse($user->date_of_birth)->age }} years old</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($user->bio)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Bio</label>
                            <p class="text-gray-900">{{ $user->bio }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Account Status & Security -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shield-alt mr-3 text-green-600"></i>Account Status & Security
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left: Status Badges -->
                            <div class="space-y-3">
                                <!-- Account Status -->
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Status</span>
                                    @if($user->suspended_at)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Suspended
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Active
                                    </span>
                                    @endif
                                </div>

                                @if($user->suspended_at && $user->suspension_reason)
                                <div class="px-3 py-2 bg-red-50 rounded-lg border border-red-100">
                                    <p class="text-xs text-red-600"><span class="font-semibold">Reason:</span> {{
                                        $user->suspension_reason }}</p>
                                </div>
                                @endif

                                <!-- Email Verified -->
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Email Verified</span>
                                    @if($user->email_verified_at)
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                    </div>
                                    @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                    @endif
                                </div>

                                <!-- Phone Verified -->
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">Phone Verified</span>
                                    @if($user->phone_verified_at)
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                    </div>
                                    @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                    @endif
                                </div>

                                <!-- 2FA Status -->
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">2FA Status</span>
                                    @if($user->hasTwoFactorEnabled())
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Enabled
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        Disabled
                                    </span>
                                    @endif
                                </div>

                                <!-- Account Lock -->
                                @if($user->isLocked())
                                <div
                                    class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                    <span class="text-sm font-medium text-red-600">Account Locked</span>
                                    <span class="text-xs text-red-500">Until {{ $user->locked_until->format('M d, Y
                                        H:i') }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Right: Login Information Card -->
                            <div>
                                <div class="bg-gray-50 rounded-xl border border-gray-200 p-2">
                                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Last
                                        Login Information</h3>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">IP
                                                Address</label>
                                            <p class="text-sm font-semibold text-gray-900 font-mono">{{
                                                $user->last_login_ip ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Date &
                                                Time</label>
                                            <p class="text-sm font-semibold text-gray-900">
                                                @if($user->last_login_at)
                                                @if($user->last_login_at->isToday())
                                                Today, {{ $user->last_login_at->format('h:i A') }}
                                                @elseif($user->last_login_at->isYesterday())
                                                Yesterday, {{ $user->last_login_at->format('h:i A') }}
                                                @else
                                                {{ $user->last_login_at->format('M d, Y') }}
                                                @endif
                                                @else
                                                Never
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Failed
                                                Attempts</label>
                                            <p class="text-sm font-semibold text-gray-900">{{
                                                $user->failed_login_attempts ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Registration Info -->
                                <div class="mt-4 bg-gray-50 rounded-xl border border-gray-200 p-2">
                                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
                                        Registration Details</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Registration
                                                IP</label>
                                            <p class="text-sm font-semibold text-gray-900 font-mono">{{
                                                $user->registration_ip ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Registered
                                                On</label>
                                            <p class="text-sm font-semibold text-gray-900">{{
                                                $user->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Logs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Recent Activity Logs</h2>
                    </div>
                    <div class="p-6">
                        @if(count($activities) > 0)
                        <div class="space-y-6">
                            @foreach($activities as $activity)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        @php
                                        $bgClass = 'bg-gray-100';
                                        $iconClass = 'fa-circle text-gray-500';

                                        switch($activity->type) {
                                        case 'user_login':
                                        case 'user_registration':
                                        $bgClass = 'bg-green-50';
                                        $iconClass = 'fa-sign-in-alt text-green-600';
                                        if($activity->type === 'user_registration') $iconClass = 'fa-user-plus
                                        text-green-600';
                                        break;
                                        case 'user_logout':
                                        $bgClass = 'bg-orange-50';
                                        $iconClass = 'fa-sign-out-alt text-orange-600';
                                        break;
                                        case 'profile_update':
                                        case 'password_change':
                                        $bgClass = 'bg-indigo-50';
                                        $iconClass = 'fa-user-edit text-indigo-600';
                                        if($activity->type === 'password_change') $iconClass = 'fa-key text-indigo-600';
                                        break;
                                        case 'lesson_access':
                                        case 'video_access':
                                        case 'quiz_access':
                                        $bgClass = 'bg-blue-50';
                                        $iconClass = 'fa-play text-blue-600';
                                        if($activity->type === 'video_access') $iconClass = 'fa-video text-blue-600';
                                        if($activity->type === 'quiz_access') $iconClass = 'fa-question-circle
                                        text-blue-600';
                                        break;
                                        case 'payment_action':
                                        case 'subscription_action':
                                        $bgClass = 'bg-purple-50';
                                        $iconClass = 'fa-credit-card text-purple-600';
                                        break;
                                        case 'data_creation':
                                        $bgClass = 'bg-green-50';
                                        $iconClass = 'fa-plus text-green-600';
                                        break;
                                        case 'data_update':
                                        $bgClass = 'bg-blue-50';
                                        $iconClass = 'fa-edit text-blue-600';
                                        break;
                                        case 'data_deletion':
                                        $bgClass = 'bg-red-50';
                                        $iconClass = 'fa-trash text-red-600';
                                        break;
                                        case 'page_view':
                                        $bgClass = 'bg-gray-100';
                                        $iconClass = 'fa-eye text-gray-600';
                                        break;
                                        }
                                        @endphp
                                        <div
                                            class="w-10 h-10 {{ $bgClass }} rounded-xl flex items-center justify-center">
                                            <i class="fas {{ $iconClass }} text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">
                                            {{ $activity->description }}
                                        </p>
                                        <p
                                            class="text-[10px] font-bold text-gray-400 font-mono tracking-tight uppercase">
                                            IP: {{ $activity->ip_address }}</p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-xs font-medium text-gray-400">
                                    {{ $activity->created_at->diffForHumans() }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-100 mb-4"></i>
                            <p class="text-gray-400 text-sm">No recent activities found.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Learning Progress Metrics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center space-x-2 mb-6">
                            <i class="fas fa-chart-line text-blue-600"></i>
                            <h2 class="text-lg font-semibold text-gray-900">Learning Progress Metrics</h2>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @php
                            $watchTime = $lessonProgress['total_watch_time'] ?? '0 min';
                            if (preg_match('/(\d+)\s*hour/i', $watchTime, $hMatches) && preg_match('/(\d+)\s*minute/i',
                            $watchTime, $mMatches)) {
                            $h = (int)$hMatches[1];
                            $m = (int)$mMatches[1];
                            $formattedWatchTime = number_format($h + ($m / 60), 1) . 'h';
                            } else {
                            $formattedWatchTime = str_replace([' hours', ' minutes', ' hour', ' minute'], ['h', 'm',
                            'h', 'm'], $watchTime);
                            }

                            $favSubject = $lessonProgress['favorite_subject'] ?? 'N/A';
                            $favSubject = str_replace('Mathematics', 'Maths', $favSubject);
                            @endphp

                            <!-- Lessons Completed -->
                            <div class="bg-blue-50 rounded-xl p-6 text-center">
                                <div class="text-3xl font-bold text-blue-700 mb-2">{{
                                    $lessonProgress['completed_lessons'] ?? 0 }}</div>
                                <div
                                    class="text-[9px] font-bold text-blue-400 uppercase tracking-wider whitespace-nowrap">
                                    Lessons Completed</div>
                            </div>

                            <!-- Completion Rate -->
                            <div class="bg-green-50 rounded-xl p-6 text-center">
                                <div class="text-3xl font-bold text-green-700 mb-2">{{
                                    $lessonProgress['completion_rate'] ?? 0 }}%</div>
                                <div
                                    class="text-[9px] font-bold text-green-400 uppercase tracking-wider whitespace-nowrap">
                                    Completion Rate</div>
                            </div>

                            <!-- Watch Time -->
                            <div class="bg-gray-50 rounded-xl p-6 text-center">
                                <div class="text-3xl font-bold text-gray-700 mb-2">{{ $formattedWatchTime }}</div>
                                <div
                                    class="text-[9px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">
                                    Watch Time</div>
                            </div>

                            <!-- Favorite Subject -->
                            <div class="bg-indigo-50 rounded-xl p-6 text-center">
                                <div class="text-2xl font-bold text-blue-700 mb-2 truncate"
                                    title="{{ $lessonProgress['favorite_subject'] ?? 'N/A' }}">
                                    {{ $favSubject }}
                                </div>
                                <div
                                    class="text-[9px] font-bold text-indigo-400 uppercase tracking-wider whitespace-nowrap">
                                    Favorite Subject</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar Info -->
            <div class="space-y-6">
                <!-- Subscription Plan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-5">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Subscription Plan
                        </h3>
                        @if($user->currentSubscription)
                        @php $subscription = $user->currentSubscription @endphp
                        <div class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div
                                class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-crown text-blue-600"></i>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900">{{ $subscription->pricingPlan->name ??
                                    'Unknown Plan' }}</p>
                                <p class="text-xs text-gray-400">
                                    @if($subscription->status === 'active')
                                    Active since {{ $subscription->created_at->format('M d, Y') }}
                                    @elseif($subscription->status === 'trial')
                                    Trial • {{ $subscription->trial_days_remaining ?? 0 }} days left
                                    @else
                                    {{ ucfirst($subscription->status) }}
                                    @endif
                                </p>
                            </div>
                            @if($subscription->status === 'active')
                            <div
                                class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            @elseif($subscription->status === 'trial')
                            <div
                                class="w-7 h-7 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-yellow-600 text-xs"></i>
                            </div>
                            @else
                            <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-times text-red-600 text-xs"></i>
                            </div>
                            @endif
                        </div>

                        @if($subscription->expires_at)
                        <div class="mt-3 flex items-center justify-between px-1">
                            <span class="text-xs text-gray-400">Expires</span>
                            <span class="text-xs font-semibold text-gray-600">{{ $subscription->expires_at->format('M d,
                                Y') }} ({{ $subscription->expires_at->diffForHumans() }})</span>
                        </div>
                        @endif
                        @else
                        <div class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div
                                class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-money-bill-wave text-gray-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-bold text-gray-900">Free Plan</p>
                                <p class="text-xs text-gray-400">No active subscription</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0 cursor-pointer hover:bg-blue-700 transition-colors"
                                title="Assign plan">
                                <i class="fas fa-plus text-white text-xs"></i>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Online Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-5">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Current Status
                        </h3>
                        <div class="flex items-center space-x-4">
                            @if($user->is_online)
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-wifi text-green-500 text-lg"></i>
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-900 uppercase tracking-wide">Online</p>
                                @if($user->current_room_id)
                                <p class="text-xs text-gray-400">In room: {{ $user->current_room_id }}</p>
                                @else
                                <p class="text-xs text-gray-400">Active now</p>
                                @endif
                            </div>
                            @else
                            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-wifi text-gray-400 text-lg" style="position: relative;">
                                </i>
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-900 uppercase tracking-wide">Offline</p>
                                @if($user->last_activity_at)
                                <p class="text-xs text-gray-400">Last active {{ $user->last_activity_at->diffForHumans()
                                    }}</p>
                                @else
                                <p class="text-xs text-gray-400">No recent activity</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Network & Location Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-5">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Network Info</h3>
                        <div class="space-y-3">
                            @if($user->registration_ip)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Reg. IP</span>
                                <span class="text-sm font-semibold text-gray-900 font-mono">{{ $user->registration_ip
                                    }}</span>
                            </div>
                            @endif

                            @if($user->last_login_ip && $user->last_login_ip !== $user->registration_ip)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Login IP</span>
                                <span class="text-sm font-semibold text-gray-900 font-mono">{{ $user->last_login_ip
                                    }}</span>
                            </div>
                            @endif

                            @if($user->country)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Country</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    <span class="text-xs text-gray-400 font-mono mr-1">{{
                                        strtoupper(substr($user->country, 0, 2)) }}</span>
                                    {{ $user->country }}
                                </span>
                            </div>
                            @endif

                            @if($user->city)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-500">City</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $user->city }}</span>
                            </div>
                            @endif

                            @if($user->registration_ip || $user->last_login_ip)
                            <button onclick="lookupIP('{{ $user->last_login_ip ?? $user->registration_ip }}')"
                                class="w-full mt-2 flex items-center justify-center px-4 py-2.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-semibold border border-blue-100">
                                <i class="fas fa-map-marker-alt mr-2"></i>Lookup Location
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Admin Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-5">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Quick Admin
                            Actions</h3>
                        <div class="space-y-2.5">
                            <button onclick="sendMessage()"
                                class="w-full flex items-center px-4 py-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors text-sm font-semibold text-blue-700 border border-blue-100">
                                <i class="fas fa-envelope text-blue-500 mr-3 text-base"></i>Send Message
                            </button>
                            <button onclick="resetPassword()"
                                class="w-full flex items-center px-4 py-3 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition-colors text-sm font-semibold text-yellow-700 border border-yellow-100">
                                <i class="fas fa-key text-yellow-600 mr-3 text-base"></i>Reset Password
                            </button>
                            <button onclick="viewLoginHistory()"
                                class="w-full flex items-center px-4 py-3 bg-green-50 rounded-xl hover:bg-green-100 transition-colors text-sm font-semibold text-green-700 border border-green-100">
                                <i class="fas fa-history text-green-500 mr-3 text-base"></i>Login History
                            </button>
                            <button onclick="impersonateUser()"
                                class="w-full flex items-center px-4 py-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors text-sm font-semibold text-purple-700 border border-purple-100">
                                <i class="fas fa-user-secret text-purple-500 mr-3 text-base"></i>Impersonate User
                            </button>
                            @role('super-admin')
                            @if($user->hasRole('restricted-admin'))
                            <button onclick="demoteAdmin({{ $user->id }})"
                                class="w-full flex items-center px-4 py-3 bg-red-50 rounded-xl hover:bg-red-100 transition-colors text-sm font-semibold text-red-700 border border-red-100">
                                <i class="fas fa-user-minus text-red-500 mr-3 text-base"></i>Demote from Admin
                            </button>
                            @endif
                            @endrole
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send User Notification Modal -->
<div class="modal fade" id="sendUserNotificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 rounded-2xl shadow-2xl">
            <div class="modal-header border-b border-gray-100 p-6 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-paper-plane text-blue-600"></i>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold text-gray-900 leading-tight">Send Notification</h5>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">To: {{
                            $user->name }}</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                    data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="sendUserNotificationForm">
                <div class="modal-body p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Notification Type</label>
                            <select
                                class="block w-full px-4 py-3 rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500/10 bg-white"
                                name="notification_type_id" id="userNotificationTypeSelect" required>
                                <option value="">Select notification type...</option>
                                @foreach(\App\Models\NotificationType::active()->get() ?? [] as $type)
                                <option value="{{ $type->id }}"
                                    data-channels="{{ json_encode($type->default_channels ?? ['database']) }}">
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Title</label>
                            <input type="text"
                                class="block w-full px-4 py-3 rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500/10 bg-white"
                                name="title" placeholder="Enter notification title..." required>
                        </div>
                    </div>

                    <div class="space-y-2 mb-6">
                        <label class="block text-sm font-bold text-gray-700">Message</label>
                        <textarea
                            class="block w-full px-4 py-3 rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500/10 bg-white"
                            name="message" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">URL <span
                                    class="text-xs font-normal text-gray-400">(Optional)</span></label>
                            <input type="url"
                                class="block w-full px-4 py-3 rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500/10 bg-white"
                                name="url" placeholder="https://example.com/...">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Delivery Channels</label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="channels[]" value="database" id="userDatabase" checked
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                    In-App
                                </label>
                                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="channels[]" value="mail" id="userMail"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                    Email
                                </label>
                                <label class="flex items-center text-sm font-medium text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="channels[]" value="broadcast" id="userBroadcast"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                    Real-time
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100/50" id="userNotificationPreview">
                        <div class="flex items-center text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="text-sm font-semibold italic"><strong>Preview:</strong> Select a notification
                                type to see preview</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-gray-100 p-6 space-x-3">
                    <button type="button"
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors"
                        data-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-500/25 transition-all flex items-center">
                        <i class="fas fa-paper-plane mr-2 text-xs"></i>Send Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- IP Lookup Modal -->
<div id="ipLookupModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">IP Location Lookup</h3>
            <div id="ipLookupContent">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="closeIPLookup()"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function demoteAdmin(userId) {
        if (!confirm('Are you sure you want to demote this administrator to a regular user? This will revoke all administrative privileges.')) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        const url = "{{ route('admin.users.demote', $user->id) }}";

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    function toggleUserStatus(userId, action) {
        const message = action === 'suspend' ? 'suspend this user' : 'unsuspend this user';
        if (!confirm(`Are you sure you want to ${message}?`)) {
            return;
        }

        const reason = action === 'suspend' ? prompt('Reason for suspension (optional):') : null;

        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: reason })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'An error occurred'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    }

    function exportUserData() {
        window.location.href = `/admin/users/{{ $user->id }}/export`;
    }

    function lookupIP(ip) {
        document.getElementById('ipLookupModal').classList.remove('hidden');
        document.getElementById('ipLookupContent').innerHTML = `
            <div class="flex justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        `;

        // Using a free IP geolocation service
        fetch(`https://ipapi.co/${ip}/json/`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('ipLookupContent').innerHTML = `
                    <div class="space-y-3">
                        <div class="text-center">
                            <h4 class="font-medium text-gray-900">${ip}</h4>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Country:</span>
                                <span class="text-gray-900">${data.country_name || 'Unknown'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">City:</span>
                                <span class="text-gray-900">${data.city || 'Unknown'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Region:</span>
                                <span class="text-gray-900">${data.region || 'Unknown'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ISP:</span>
                                <span class="text-gray-900">${data.org || 'Unknown'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Timezone:</span>
                                <span class="text-gray-900">${data.timezone || 'Unknown'}</span>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                document.getElementById('ipLookupContent').innerHTML = `
                    <div class="text-center text-red-600">
                        <i class="fas fa-exclamation-triangle mb-2"></i>
                        <p>Failed to lookup IP location</p>
                    </div>
                `;
            });
    }

    function closeIPLookup() {
        document.getElementById('ipLookupModal').classList.add('hidden');
    }

    function sendMessage() {
        // Open the send notification modal
        $('#sendUserNotificationModal').modal('show');
    }

    // Handle user notification form submission
    $('#sendUserNotificationForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('user_ids', ['{{ $user->id }}']); // Send to this specific user

        $.ajax({
            url: '{{ route("admin.notifications.send") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.success) {
                    toastr.success('Notification sent successfully!');
                    $('#sendUserNotificationModal').modal('hide');
                    $('#sendUserNotificationForm')[0].reset();
                } else {
                    toastr.error(data.message || 'Failed to send notification');
                }
            },
            error: function () {
                toastr.error('An error occurred while sending the notification');
            }
        });
    });

    // Update preview when notification type changes for user modal
    $('#userNotificationTypeSelect').change(function () {
        const selectedOption = $(this).find('option:selected');
        const channels = selectedOption.data('channels');

        if (channels) {
            $('#userNotificationPreview').html(`<strong>Preview:</strong> This notification will be sent via: ${channels.join(', ')}`);
        }
    });

    function resetPassword() {
        if (confirm('Are you sure you want to reset this user\'s password?')) {
            // Implement password reset functionality
            alert('Password reset functionality would be implemented here');
        }
    }

    function viewLoginHistory() {
        // Implement login history view
        alert('Login history functionality would be implemented here');
    }

    function impersonateUser() {
        if (confirm('Are you sure you want to impersonate this user? This will log you in as them.')) {
            // Implement user impersonation functionality
            alert('User impersonation functionality would be implemented here');
        }
    }

    // Avatar upload functionality
    document.getElementById('avatar-upload-button').addEventListener('click', function () {
        document.getElementById('avatar-input').click();
    });

    document.getElementById('avatar-input').addEventListener('change', function () {
        const fileInput = this;
        if (fileInput.files.length === 0) return;

        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('/admin/users/{{ $user->id }}/update-avatar', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update avatar image src with cache buster
                    const avatarImg = document.querySelector('#user-avatar img');
                    avatarImg.src = data.avatar_url + '?t=' + new Date().getTime();
                    alert(data.message);
                } else {
                    alert(data.message || 'Failed to update avatar.');
                }
            })
            .catch(() => {
                alert('An error occurred while uploading the avatar.');
            });
    });
</script>
@endsection