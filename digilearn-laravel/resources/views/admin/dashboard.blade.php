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
        });
    });

    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);

    // Real-time updates (you can implement WebSocket here)
    function updateStats() {
        // Implementation for real-time stats updates
    }
</script>
@endsection
