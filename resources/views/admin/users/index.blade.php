@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                    <p class="text-gray-600 mt-1">Manage, filter, and monitor user accounts</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="toggleAnalyticsChart()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors shadow-sm text-sm font-medium inline-flex items-center">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        <span id="chartToggleText">Show Growth Chart</span>
                    </button>
                    @if(auth()->user()->hasRole('super-admin') || auth()->user()->is_superuser)
                    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
                        .btn-pulse-admin-invite {
                            animation: pulse-blue-admin 2s 3;
                        }
                        .btn-pulse-admin-invite:hover {
                            animation: none;
                        }
                        @keyframes pulse-blue-admin {
                            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.8); }
                            70% { box-shadow: 0 0 0 15px rgba(79, 70, 229, 0); }
                            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
                        }
                        .custom-select-wrapper {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            width: 100%;
                        }
                        .custom-select-wrapper .toolbar-select {
                            appearance: none;
                            -webkit-appearance: none;
                            -moz-appearance: none;
                            background-color: #ffffff;
                            border: 1px solid #d1d5db;
                            border-radius: 0.75rem;
                            padding: 10px 38px 10px 14px;
                            font-size: 0.875rem;
                            font-weight: 500;
                            color: #1f2937;
                            cursor: pointer;
                            transition: all 0.15s ease;
                            width: 100%;
                        }
                        .custom-select-wrapper .toolbar-select:hover {
                            background-color: #f9fafb;
                            border-color: #9ca3af;
                        }
                        .custom-select-wrapper .toolbar-select:focus {
                            outline: none;
                            border-color: #2563eb;
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
                        }
                        .custom-select-arrow {
                            position: absolute;
                            right: 14px;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #6b7280;
                            font-size: 0.75rem;
                            pointer-events: none;
                            transition: color 0.15s ease;
                        }
                        .custom-select-wrapper:hover .custom-select-arrow {
                            color: #111827;
                        }
                    </style>
                    <a href="{{ route('admin.users.invite') }}" class="btn-pulse-admin-invite bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm text-sm font-medium inline-flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>Invite Admin
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subscription Statistics Cards (Immediate High Level Stats) -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['total']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-crown text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Subscribed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['subscribed']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">On Trial</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['on_trial']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Expired</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['expired']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-user text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Free Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($userStats['total'] - $userStats['subscribed']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible User Growth Analytics Section -->
        <div id="analyticsChartContainer" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">User Growth</h2>
                    <p class="text-sm text-gray-600">New user registrations over time</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="monthlyBtn" onclick="updateChart('monthly')" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors chart-toggle">Monthly</button>
                    <button id="quarterlyBtn" onclick="updateChart('quarterly')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors chart-toggle">Quarterly</button>
                    <button id="annualBtn" onclick="updateChart('annual')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors chart-toggle">Annual</button>
                </div>
            </div>
            <div id="userGrowthChart" style="min-height: 350px;"></div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.users') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="all" {{ request('level') === 'all' ? 'selected' : '' }}>All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $level)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subscription</label>
                        <select name="subscription_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="all" {{ request('subscription_status') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="subscribed" {{ request('subscription_status') === 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                            <option value="not_subscribed" {{ request('subscription_status') === 'not_subscribed' ? 'selected' : '' }}>Not Subscribed</option>
                            <option value="active" {{ request('subscription_status') === 'active' ? 'selected' : '' }}>Active Plan</option>
                            <option value="trial" {{ request('subscription_status') === 'trial' ? 'selected' : '' }}>Trial Plan</option>
                            <option value="expired" {{ request('subscription_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ request('subscription_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plan Type</label>
                        <select name="plan_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="all" {{ request('plan_type') === 'all' ? 'selected' : '' }}>All Plans</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan }}" {{ request('plan_type') === $plan ? 'selected' : '' }}>{{ $plan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Table Header Toolbar -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-xl font-semibold text-gray-900">Users ({{ number_format($users->total()) }})</h2>
                        <div class="flex items-center space-x-2 pl-4 border-l border-gray-200">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <label for="selectAll" class="text-sm font-medium text-gray-600 cursor-pointer select-none">Select All</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <!-- Add this new column for Subscription -->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" onchange="updateBulkActionBar()" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <x-user-avatar :user="$user" :size="30" id="user-avatar" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 flex items-center">
                                            {{ $user->name }}
                                            @if($user->hasRole('super-admin'))
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 uppercase">Super Admin</span>
                                            @elseif($user->hasRole('restricted-admin'))
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 uppercase">Admin</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                @if($user->phone)
                                    <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $user->grade ? ucwords(str_replace('-', ' ', $user->grade)) : 'Not Set' }}
                                </span>
                            </td>
                            <!-- Add this new cell for Subscription information -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->currentSubscription)
                                    <span class="text-sm text-gray-900">{{ ucfirst($user->currentSubscription->status) }}</span>
                                @else
                                    <span class="text-sm text-gray-500">Free</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-block w-3 h-3 rounded-full {{ $user->suspended_at ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                    <span class="text-sm text-gray-900">{{ $user->suspended_at ? 'Suspended' : 'Active' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->suspended_at ? 'unsuspend' : 'suspend' }}')" 
                                            class="text-{{ $user->suspended_at ? 'green' : 'red' }}-600 hover:text-{{ $user->suspended_at ? 'green' : 'red' }}-900">
                                        <i class="fas fa-{{ $user->suspended_at ? 'unlock' : 'ban' }}"></i>
                                    </button>
                                    @role('super-admin')
                                        @if($user->hasRole('restricted-admin'))
                                            <button onclick="demoteAdmin({{ $user->id }}, '{{ $user->name }}')" 
                                                    class="text-orange-600 hover:text-orange-900" title="Demote from Admin">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        @endif
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p>No users found matching your criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Floating Bulk Selection Bar -->
<div id="bulkActionBar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 bg-slate-900 text-white px-6 py-3.5 rounded-xl shadow-2xl flex items-center space-x-6 border border-slate-700 transition-all duration-300 opacity-0 translate-y-12 pointer-events-none">
    <div class="flex items-center space-x-2 font-medium text-sm">
        <span class="bg-blue-600 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-xs font-bold" id="selectedCount">0</span>
        <span>users selected</span>
    </div>
    <div class="h-4 w-px bg-slate-700"></div>
    <div class="flex items-center space-x-3">
        <button onclick="showBulkActions()" class="bg-blue-600 hover:bg-blue-500 text-white px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
            <i class="fas fa-tasks mr-1.5"></i> Bulk Actions
        </button>
        <button onclick="exportSelectedUsers()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
            <i class="fas fa-download mr-1.5"></i> Export Selected
        </button>
        <button onclick="deselectAllUsers()" class="text-slate-400 hover:text-white text-sm font-medium transition-colors">
            Deselect All
        </button>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="backdrop-filter: blur(4px);">
    <!-- Semi-transparent backdrop revealing index page behind -->
    <div class="fixed inset-0 bg-gray-900/40 transition-opacity" onclick="closeBulkActions()"></div>

    <!-- Modal Content Box -->
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10 border border-gray-100 transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Bulk User Actions</h3>
                    <p class="text-xs text-gray-500">Apply action to selected user accounts</p>
                </div>
            </div>
            <button type="button" onclick="closeBulkActions()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="bulkActionForm" method="POST" action="{{ route('admin.users.bulk-action') }}">
            @csrf
            <!-- Target Selection Badge Summary -->
            <div class="mb-4 bg-blue-50/70 border border-blue-100 rounded-xl p-3 flex items-center justify-between">
                <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider">Target Selection</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-600 text-white" id="modalSelectedBadge">
                    0 users selected
                </span>
            </div>

            <!-- Action Select -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Choose Action</label>
                <div class="custom-select-wrapper w-full">
                    <select name="action" required class="toolbar-select w-full">
                        <option value="">Select an action to perform...</option>
                        <option value="suspend">Suspend Users</option>
                        <option value="unsuspend">Unsuspend Users</option>
                        <option value="verify">Verify Email Address</option>
                        <option value="delete">Delete Users</option>
                    </select>
                    <i class="fas fa-chevron-down custom-select-arrow"></i>
                </div>
            </div>

            <div id="selectedUsersContainer"></div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeBulkActions()" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center">
                    <i class="fas fa-check mr-2"></i> Execute Action
                </button>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function updateBulkActionBar() {
        const checked = document.querySelectorAll('.user-checkbox:checked');
        const bar = document.getElementById('bulkActionBar');
        const countSpan = document.getElementById('selectedCount');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const selectAllTop = document.getElementById('selectAll');
        
        if (countSpan) countSpan.textContent = checked.length;

        if (checked.length > 0) {
            bar.classList.remove('opacity-0', 'translate-y-12', 'pointer-events-none');
            bar.classList.add('opacity-100', 'translate-y-0');
        } else {
            bar.classList.add('opacity-0', 'translate-y-12', 'pointer-events-none');
            bar.classList.remove('opacity-100', 'translate-y-0');
        }

        const totalBoxes = document.querySelectorAll('.user-checkbox');
        if (selectAllHeader) selectAllHeader.checked = totalBoxes.length > 0 && checked.length === totalBoxes.length;
        if (selectAllTop) selectAllTop.checked = totalBoxes.length > 0 && checked.length === totalBoxes.length;
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const isChecked = (selectAll && selectAll.checked) || (selectAllHeader && selectAllHeader.checked);
        
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        if (selectAll) selectAll.checked = isChecked;
        if (selectAllHeader) selectAllHeader.checked = isChecked;
        updateBulkActionBar();
    }

    function deselectAllUsers() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        if (selectAll) selectAll.checked = false;
        if (selectAllHeader) selectAllHeader.checked = false;
        updateBulkActionBar();
    }

    function toggleAnalyticsChart() {
        const container = document.getElementById('analyticsChartContainer');
        const text = document.getElementById('chartToggleText');
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            if (text) text.textContent = 'Hide Growth Chart';
        } else {
            container.classList.add('hidden');
            if (text) text.textContent = 'Show Growth Chart';
        }
    }

    function exportSelectedUsers() {
        const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        if (selectedUsers.length === 0) {
            alert('Please select at least one user to export.');
            return;
        }
        window.location.href = '{{ route("admin.export") }}?type=users&format=csv&ids=' + selectedUsers.join(',');
    }

    function showBulkActions() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        
        if (selectedUsers.length === 0) {
            alert('Please select at least one user.');
            return;
        }
        
        const container = document.getElementById('selectedUsersContainer');
        container.innerHTML = '';

        const badge = document.getElementById('modalSelectedBadge');
        if (badge) badge.textContent = `${selectedUsers.length} user${selectedUsers.length > 1 ? 's' : ''} selected`;
        
        selectedUsers.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
        });
        
        document.getElementById('bulkActionsModal').classList.remove('hidden');
    }

    function closeBulkActions() {
        document.getElementById('bulkActionsModal').classList.add('hidden');
    }

    function demoteAdmin(userId, userName) {
        if (!confirm(`Are you sure you want to demote ${userName} to a regular user? This will revoke all administrative privileges.`)) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        const url = "{{ route('admin.users.demote', ':id') }}".replace(':id', userId);

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
        if (!confirm(`Are you sure you want to ${action} this user?`)) {
            return;
        }

        const reason = action === 'suspend' ? prompt('Reason for suspension (optional):') : null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message + '. Please try again.');
        });
    }

    function exportUsers() {
        window.location.href = '{{ route("admin.export") }}?type=users&format=csv';
    }

    function convertToPaid(userId, subscriptionId) {
        if (confirm('Convert this trial to a paid subscription?')) {
            const button = event.target.closest('button');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/users/${userId}/subscriptions/${subscriptionId}/convert-to-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message || 'Subscription converted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message || 'Failed to convert subscription');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-dollar-sign"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-dollar-sign"></i>';
            });
        }
    }

    function cancelSubscription(userId, subscriptionId) {
        const reason = prompt('Reason for cancellation (optional):');

        if (reason !== null) { // Allow empty reason
            const button = event.target.closest('button');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/users/${userId}/subscriptions/${subscriptionId}/cancel`, {
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
                    toastr.success(data.message || 'Subscription cancelled successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message || 'Failed to cancel subscription');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-times-circle"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-times-circle"></i>';
            });
        }
    }

    function assignSubscription(userId) {
        // You can implement a modal to select a plan and assign it
        const plan = prompt('Enter plan name to assign (e.g., "Essential", "Extra Tuition", "Home School"):');

        if (plan) {
            const button = event.target.closest('button');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/users/${userId}/assign-subscription`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ plan: plan })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message || 'Subscription assigned successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message || 'Failed to assign subscription');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-plus-circle"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred. Please try again.');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-plus-circle"></i>';
            });
        }
    }
</script>
@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    let growthData = @json($userGrowthData);
    let chart;

    document.addEventListener('DOMContentLoaded', function() {
        const options = {
            series: growthData.monthly.series,
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'inherit'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#4f46e5'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            xaxis: {
                categories: growthData.monthly.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#64748b' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b' },
                    formatter: function(val) { return Math.floor(val); }
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 4
            },
            tooltip: {
                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
                x: { show: true }
            },
            markers: {
                size: 4,
                colors: ['#4f46e5'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { size: 6 }
            }
        };

        chart = new ApexCharts(document.querySelector("#userGrowthChart"), options);
        chart.render();
    });

    function updateChart(period) {
        // Update button styles
        document.querySelectorAll('.chart-toggle').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        const activeBtn = document.getElementById(period + 'Btn');
        activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
        activeBtn.classList.add('bg-blue-600', 'text-white');

        // Update chart data
        chart.updateOptions({
            xaxis: { categories: growthData[period].labels },
            series: growthData[period].series
        });
    }
</script>
@endpush
@endsection
