@extends('layouts.admin')

@section('title', 'Notification Management')
@section('page-title', 'Notification Management')
@section('page-description', 'Manage and send notifications to users across your platform')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Modern Notification Management Styles */
    .notification-dashboard {
        display: grid;
        gap: 1.5rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--white);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-blue), var(--primary-blue-hover));
    }

    .stat-card.success::before {
        background: linear-gradient(90deg, #10b981, #059669);
    }

    .stat-card.warning::before {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .stat-card.danger::before {
        background: linear-gradient(90deg, var(--accent-red), var(--accent-red-hover));
    }

    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--white);
    }

    .stat-icon.primary { background: var(--primary-blue); }
    .stat-icon.success { background: #10b981; }
    .stat-icon.warning { background: #f59e0b; }
    .stat-icon.danger { background: var(--accent-red); }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    /* Action Bar */
    .action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-hover));
        color: var(--white);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-primary-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    .btn-secondary-modern {
        background: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .btn-secondary-modern:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
    }

    /* Content Cards */
    .content-card {
        background: var(--white);
        border-radius: 12px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .card-header-modern {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .card-title-modern {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .card-subtitle-modern {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 0.25rem 0 0 0;
    }

    .card-body-modern {
        padding: 1.5rem;
    }

    /* Notification Types Grid */
    .types-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .type-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 1.25rem;
        transition: all 0.2s ease;
        position: relative;
    }

    .type-card:hover {
        border-color: var(--primary-blue);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .type-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .type-name {
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: var(--gray-200);
        color: var(--gray-600);
    }

    .type-description {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .type-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        background: var(--white);
        color: var(--gray-600);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        background: var(--gray-50);
        color: var(--gray-900);
        border-color: var(--gray-400);
    }

    /* Recent Notifications List */
    .notification-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .notification-item {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background: var(--gray-50);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-avatar {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: var(--white);
        font-size: 1rem;
    }

    .notification-avatar-bg {
        background-color: {{ $notification->notificationType?->color ?? '#2563eb' }};
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
    }

    .notification-meta {
        font-size: 0.75rem;
        color: var(--gray-500);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-status {
        padding: 0.125rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .notification-status.read {
        background: #dcfce7;
        color: #166534;
    }

    .notification-status.unread {
        background: #fef3c7;
        color: #92400e;
    }

    .notification-type {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--gray-500);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 0.875rem;
        margin: 0;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: var(--white);
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .modal-overlay.show .modal-content {
        transform: scale(1);
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .modal-close {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        background: var(--gray-100);
        color: var(--gray-600);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: var(--gray-200);
        color: var(--gray-900);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: var(--white);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .checkbox-input {
        width: 16px;
        height: 16px;
        accent-color: var(--primary-blue);
    }

    .checkbox-label {
        font-size: 0.875rem;
        color: var(--gray-700);
        cursor: pointer;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .action-buttons {
            justify-content: center;
        }
        
        .types-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            width: 95%;
            margin: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="notification-dashboard">
     Stats Overview 
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon primary">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            <div class="stat-value">{{ $totalNotifications ?? 0 }}</div>
            <div class="stat-label">Total Notifications</div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $activeTypes ?? 0 }}</div>
            <div class="stat-label">Active Types</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon warning">
                    <i class="fas fa-bullhorn"></i>
                </div>
            </div>
            <div class="stat-value">{{ $systemAnnouncements ?? 0 }}</div>
            <div class="stat-label">System Announcements</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon danger">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-value">{{ $unreadNotifications ?? 0 }}</div>
            <div class="stat-label">Unread Notifications</div>
        </div>
    </div>

     Action Bar 
    <div class="action-bar">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Notification Center</h2>
            <p class="text-sm text-gray-600 mt-1">Manage and send notifications across your platform</p>
        </div>
        <div class="action-buttons">
            <button type="button" class="btn-modern btn-primary-modern" onclick="openSendNotificationModal()">
                <i class="fas fa-plus"></i>
                Send Notification
            </button>
            <button type="button" class="btn-modern btn-secondary-modern" onclick="openBulkNotificationModal()">
                <i class="fas fa-users"></i>
                Bulk Send
            </button>
        </div>
    </div>

     Notification Types 
    <div class="content-card">
        <div class="card-header-modern">
            <div class="notification-type">
                <div>
                    <h3 class="card-title-modern">Notification Types</h3>
                    <p class="card-subtitle-modern">Configure different types of notifications for your platform</p>
                </div>
                <button type="button" class="btn-modern btn-secondary-modern" onclick="openCreateTypeModal()">
                    <i class="fas fa-plus"></i>
                    Add Type
                </button>
            </div>
        </div>

        <div class="card-body-modern">
            @if(isset($notificationTypes) && $notificationTypes->count() > 0)
                <div class="types-grid">
                    @foreach($notificationTypes as $type)
                    <div class="type-card">
                        <div class="type-header">
                            <h4 class="type-name">{{ $type->name }}</h4>
                            <span class="status-badge {{ $type->is_active ? 'active' : 'inactive' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="type-description">{{ $type->description ?? 'No description provided' }}</p>
                        <div class="type-actions">
                            <button type="button" class="btn-icon" onclick="editNotificationType({{ $type->id }})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-icon" onclick="toggleNotificationType({{ $type->id }})" title="Toggle Status">
                                <i class="fas fa-{{ $type->is_active ? 'eye-slash' : 'eye' }}"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-layer-group"></i>
                    <h3>No notification types configured</h3>
                    <p>Create your first notification type to get started</p>
                </div>
            @endif
        </div>
    </div>

     Recent Notifications 
    <div class="content-card">
        <div class="card-header-modern">
            <div>
                <h3 class="card-title-modern">Recent Notifications</h3>
                <p class="card-subtitle-modern">Latest notifications sent to users</p>
            </div>
        </div>

        @if(isset($recentNotifications) && $recentNotifications->count() > 0)
            <div class="notification-list">
                @foreach($recentNotifications as $notification)
                <div class="notification-item">
                    <div class="notification-avatar notification-avatar-bg">
                        <i class="{{ $notification->notificationType?->icon ?? 'fas fa-bell' }}"></i>
                    </div>
                    <div class="notification-content">
                        <h4 class="notification-title">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                        <div class="notification-meta">
                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                            <span>•</span>
                            <span>{{ class_basename($notification->notifiable_type) }}</span>
                            <span class="notification-status {{ $notification->read_at ? 'read' : 'unread' }}">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No notifications sent yet</h3>
                <p>Start sending notifications to see them appear here</p>
            </div>
        @endif
    </div>
</div>

 Send Notification Modal 
<div class="modal-overlay" id="sendNotificationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Send Notification</h3>
            <button type="button" class="modal-close" onclick="closeSendNotificationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="sendNotificationForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Notification Type</label>
                    <select class="form-control form-select" name="notification_type_id" id="notificationTypeSelect" required>
                        <option value="">Select notification type...</option>
                        @foreach($notificationTypes ?? [] as $type)
                        <option value="{{ $type->id }}" data-channels="{{ json_encode($type->default_channels ?? ['database']) }}">
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">URL (optional)</label>
                    <input type="url" class="form-control" name="url" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label class="form-label">Send to</label>
                    <select class="form-control form-select" name="send_type" id="sendTypeSelect" required>
                        <option value="all">All Users</option>
                        <option value="criteria">Users by Criteria</option>
                        <option value="specific">Specific Users</option>
                    </select>
                </div>

                <div class="form-group" id="criteriaGroup" style="display: none;">
                    <label class="form-label">User Criteria</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="criteria[]" value="verified" id="verified">
                            <label class="checkbox-label" for="verified">Verified Users</label>
                        </div>
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="criteria[]" value="active_subscription" id="active_subscription">
                            <label class="checkbox-label" for="active_subscription">Active Subscription</label>
                        </div>
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="criteria[]" value="recent_login" id="recent_login">
                            <label class="checkbox-label" for="recent_login">Recent Login (30 days)</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Delivery Channels</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="channels[]" value="database" id="database" checked>
                            <label class="checkbox-label" for="database">In-App</label>
                        </div>
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="channels[]" value="mail" id="mail">
                            <label class="checkbox-label" for="mail">Email</label>
                        </div>
                        <div class="checkbox-item">
                            <input class="checkbox-input" type="checkbox" name="channels[]" value="broadcast" id="broadcast">
                            <label class="checkbox-label" for="broadcast">Real-time</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" onclick="closeSendNotificationModal()">Cancel</button>
                <button type="submit" class="btn-modern btn-primary-modern">Send Notification</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when notification type changes
    const notificationTypeSelect = document.getElementById('notificationTypeSelect');
    if (notificationTypeSelect) {
        notificationTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const channels = selectedOption.getAttribute('data-channels');
            
            if (channels) {
                try {
                    const channelArray = JSON.parse(channels);
                    console.log('Selected channels:', channelArray);
                } catch (e) {
                    console.error('Error parsing channels:', e);
                }
            }
        });
    }

    // Show/hide criteria group based on send type
    const sendTypeSelect = document.getElementById('sendTypeSelect');
    const criteriaGroup = document.getElementById('criteriaGroup');
    
    if (sendTypeSelect && criteriaGroup) {
        sendTypeSelect.addEventListener('change', function() {
            criteriaGroup.style.display = this.value === 'criteria' ? 'block' : 'none';
        });
    }

    // Handle form submission
    const sendNotificationForm = document.getElementById('sendNotificationForm');
    if (sendNotificationForm) {
        sendNotificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("admin.notifications.send") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Notification sent successfully!', 'success');
                    closeSendNotificationModal();
                    sendNotificationForm.reset();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Failed to send notification', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while sending the notification', 'error');
            });
        });
    }
});

function openSendNotificationModal() {
    const modal = document.getElementById('sendNotificationModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeSendNotificationModal() {
    const modal = document.getElementById('sendNotificationModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function openBulkNotificationModal() {
    showNotification('Bulk notification feature coming soon!', 'info');
}

function openCreateTypeModal() {
    showNotification('Create notification type feature coming soon!', 'info');
}

function editNotificationType(typeId) {
    showNotification('Edit notification type feature coming soon!', 'info');
}

function toggleNotificationType(typeId) {
    showNotification('Toggle notification type feature coming soon!', 'info');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
        font-size: 0.875rem;
        font-weight: 500;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('sendNotificationModal');
    if (modal && e.target === modal) {
        closeSendNotificationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSendNotificationModal();
    }
});
</script>
@endpush
