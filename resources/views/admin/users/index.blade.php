@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                    <p class="text-gray-600 mt-1">Manage and monitor user accounts</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="exportUsers()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button onclick="showBulkActions()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-tasks mr-2"></i>Bulk Actions
                    </button>
                </div>
            </div>
        </div>

        <!-- Subscription Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $userStats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-crown text-green-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Subscribed Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $userStats['subscribed'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">On Trial</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $userStats['on_trial'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 p-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Expired</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $userStats['expired'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-lg">
                        <i class="fas fa-user text-gray-600 text-xl" aria-hidden="true"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Free Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $userStats['total'] - $userStats['subscribed'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('admin.users') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" {{ request('level') === 'all' ? 'selected' : '' }}>All Levels</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $level)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subscription</label>
                        <select name="subscription_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                        <select name="plan_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" {{ request('plan_type') === 'all' ? 'selected' : '' }}>All Plans</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan }}" {{ request('plan_type') === $plan ? 'selected' : '' }}>{{ $plan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Users ({{ $users->total() }})</h2>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="selectAll" class="text-sm text-gray-600">Select All</label>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <x-user-avatar :user="$user" :size="30" id="user-avatar" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
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

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
            <form id="bulkActionForm" method="POST" action="{{ route('admin.users.bulk-action') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select name="action" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Action</option>
                        <option value="suspend">Suspend Users</option>
                        <option value="unsuspend">Unsuspend Users</option>
                        <option value="verify">Verify Email</option>
                    </select>
                </div>
                <div id="selectedUsersContainer"></div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBulkActions()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Execute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function showBulkActions() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        
        if (selectedUsers.length === 0) {
            alert('Please select at least one user.');
            return;
        }
        
        const container = document.getElementById('selectedUsersContainer');
        container.innerHTML = `<p class="text-sm text-gray-600 mb-4">${selectedUsers.length} users selected</p>`;
        
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
@endsection
