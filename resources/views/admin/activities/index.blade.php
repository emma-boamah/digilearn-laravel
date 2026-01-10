@extends('layouts.admin')

@section('title', 'User Activities')
@section('page-title', 'User Activity Monitor')
@section('page-description', 'Monitor and analyze user activities across the platform')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .activity-item {
        transition: all 0.2s ease;
    }
    .activity-item:hover {
        background-color: #f8fafc;
        transform: translateX(4px);
    }
    .activity-type-login { border-left: 4px solid #10b981; }
    .activity-type-logout { border-left: 4px solid #6b7280; }
    .activity-type-profile_update { border-left: 4px solid #3b82f6; }
    .activity-type-password_change { border-left: 4px solid #f59e0b; }
    .activity-type-lesson_access { border-left: 4px solid #8b5cf6; }
    .activity-type-video_access { border-left: 4px solid #ec4899; }
    .activity-type-quiz_access { border-left: 4px solid #06b6d4; }
    .activity-type-page_view { border-left: 4px solid #64748b; }
    .activity-type-data_creation { border-left: 4px solid #22c55e; }
    .activity-type-data_update { border-left: 4px solid #f97316; }
    .activity-type-data_deletion { border-left: 4px solid #ef4444; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Activity Monitor</h1>
                    <p class="text-gray-600">Monitor and analyze user activities across the platform</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500" id="lastUpdated">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    <button id="refreshButton" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Activity Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-3xl font-bold text-gray-900" id="totalActivities">0</p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>Last 24h
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-activity text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Unique Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-3xl font-bold text-gray-900" id="uniqueUsers">0</p>
                        <p class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-users mr-1"></i>Unique users
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Most Active Type -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Top Activity</p>
                        <p class="text-lg font-bold text-gray-900" id="topActivityType">Loading...</p>
                        <p class="text-sm text-purple-600 mt-1" id="topActivityCount">
                            <i class="fas fa-chart-line mr-1"></i>0 activities
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-trophy text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today</p>
                        <p class="text-3xl font-bold text-gray-900" id="todayActivities">0</p>
                        <p class="text-sm text-orange-600 mt-1">
                            <i class="fas fa-calendar-day mr-1"></i>Activities today
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select id="userFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">All Users</option>
                        <!-- Will be populated via AJAX -->
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                    <select id="typeFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">All Types</option>
                        <option value="user_login">Login</option>
                        <option value="user_logout">Logout</option>
                        <option value="profile_update">Profile Update</option>
                        <option value="password_change">Password Change</option>
                        <option value="lesson_access">Lesson Access</option>
                        <option value="video_access">Video Access</option>
                        <option value="quiz_access">Quiz Access</option>
                        <option value="page_view">Page View</option>
                        <option value="data_creation">Data Creation</option>
                        <option value="data_update">Data Update</option>
                        <option value="data_deletion">Data Deletion</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" id="dateFromFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" id="dateToFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div>
                    <input type="text" id="searchFilter" placeholder="Search activities..." class="border border-gray-300 rounded-md px-3 py-2 w-64">
                </div>
                <div class="flex space-x-2">
                    <button id="applyFilters" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <button id="clearFilters" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Activities</h2>
                    <div class="flex items-center space-x-2">
                        <select id="perPageSelect" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                            <option value="20">20 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4" id="activitiesContainer">
                    <!-- Activities will be populated via AJAX -->
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                        <p class="text-gray-500 mt-2">Loading activities...</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6" id="paginationContainer">
                    <!-- Pagination will be populated via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    let currentPage = 1;
    let currentFilters = {};

    // Function to load activities via AJAX
    function loadActivities(page = 1, filters = {}) {
        const params = new URLSearchParams({
            page: page,
            per_page: document.getElementById('perPageSelect').value,
            ...filters
        });

        fetch(`/admin/user-activities?${params}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateActivities(data.data);
                updateStats(data.stats);
                updateTimestamp();
            }
        })
        .catch(error => {
            console.error('Error loading activities:', error);
            document.getElementById('activitiesContainer').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                    <p class="text-red-500 mt-2">Error loading activities. Please try again.</p>
                </div>
            `;
        });
    }

    // Update activities list
    function updateActivities(data) {
        const container = document.getElementById('activitiesContainer');
        container.innerHTML = '';

        if (data.data.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    <p class="text-gray-500 mt-2">No activities found.</p>
                </div>
            `;
            return;
        }

        data.data.forEach(activity => {
            const activityElement = createActivityElement(activity);
            container.appendChild(activityElement);
        });

        // Update pagination
        updatePagination(data);
    }

    // Create activity element
    function createActivityElement(activity) {
        const div = document.createElement('div');
        div.className = `activity-item flex items-start space-x-3 p-4 rounded-lg border border-gray-100 activity-type-${activity.type}`;

        const iconHtml = getActivityIcon(activity.type);
        const badgeClass = getActivityBadgeClass(activity.type);

        div.innerHTML = `
            <div class="flex-shrink-0">
                ${iconHtml}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-900 font-medium">${activity.description}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${badgeClass} ml-2">
                        ${formatActivityType(activity.type)}
                    </span>
                </div>
                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                    <span><i class="fas fa-user mr-1"></i>${activity.user_name} (${activity.user_email})</span>
                    <span><i class="fas fa-clock mr-1"></i>${activity.time_ago}</span>
                    ${activity.ip_address ? `<span><i class="fas fa-globe mr-1"></i>${activity.ip_address}</span>` : ''}
                </div>
                ${activity.metadata && Object.keys(activity.metadata).length > 0 ? `
                    <div class="mt-2 text-xs text-gray-400">
                        ${formatMetadata(activity.metadata)}
                    </div>
                ` : ''}
            </div>
        `;

        return div;
    }

    // Get activity icon
    function getActivityIcon(type) {
        const icons = {
            'user_login': '<div class="bg-green-100 p-2 rounded-full"><i class="fas fa-sign-in-alt text-green-600 text-sm"></i></div>',
            'user_logout': '<div class="bg-gray-100 p-2 rounded-full"><i class="fas fa-sign-out-alt text-gray-600 text-sm"></i></div>',
            'profile_update': '<div class="bg-blue-100 p-2 rounded-full"><i class="fas fa-user-edit text-blue-600 text-sm"></i></div>',
            'password_change': '<div class="bg-orange-100 p-2 rounded-full"><i class="fas fa-key text-orange-600 text-sm"></i></div>',
            'lesson_access': '<div class="bg-purple-100 p-2 rounded-full"><i class="fas fa-book text-purple-600 text-sm"></i></div>',
            'video_access': '<div class="bg-pink-100 p-2 rounded-full"><i class="fas fa-video text-pink-600 text-sm"></i></div>',
            'quiz_access': '<div class="bg-cyan-100 p-2 rounded-full"><i class="fas fa-question-circle text-cyan-600 text-sm"></i></div>',
            'page_view': '<div class="bg-slate-100 p-2 rounded-full"><i class="fas fa-eye text-slate-600 text-sm"></i></div>',
            'data_creation': '<div class="bg-green-100 p-2 rounded-full"><i class="fas fa-plus text-green-600 text-sm"></i></div>',
            'data_update': '<div class="bg-orange-100 p-2 rounded-full"><i class="fas fa-edit text-orange-600 text-sm"></i></div>',
            'data_deletion': '<div class="bg-red-100 p-2 rounded-full"><i class="fas fa-trash text-red-600 text-sm"></i></div>'
        };

        return icons[type] || '<div class="bg-gray-100 p-2 rounded-full"><i class="fas fa-circle text-gray-600 text-sm"></i></div>';
    }

    // Get activity badge class
    function getActivityBadgeClass(type) {
        const classes = {
            'user_login': 'bg-green-100 text-green-800',
            'user_logout': 'bg-gray-100 text-gray-800',
            'profile_update': 'bg-blue-100 text-blue-800',
            'password_change': 'bg-orange-100 text-orange-800',
            'lesson_access': 'bg-purple-100 text-purple-800',
            'video_access': 'bg-pink-100 text-pink-800',
            'quiz_access': 'bg-cyan-100 text-cyan-800',
            'page_view': 'bg-slate-100 text-slate-800',
            'data_creation': 'bg-green-100 text-green-800',
            'data_update': 'bg-orange-100 text-orange-800',
            'data_deletion': 'bg-red-100 text-red-800'
        };

        return classes[type] || 'bg-gray-100 text-gray-800';
    }

    // Format activity type for display
    function formatActivityType(type) {
        return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }

    // Format metadata
    function formatMetadata(metadata) {
        const entries = Object.entries(metadata).slice(0, 3); // Show only first 3
        return entries.map(([key, value]) => `${key}: ${value}`).join(' | ');
    }

    // Update pagination
    function updatePagination(data) {
        const container = document.getElementById('paginationContainer');
        container.innerHTML = '';

        if (data.last_page > 1) {
            const pagination = createPagination(data);
            container.appendChild(pagination);
        }
    }

    // Create pagination element
    function createPagination(data) {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between';

        const prevDisabled = data.current_page === 1 ? 'opacity-50 cursor-not-allowed' : '';
        const nextDisabled = data.current_page === data.last_page ? 'opacity-50 cursor-not-allowed' : '';

        div.innerHTML = `
            <div class="flex items-center space-x-2">
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm ${prevDisabled}"
                        ${data.current_page === 1 ? 'disabled' : ''}
                        onclick="changePage(${data.current_page - 1})">
                    <i class="fas fa-chevron-left mr-1"></i>Previous
                </button>
                <span class="text-sm text-gray-700">
                    Page ${data.current_page} of ${data.last_page} (${data.total} total)
                </span>
                <button class="px-3 py-1 border border-gray-300 rounded-md text-sm ${nextDisabled}"
                        ${data.current_page === data.last_page ? 'disabled' : ''}
                        onclick="changePage(${data.current_page + 1})">
                    Next<i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        `;

        return div;
    }

    // Update statistics
    function updateStats(stats) {
        document.getElementById('totalActivities').textContent = stats.total_activities.toLocaleString();
        document.getElementById('uniqueUsers').textContent = stats.unique_users.toLocaleString();

        // Find top activity type
        let topType = '';
        let topCount = 0;
        Object.entries(stats.activities_by_type).forEach(([type, count]) => {
            if (count > topCount) {
                topCount = count;
                topType = type;
            }
        });

        document.getElementById('topActivityType').textContent = formatActivityType(topType);
        document.getElementById('topActivityCount').textContent = `${topCount} activities`;

        // Calculate today's activities
        const today = new Date().toISOString().split('T')[0];
        const todayCount = stats.activities_by_day[today] || 0;
        document.getElementById('todayActivities').textContent = todayCount.toLocaleString();
    }

    // Update timestamp
    function updateTimestamp() {
        const now = new Date();
        document.getElementById('lastUpdated').textContent = 'Last updated: ' + now.toLocaleString();
    }

    // Change page
    function changePage(page) {
        currentPage = page;
        loadActivities(page, currentFilters);
    }

    // Apply filters
    function applyFilters() {
        currentFilters = {
            user_id: document.getElementById('userFilter').value,
            type: document.getElementById('typeFilter').value,
            date_from: document.getElementById('dateFromFilter').value,
            date_to: document.getElementById('dateToFilter').value,
            search: document.getElementById('searchFilter').value
        };

        currentPage = 1;
        loadActivities(1, currentFilters);
    }

    // Clear filters
    function clearFilters() {
        document.getElementById('userFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';
        document.getElementById('searchFilter').value = '';

        currentFilters = {};
        currentPage = 1;
        loadActivities(1, {});
    }

    // Event listeners
    document.getElementById('refreshButton').addEventListener('click', () => loadActivities(currentPage, currentFilters));
    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('perPageSelect').addEventListener('change', () => loadActivities(1, currentFilters));

    // Enter key for search
    document.getElementById('searchFilter').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadActivities();
    });
</script>
@endsection