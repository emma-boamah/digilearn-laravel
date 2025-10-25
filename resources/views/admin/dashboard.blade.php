@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your learning platform')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Dynamic progress bar widths */
    .progress-bar-storage {
        width: {{ $systemHealth['storage_usage']['used_percentage'] }};
    }
    
    .progress-bar-memory {
        width: {{ $systemHealth['memory_usage'] }};
    }
    
    .progress-bar-cpu {
        width: {{ $systemHealth['cpu_usage'] }};
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="text-gray-600">Welcome back, {{ Auth::user()->name }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    <button id="refreshButton" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button id="toggleLockButton" class="@if($websiteLocked) bg-green-600 @else bg-red-600 @endif text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas @if($websiteLocked) fa-unlock @else fa-lock @endif mr-2"></i>
                        @if($websiteLocked) Unlock Website @else Lock Website @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>+{{ $stats['new_users_this_week'] }} this week
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Online Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['online_users']) }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ number_format(($stats['online_users'] / $stats['total_users']) * 100, 1) }}% of total
                            <i class="fas fa-wifi text-green-500 mr-1"></i>
                            Active in last 5 minutes
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Lessons -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Lessons</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_lessons']) }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $stats['total_subjects'] }} subjects
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-book text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- New Today -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">New Today</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['new_users_today']) }}</p>
                        <p class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-calendar-day mr-1"></i>Registrations
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-user-plus text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cookie Consent Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Cookie Consent Analytics</h2>
                <a href="{{ route('admin.cookie-stats') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View Details <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-900">Total Consents</h3>
                            <p class="text-2xl font-bold text-green-800">{{ number_format($stats['cookie_consents']['total_consents']) }}</p>
                            <p class="text-sm text-green-700">{{ number_format($stats['cookie_consents']['consent_rate'], 1) }}% of users</p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-cookie-bite text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-900">Analytics Accepted</h3>
                            <p class="text-2xl font-bold text-blue-800">{{ number_format($stats['cookie_consents']['analytics_accepted']) }}</p>
                            <p class="text-sm text-blue-700">Optional tracking</p>
                        </div>
                        <div class="text-blue-600">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-purple-900">Recent Activity</h3>
                            <p class="text-2xl font-bold text-purple-800">{{ number_format($stats['cookie_consents']['recent_consents']) }}</p>
                            <p class="text-sm text-purple-700">Last 30 days</p>
                        </div>
                        <div class="text-purple-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        </div>

        <!-- Subscription Plans Badges -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Subscription Plans</h2>
                <a href="{{ route('admin.revenue') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View Revenue Analytics <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($stats['subscription_plans'] as $plan)
                <div class="bg-gradient-to-r from-{{ $plan['color'] }}-50 to-{{ $plan['color'] }}-100 rounded-lg p-4 border border-{{ $plan['color'] }}-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-{{ $plan['color'] }}-900">{{ $plan['name'] }}</h3>
                            <p class="text-2xl font-bold text-{{ $plan['color'] }}-800">{{ number_format($plan['subscribers']) }}</p>
                            <p class="text-sm text-{{ $plan['color'] }}-700">subscribers</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-{{ $plan['color'] }}-900">${{ number_format($plan['revenue'], 0) }}</p>
                            <p class="text-sm text-{{ $plan['color'] }}-700">revenue</p>
                        </div>
                    </div>
                    <div class="mt-3 bg-{{ $plan['color'] }}-200 rounded-full h-2">
                        <div class="bg-{{ $plan['color'] }}-600 h-2 rounded-full" style="width: {{ collect($stats['subscription_plans'])->sum('subscribers') > 0 ? ($plan['subscribers'] / collect($stats['subscription_plans'])->sum('subscribers')) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activities -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Activities</h2>
                            <a href="{{ route('admin.security') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recentActivities as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($activity['type'] === 'user_registration')
                                        <div class="bg-green-100 p-2 rounded-full">
                                            <i class="fas fa-user-plus text-green-600 text-sm"></i>
                                        </div>
                                    @elseif($activity['type'] === 'lesson_view')
                                        <div class="bg-blue-100 p-2 rounded-full">
                                            <i class="fas fa-play text-blue-600 text-sm"></i>
                                        </div>
                                    @elseif($activity['type'] === 'login_attempt')
                                        <div class="bg-purple-100 p-2 rounded-full">
                                            <i class="fas fa-sign-in-alt text-purple-600 text-sm"></i>
                                        </div>
                                    @else
                                        <div class="bg-gray-100 p-2 rounded-full">
                                            <i class="fas fa-edit text-gray-600 text-sm"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $activity['user'] }}</span>
                                        @if($activity['type'] === 'user_registration')
                                            registered a new account
                                        @elseif($activity['type'] === 'lesson_view')
                                            viewed lesson "{{ $activity['lesson'] ?? 'N/A' }}"
                                        @elseif($activity['type'] === 'login_attempt')
                                            {{ $activity['status'] === 'success' ? 'logged in successfully' : 'failed to log in' }}
                                        @elseif($activity['type'] === 'profile_update')
                                            updated their profile
                                        @elseif($activity['type'] === 'password_change')
                                            changed their password
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $activity['time'] }}
                                        @if(isset($activity['ip']))
                                            • {{ $activity['ip'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="space-y-6">
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">System Health</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Server Status</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['server_status']) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['database_status']['status']) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Cache</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['cache_status']['status']) }}
                                </span>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Storage Usage</span>
                                            <span class="text-gray-900">{{ $systemHealth['storage_usage']['used_percentage'] }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full progress-bar-storage"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Memory Usage</span>
                                            <span class="text-gray-900">{{ $systemHealth['memory_usage'] }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full progress-bar-memory"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">CPU Usage</span>
                                            <span class="text-gray-900">{{ $systemHealth['cpu_usage'] }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full progress-bar-cpu"></div>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.credentials') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-key text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Superuser Credentials</p>
                                            <p class="text-xs text-gray-500">Manage website lock credentials</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 text-xs text-gray-500">
                                <p>Uptime: {{ $systemHealth['uptime'] }}</p>
                                <p>Last Backup: {{ $systemHealth['last_backup'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('admin.users') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-users text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Manage Users</p>
                                    <p class="text-xs text-gray-500">View and manage user accounts</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.content') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-book text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Content Management</p>
                                    <p class="text-xs text-gray-500">Manage lessons and content</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.analytics') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-chart-bar text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Analytics</p>
                                    <p class="text-xs text-gray-500">View platform analytics</p>
                                </div>
                            </a>
                            <a href="{{ route('admin.security') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-red-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-shield-alt text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Security Monitor</p>
                                    <p class="text-xs text-gray-500">Monitor security events</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Refresh button handler
    document.getElementById('refreshButton').addEventListener('click', function() {
        location.reload();
    });

    // Toggle lock button handler
    document.getElementById('toggleLockButton').addEventListener('click', function() {
        // Show loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        this.disabled = true;

        fetch('{{ route("admin.toggle-lock") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Restore button state
            this.innerHTML = originalText;
            this.disabled = false;

            if (data.requires_recovery_codes) {
                // Show recovery code warning modal
                showRecoveryCodeWarning();
                return;
            }

            const icon = this.querySelector('i');
            const textNode = this.childNodes[this.childNodes.length - 1];

            if (data.locked) {
                icon.className = 'fas fa-unlock mr-2';
                textNode.textContent = ' Unlock Website';
                this.classList.remove('bg-red-600');
                this.classList.add('bg-green-600');
            } else {
                icon.className = 'fas fa-lock mr-2';
                textNode.textContent = ' Lock Website';
                this.classList.remove('bg-green-600');
                this.classList.add('bg-red-600');
            }

            alert(data.message);
        })
        .catch(error => {
            // Restore button state on error
            this.innerHTML = originalText;
            this.disabled = false;
            alert('An error occurred. Please try again.');
        });
    });

    // Function to show recovery code warning modal
    function showRecoveryCodeWarning() {
        // Create modal HTML
        const modalHTML = `
            <div id="recoveryCodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Recovery Codes Required</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-700 text-center">
                                You cannot lock the website without valid recovery codes for superusers.
                                Please generate recovery codes first to ensure you can unlock the website later.
                            </p>
                        </div>
                        <div class="flex items-center px-4 py-3 space-x-3">
                            <button id="generateCodesBtn" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-key mr-2"></i>Generate Recovery Codes
                            </button>
                            <button id="cancelLockBtn" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Handle generate codes button
        document.getElementById('generateCodesBtn').addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
            this.disabled = true;

            fetch('{{ route("admin.generate-recovery-codes") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('recoveryCodeModal').remove();
                alert('Recovery codes generated successfully! You can now lock the website.');
                location.reload(); // Refresh to show updated codes
            })
            .catch(error => {
                this.innerHTML = '<i class="fas fa-key mr-2"></i>Generate Recovery Codes';
                this.disabled = false;
                alert('Failed to generate recovery codes. Please try again.');
            });
        });

        // Handle cancel button
        document.getElementById('cancelLockBtn').addEventListener('click', function() {
            document.getElementById('recoveryCodeModal').remove();
        });
    }

    // Auto-refresh dashboard every 30 seconds for better real-time updates
    setInterval(function() {
        // Only auto-refresh if the page is visible and user hasn't interacted recently
        if (!document.hidden && !recentUserInteraction) {
            updateDashboardStats();
        }
    }, 30000); // 30 seconds

    // Track recent user interactions to avoid interrupting work
    let recentUserInteraction = false;
    let interactionTimeout;

    function markUserInteraction() {
        recentUserInteraction = true;
        clearTimeout(interactionTimeout);
        interactionTimeout = setTimeout(() => {
            recentUserInteraction = false;
        }, 10000); // Reset after 10 seconds of no interaction
    }

    // Track user interactions
    document.addEventListener('click', markUserInteraction);
    document.addEventListener('keydown', markUserInteraction);
    document.addEventListener('scroll', markUserInteraction);

    // Function to update dashboard stats without full page reload
    function updateDashboardStats() {
        fetch('{{ route("admin.dashboard.stats") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.stats);
                updateLastUpdatedTime();
            }
        })
        .catch(error => {
            console.log('Failed to update dashboard stats:', error);
        });
    }

    // Update the stats display with new data
    function updateStatsDisplay(stats) {
        // Update Total Users
        const totalUsersElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(1) p.text-3xl.font-bold.text-gray-900');
        if (totalUsersElement) {
            totalUsersElement.textContent = stats.total_users.toLocaleString();
        }

        // Update New Today count
        const newTodayElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(4) p.text-3xl.font-bold.text-gray-900');
        if (newTodayElement) {
            newTodayElement.textContent = stats.new_users_today.toLocaleString();
        }

        // Update Online Users
        const onlineUsersElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(2) p.text-3xl.font-bold.text-gray-900');
        if (onlineUsersElement) {
            onlineUsersElement.textContent = stats.online_users.toLocaleString();

            // Update percentage
            const percentageElement = onlineUsersElement.closest('.bg-white.rounded-xl').querySelector('.text-sm.text-gray-500');
            if (percentageElement && percentageElement.textContent.includes('% of total')) {
                const percentage = stats.total_users > 0 ? ((stats.online_users / stats.total_users) * 100).toFixed(1) : 0;
                percentageElement.innerHTML = percentage + '% of total <i class="fas fa-wifi text-green-500 mr-1"></i> Active in last 5 minutes';
            }
        }

        // Update New Users This Week
        const newWeekElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(1) p.text-sm.text-green-600');
        if (newWeekElement && newWeekElement.innerHTML.includes('this week')) {
            newWeekElement.innerHTML = '<i class="fas fa-arrow-up mr-1"></i>+' + stats.new_users_this_week + ' this week';
        }
    }

    // Update the "Last updated" timestamp
    function updateLastUpdatedTime() {
        const lastUpdatedElement = document.querySelector('.flex.items-center.space-x-4 .text-sm.text-gray-500');
        if (lastUpdatedElement) {
            const now = new Date();
            const formatted = now.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            lastUpdatedElement.textContent = 'Last updated: ' + formatted;
        }
    }

    // Real-time online users updates with Laravel Echo
    function updateOnlineUsersCount(count) {
        // Update the online users count
        const onlineUsersElement = document.querySelector('.text-3xl.font-bold.text-gray-900');
        const onlineUsersCard = onlineUsersElement.closest('.bg-white.rounded-xl');

        if (onlineUsersCard && onlineUsersCard.querySelector('.text-sm.font-medium.text-gray-600')) {
            const label = onlineUsersCard.querySelector('.text-sm.font-medium.text-gray-600');
            if (label.textContent.includes('Online Users')) {
                onlineUsersElement.textContent = count.toLocaleString();

                // Update percentage
                const percentageElement = onlineUsersCard.querySelector('.text-sm.text-gray-500');
                if (percentageElement && percentageElement.textContent.includes('% of total')) {
                    const totalUsers = parseInt(onlineUsersCard.getAttribute('data-total-users')) || 0;
                    const percentage = totalUsers > 0 ? ((count / totalUsers) * 100).toFixed(1) : 0;
                    percentageElement.innerHTML = percentage + '% of total <i class="fas fa-wifi text-green-500 mr-1"></i> Active in last 5 minutes';
                }
            }
        }
    }

    // Get initial online users count
    function fetchOnlineUsersCount() {
        fetch('{{ route("online-users") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOnlineUsersCount(data.count);
            }
        })
        .catch(error => {
            console.log('Failed to fetch online users count:', error);
        });
    }

    // Listen for real-time user online events
    if (window.Echo) {
        window.Echo.channel('online-users')
            .listen('.user.came-online', (e) => {
                console.log('User came online:', e.user);
                // Fetch updated count when a user comes online
                fetchOnlineUsersCount();
            });
    }

    // Fallback: Update online users count every 30 seconds (in case WebSocket fails)
    setInterval(fetchOnlineUsersCount, 30000);

    // Initial update
    setTimeout(fetchOnlineUsersCount, 2000);
</script>
@endsection
