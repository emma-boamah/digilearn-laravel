@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="text-gray-600 mt-1">User ID: {{ $user->id }} • Joined {{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if($user->suspended_at)
                        <button onclick="toggleUserStatus({{ $user->id }}, 'unsuspend')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-unlock mr-2"></i>Unsuspend User
                        </button>
                    @else
                        <button onclick="toggleUserStatus({{ $user->id }}, 'suspend')" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-ban mr-2"></i>Suspend User
                        </button>
                    @endif
                    <button onclick="exportUserData()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 relative">
                                    <x-user-avatar :user="$user" :size="80" id="user-avatar" />
                                    <form id="avatar-upload-form" enctype="multipart/form-data" class="absolute bottom-0 right-0">
                                        @csrf
                                        <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden" />
                                        <button type="button" id="avatar-upload-button" title="Change Avatar" class="bg-gray-700 text-white rounded-full p-1 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-camera text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                                    <p class="text-gray-600">{{ $user->email }}</p>
                                    @if($user->phone)
                                        <p class="text-gray-600">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Age</label>
                                    <p class="text-gray-900">
                                        @if($user->date_of_birth)
                                            {{ \Carbon\Carbon::parse($user->date_of_birth)->age }} years old
                                        @else
                                            <span class="text-gray-500">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                    <p class="text-gray-900">
                                        @if($user->city && $user->country)
                                            {{ $user->city }}, {{ $user->country }}
                                        @elseif($user->country)
                                            {{ $user->country }}
                                        @else
                                            <span class="text-gray-500">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Education Level</label>
                                    <p class="text-gray-900">
                                        {{ $user->education_level ? ucwords(str_replace('-', ' ', $user->education_level)) : 'Not specified' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Current Grade</label>
                                    <p class="text-gray-900">
                                        {{ $user->grade ? ucwords(str_replace('-', ' ', $user->grade)) : 'Not set' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        @if($user->bio)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
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
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                                    <div class="flex flex-wrap gap-2">
                                        @if($user->suspended_at)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-ban mr-2"></i>Suspended
                                            </span>
                                            @if($user->suspension_reason)
                                                <span class="text-sm text-gray-600">Reason: {{ $user->suspension_reason }}</span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-2"></i>Active
                                            </span>
                                        @endif
                                        
                                        @if($user->is_superuser)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-crown mr-2"></i>Super Admin
                                            </span>
                                        @elseif($user->is_verified)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-user-shield mr-2"></i>Admin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-user mr-2"></i>User
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            @if($user->email_verified_at)
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Email verified on {{ $user->email_verified_at->format('M d, Y') }}</span>
                                            @else
                                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Email not verified</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center">
                                            @if($user->phone_verified_at)
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Phone verified on {{ $user->phone_verified_at->format('M d, Y') }}</span>
                                            @else
                                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Phone not verified</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Login Information</label>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Last Login:</span>
                                            <span class="text-gray-900">
                                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Last Login IP:</span>
                                            <span class="text-gray-900">{{ $user->last_login_ip ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Registration IP:</span>
                                            <span class="text-gray-900">{{ $user->registration_ip ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Failed Attempts:</span>
                                            <span class="text-gray-900">{{ $user->failed_login_attempts ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Security Features</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            @if($user->hasTwoFactorEnabled())
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Two-Factor Authentication Enabled</span>
                                            @else
                                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Two-Factor Authentication Disabled</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center">
                                            @if($user->isLocked())
                                                <i class="fas fa-lock text-red-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Account locked until {{ $user->locked_until->format('M d, Y H:i') }}</span>
                                            @else
                                                <i class="fas fa-unlock text-green-500 mr-2"></i>
                                                <span class="text-sm text-gray-900">Account not locked</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-history mr-3 text-orange-600"></i>Recent Activities
                        </h2>
                    </div>
                    <div class="p-6">
                        @if(count($activities) > 0)
                            <div class="space-y-4">
                                @foreach($activities as $activity)
                                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            @switch($activity['action'])
                                                @case('login')
                                                    <i class="fas fa-sign-in-alt text-blue-600 text-sm"></i>
                                                    @break
                                                @case('lesson_view')
                                                    <i class="fas fa-play text-green-600 text-sm"></i>
                                                    @break
                                                @case('profile_update')
                                                    <i class="fas fa-user-edit text-purple-600 text-sm"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-circle text-gray-600 text-sm"></i>
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $activity['description'] }}</p>
                                        <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                            <span><i class="fas fa-clock mr-1"></i>{{ $activity['created_at']->diffForHumans() }}</span>
                                            <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $activity['ip'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">No recent activities found.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Learning Progress -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-line mr-3 text-indigo-600"></i>Learning Progress
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ $lessonProgress['completed_lessons'] ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Lessons Completed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ $lessonProgress['completion_rate'] ?? 0 }}%</div>
                                <div class="text-sm text-gray-600">Completion Rate</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ $lessonProgress['total_watch_time'] ?? '0 min' }}</div>
                                <div class="text-sm text-gray-600">Total Watch Time</div>
                            </div>
                        </div>
                        
                        @if(isset($lessonProgress['favorite_subject']))
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Favorite Subject:</span>
                                <span class="text-sm text-gray-900">{{ $lessonProgress['favorite_subject'] }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar Info -->
            <div class="space-y-6">
                <!-- Subscription Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-credit-card mr-3 text-yellow-600"></i>Subscription
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($user->currentSubscription)
                            @php $subscription = $user->currentSubscription @endphp
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Current Plan</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ $subscription->pricingPlan->name ?? 'Unknown Plan' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <div class="mt-1">
                                        @if($subscription->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Active
                                            </span>
                                        @elseif($subscription->status === 'trial')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Trial
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>{{ ucfirst($subscription->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($subscription->expires_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Expires</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $subscription->expires_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $subscription->expires_at->diffForHumans() }}</p>
                                </div>
                                @endif
                                
                                @if($subscription->isInTrial())
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Trial Days Remaining</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $subscription->trial_days_remaining }} days</p>
                                </div>
                                @endif
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Started</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $subscription->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-times-circle text-3xl text-gray-300 mb-2"></i>
                                <p class="text-gray-500 text-sm">No active subscription</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 mt-2">
                                    Free Plan
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Online Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-wifi mr-3 text-green-600"></i>Online Status
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Current Status:</span>
                                @if($user->is_online)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"></div>Offline
                                    </span>
                                @endif
                            </div>
                            
                            @if($user->last_activity_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Last Activity:</span>
                                <span class="text-sm text-gray-900">{{ $user->last_activity_at->diffForHumans() }}</span>
                            </div>
                            @endif
                            
                            @if($user->current_room_id)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Current Room:</span>
                                <span class="text-sm text-blue-600">{{ $user->current_room_id }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-red-600"></i>Location Info
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @if($user->registration_ip)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Registration IP</label>
                                <p class="text-sm text-gray-900 mt-1 font-mono">{{ $user->registration_ip }}</p>
                                <button onclick="lookupIP('{{ $user->registration_ip }}')" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                    <i class="fas fa-search mr-1"></i>Lookup Location
                                </button>
                            </div>
                            @endif
                            
                            @if($user->last_login_ip && $user->last_login_ip !== $user->registration_ip)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Login IP</label>
                                <p class="text-sm text-gray-900 mt-1 font-mono">{{ $user->last_login_ip }}</p>
                                <button onclick="lookupIP('{{ $user->last_login_ip }}')" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                    <i class="fas fa-search mr-1"></i>Lookup Location
                                </button>
                            </div>
                            @endif
                            
                            @if($user->country)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Country</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $user->country }}</p>
                            </div>
                            @endif
                            
                            @if($user->city)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">City</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $user->city }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-bolt mr-3 text-purple-600"></i>Quick Actions
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button onclick="sendMessage()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                <i class="fas fa-envelope mr-2"></i>Send Message
                            </button>
                            <button onclick="resetPassword()" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                <i class="fas fa-key mr-2"></i>Reset Password
                            </button>
                            <button onclick="viewLoginHistory()" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-history mr-2"></i>Login History
                            </button>
                            <button onclick="impersonateUser()" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                                <i class="fas fa-user-secret mr-2"></i>Impersonate User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send User Notification Modal -->
<div class="modal fade" id="sendUserNotificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification to {{ $user->name }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form id="sendUserNotificationForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Notification Type</label>
                        <select class="form-control" name="notification_type_id" id="userNotificationTypeSelect" required>
                            <option value="">Select notification type...</option>
                            @foreach(\App\Models\NotificationType::active()->get() ?? [] as $type)
                            <option value="{{ $type->id }}" data-channels="{{ json_encode($type->default_channels ?? ['database']) }}">
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>URL (optional)</label>
                        <input type="url" class="form-control" name="url" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label>Delivery Channels</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="channels[]" value="database" id="userDatabase" checked>
                                    <label class="custom-control-label" for="userDatabase">In-App</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="channels[]" value="mail" id="userMail">
                                    <label class="custom-control-label" for="userMail">Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="channels[]" value="broadcast" id="userBroadcast">
                                    <label class="custom-control-label" for="userBroadcast">Real-time</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" id="userNotificationPreview">
                        <strong>Preview:</strong> Select a notification type to see preview
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
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
                <button onclick="closeIPLookup()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function toggleUserStatus(userId, action) {
        const message = action === 'suspend' ? 'suspend this user' : 'unsuspend this user';
        if (!confirm(`Are you sure you want to ${message}?`)) {
            return;
        }
        
        const reason = action === 'suspend' ? prompt('Reason for suspension (optional):') : null;
        
        fetch(`/admin/users/${userId}/${action}`, {
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
    $('#sendUserNotificationForm').submit(function(e) {
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
            success: function(data) {
                if (data.success) {
                    toastr.success('Notification sent successfully!');
                    $('#sendUserNotificationModal').modal('hide');
                    $('#sendUserNotificationForm')[0].reset();
                } else {
                    toastr.error(data.message || 'Failed to send notification');
                }
            },
            error: function() {
                toastr.error('An error occurred while sending the notification');
            }
        });
    });

    // Update preview when notification type changes for user modal
    $('#userNotificationTypeSelect').change(function() {
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
    document.getElementById('avatar-upload-button').addEventListener('click', function() {
        document.getElementById('avatar-input').click();
    });

    document.getElementById('avatar-input').addEventListener('change', function() {
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
