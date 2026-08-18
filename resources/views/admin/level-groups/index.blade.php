@extends('layouts.admin')

@section('title', 'Manage Level Groups')
@section('page-title', 'Manage Level Groups')
@section('page-description', 'Activate or deactivate level groups to control which educational tiers are available to users.')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .groups-container {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--gray-200);
    }

    .stat-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon.primary {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .stat-icon.success {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .stat-icon.warning {
        background-color: #fef3c7;
        color: #d97706;
    }

    .groups-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
    }

    .groups-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .groups-table th {
        background-color: var(--gray-100);
        padding: 0.875rem 1.25rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--gray-200);
    }

    .groups-table td {
        padding: 1.25rem;
        border-bottom: 1px solid var(--gray-200);
        vertical-align: middle;
    }

    .groups-table tr:last-child td {
        border-bottom: none;
    }

    .groups-table tr:hover {
        background-color: var(--gray-50);
    }

    .level-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: var(--gray-100);
        color: var(--gray-700);
        margin: 0.15rem;
        border: 1px solid var(--gray-200);
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 26px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    input:checked + .toggle-slider {
        background-color: #10b981;
    }

    input:focus + .toggle-slider {
        box-shadow: 0 0 1px #10b981;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(22px);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pill.active {
        background-color: #dcfce7;
        color: #15803d;
    }

    .status-pill.inactive {
        background-color: #f1f5f9;
        color: #64748b;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-pill.active .status-dot {
        background-color: #22c55e;
    }

    .status-pill.inactive .status-dot {
        background-color: #94a3b8;
    }

    #toastNotification {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        transform: translateY(100px);
        opacity: 0;
        pointer-events: none;
    }

    #toastNotification.show {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    #toastNotification.toast-success {
        background-color: #065f46;
        color: #ffffff;
    }

    #toastNotification.toast-error {
        background-color: #991b1b;
        color: #ffffff;
    }
</style>

<div class="groups-container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Level Groups</p>
                <p class="text-2xl font-bold text-gray-900 mt-1" id="totalGroupsCount">{{ $levelGroups->count() }}</p>
            </div>
            <div class="stat-icon primary">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <p class="text-sm font-medium text-gray-500">Active Groups</p>
                <p class="text-2xl font-bold text-green-600 mt-1" id="activeGroupsCount">{{ $levelGroups->where('is_active', true)->count() }}</p>
            </div>
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <p class="text-sm font-medium text-gray-500">Deactivated Groups</p>
                <p class="text-2xl font-bold text-amber-600 mt-1" id="inactiveGroupsCount">{{ $levelGroups->where('is_active', false)->count() }}</p>
            </div>
            <div class="stat-icon warning">
                <i class="fas fa-eye-slash"></i>
            </div>
        </div>
    </div>

    <!-- Level Groups Table -->
    <div class="groups-section">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Platform Level Groups</h2>
                <p class="text-sm text-gray-500 mt-0.5">Toggle availability to show/hide level groups from user dashboard and restrict access.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="groups-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Level Group</th>
                        <th>Slug</th>
                        <th>Grades / Levels Included</th>
                        <th>Status</th>
                        <th class="text-center">Visibility Toggle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($levelGroups as $group)
                    <tr id="group-row-{{ $group->id }}">
                        <td class="font-medium text-gray-500">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                                {{ $group->display_order }}
                            </span>
                        </td>
                        <td>
                            <div class="font-semibold text-gray-900">{{ $group->title }}</div>
                            @if($group->description)
                            <div class="text-xs text-gray-500 mt-0.5 max-w-md truncate">{{ $group->description }}</div>
                            @endif
                        </td>
                        <td>
                            <code class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-mono">{{ $group->slug }}</code>
                        </td>
                        <td>
                            <div class="flex flex-wrap max-w-sm">
                                @forelse($group->levels as $lvl)
                                    <span class="level-badge">{{ $lvl->title }}</span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">No levels mapped</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <span id="status-pill-{{ $group->id }}" class="status-pill {{ $group->is_active ? 'active' : 'inactive' }}">
                                <span class="status-dot"></span>
                                <span id="status-text-{{ $group->id }}">{{ $group->is_active ? 'Active' : 'Deactivated' }}</span>
                            </span>
                        </td>
                        <td class="text-center">
                            <label class="toggle-switch" title="Toggle active status">
                                <input type="checkbox"
                                       class="level-group-toggle"
                                       data-id="{{ $group->id }}"
                                       data-name="{{ $group->title }}"
                                       {{ $group->is_active ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            <i class="fas fa-layer-group text-3xl mb-2 text-gray-300"></i>
                            <p>No level groups found in the database.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Feedback Notification -->
<div id="toastNotification">
    <i id="toastIcon" class="fas fa-check-circle"></i>
    <span id="toastMessage">Status updated successfully.</span>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('.level-group-toggle');
        const toast = document.getElementById('toastNotification');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        let toastTimeout = null;

        function showToast(message, isSuccess = true) {
            clearTimeout(toastTimeout);
            toastMessage.textContent = message;

            if (isSuccess) {
                toast.className = 'toast-success show';
                toastIcon.className = 'fas fa-check-circle';
            } else {
                toast.className = 'toast-error show';
                toastIcon.className = 'fas fa-exclamation-triangle';
            }

            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }

        function updateCounts() {
            const activeCount = document.querySelectorAll('.level-group-toggle:checked').length;
            const totalCount = document.querySelectorAll('.level-group-toggle').length;
            const inactiveCount = totalCount - activeCount;

            const activeEl = document.getElementById('activeGroupsCount');
            const inactiveEl = document.getElementById('inactiveGroupsCount');

            if (activeEl) activeEl.textContent = activeCount;
            if (inactiveEl) inactiveEl.textContent = inactiveCount;
        }

        toggles.forEach(toggle => {
            toggle.addEventListener('change', async function () {
                const groupId = this.dataset.id;
                const groupName = this.dataset.name;
                const checkbox = this;
                const isChecked = checkbox.checked;

                // Optimistically disable while requesting
                checkbox.disabled = true;

                try {
                    const response = await fetch(`/admin/level-groups/${groupId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        const statusPill = document.getElementById(`status-pill-${groupId}`);
                        const statusText = document.getElementById(`status-text-${groupId}`);

                        if (data.is_active) {
                            statusPill.className = 'status-pill active';
                            statusText.textContent = 'Active';
                        } else {
                            statusPill.className = 'status-pill inactive';
                            statusText.textContent = 'Deactivated';
                        }

                        checkbox.checked = data.is_active;
                        updateCounts();
                        showToast(data.message || `${groupName} status updated.`);
                    } else {
                        // Revert checkbox state on error
                        checkbox.checked = !isChecked;
                        showToast(data.message || 'Failed to update level group.', false);
                    }
                } catch (error) {
                    console.error('Toggle error:', error);
                    checkbox.checked = !isChecked;
                    showToast('An unexpected network error occurred.', false);
                } finally {
                    checkbox.disabled = false;
                }
            });
        });
    });
</script>
@endsection
