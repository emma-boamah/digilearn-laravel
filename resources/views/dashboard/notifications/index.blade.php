@extends('layouts.dashboard-components')

@section('head')
    <title>Notifications - Digital Learning Platform</title>
@endsection

@push('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Modern Notifications Index Styles */
        .notifications-container {
            padding: 2rem;
            max-width: 100vw;
            margin-top: 60px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.025em;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: var(--gray-100);
            padding: 0.25rem;
            border-radius: 0.75rem;
        }

        .filter-tab {
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .filter-tab.active {
            background: var(--white);
            color: var(--primary-red);
            box-shadow: var(--shadow-sm);
        }

        .notification-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            overflow: visible;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .notification-list-group {
            display: flex;
            flex-direction: column;
        }

        .notification-row {
            display: flex;
            gap: 1.25rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            transition: background-color 0.2s ease;
            position: relative;
        }

        .notification-row:last-child {
            border-bottom: none;
        }

        .notification-row:hover {
            background-color: var(--gray-25);
        }

        .notification-row.unread {
            background-color: rgba(38, 119, 184, 0.03);
        }

        .notification-row.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--secondary-blue);
        }

        .notification-link {
            display: flex;
            gap: 1.25rem;
            flex: 1;
            text-decoration: none;
            color: inherit;
        }

        .notification-link:hover {
            text-decoration: none;
            color: inherit;
        }

        .notification-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: var(--gray-600);
        }

        .avatar-red { background: rgba(225, 30, 45, 0.1); color: var(--primary-red); }
        .avatar-blue { background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue); }

        .notification-main {
            flex: 1;
        }

        .notification-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.25rem;
        }

        .notification-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .notification-timestamp {
            font-size: 0.75rem;
            color: var(--gray-500);
            white-space: nowrap;
        }

        .notification-excerpt {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.5;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notification-footer {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-tag {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            text-transform: uppercase;
        }

        .tag-lesson { background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue); }
        .tag-quiz { background: rgba(75, 85, 99, 0.1); color: var(--gray-600); }
        .tag-announcement { background: rgba(225, 30, 45, 0.1); color: var(--primary-red); }

        .notification-actions {
            display: flex;
            align-items: center;
            margin-left: auto;
            position: relative;
        }

        .more-options-btn {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease;
        }

        .more-options-btn:hover {
            background-color: var(--gray-100);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            z-index: 10;
            min-width: 140px;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1rem;
            background: none;
            border: none;
            text-align: left;
            color: var(--gray-700);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--gray-50);
        }

        .dropdown-item i {
            font-size: 0.875rem;
        }

        @media (max-width: 640px) {
            .notifications-container { padding: 1rem; }
            .notification-row { padding: 1rem; gap: 0.75rem; }
            .notification-link { gap: 0.75rem; }
            .notification-avatar { width: 40px; height: 40px; font-size: 1rem; }
            .notification-timestamp { display: none; }
        }
    </style>
@endpush

@section('content')
    <div class="notifications-container">
        <header class="page-header">
            <h1 class="page-title">Notifications</h1>
            
            <nav class="filter-tabs">
                <a href="{{ route('dashboard.notifications') }}" class="filter-tab {{ !request('unread_only') && !request('type') ? 'active' : '' }}">All</a>
                <a href="{{ route('dashboard.notifications') }}?unread_only=1" class="filter-tab {{ request('unread_only') ? 'active' : '' }}">Unread</a>
                <a href="{{ route('dashboard.notifications') }}?type=announcement" class="filter-tab {{ request('type') === 'announcement' ? 'active' : '' }}">Announcements</a>
            </nav>
        </header>

        <div class="notification-card">
            <div class="notification-list-group">
                @forelse($notifications as $notification)
                    @php
                        $type = $notification->type;
                        $avatarClass = 'avatar-blue';
                        $icon = 'fas fa-bell';
                        $tagClass = 'tag-announcement';
                        $tagText = 'Notification';

                        if (str_contains($type, 'NewVideoNotification') || str_contains($type, 'Lesson')) {
                            $avatarClass = 'avatar-blue';
                            $icon = 'fas fa-play-circle';
                            $tagClass = 'tag-lesson';
                            $tagText = 'Lesson';
                        } elseif (str_contains($type, 'SystemAnnouncement') || str_contains($type, 'AdminNotification')) {
                            $avatarClass = 'avatar-red';
                            $icon = 'fas fa-bullhorn';
                            $tagClass = 'tag-announcement';
                            $tagText = 'System';
                        } elseif (str_contains($type, 'NewQuizNotification') || str_contains($type, 'Quiz')) {
                            $avatarClass = '';
                            $icon = 'fas fa-tasks';
                            $tagClass = 'tag-quiz';
                            $tagText = 'Quiz';
                        } elseif (str_contains($type, 'NewDocumentNotification') || str_contains($type, 'Document')) {
                            $avatarClass = 'avatar-blue';
                            $icon = 'fas fa-file-alt';
                            $tagClass = 'tag-lesson';
                            $tagText = 'Document';
                        } elseif (str_contains($type, 'PaymentSuccessfulNotification')) {
                            $avatarClass = 'avatar-blue';
                            $icon = 'fas fa-credit-card';
                            $tagClass = 'tag-announcement';
                            $tagText = 'Payment';
                        } elseif (str_contains($type, 'ClassStartedNotification')) {
                            $avatarClass = 'avatar-red';
                            $icon = 'fas fa-users';
                            $tagClass = 'tag-announcement';
                            $tagText = 'Class';
                        } elseif (str_contains($type, 'StorageAlertNotification')) {
                            $avatarClass = 'avatar-red';
                            $icon = 'fas fa-exclamation-triangle';
                            $tagClass = 'tag-announcement';
                            $tagText = 'Storage';
                        }

                        // Use notificationType if available
                        if ($notification->notificationType) {
                            $tagText = $notification->notificationType->name ?? $tagText;
                            if ($notification->notificationType->icon) {
                                $icon = $notification->notificationType->icon;
                            }
                        }
                    @endphp
                    <div class="notification-row {{ $notification->isUnread() ? 'unread' : '' }}" data-notification-id="{{ App\Services\UrlObfuscator::encode($notification->id) }}">
                        <a href="{{ $notification->url ?: route('dashboard.notification.show', App\Services\UrlObfuscator::encode($notification->id)) }}" class="notification-link" @if($notification->url && $notification->isUnread()) data-mark-as-read="true" @endif>
                            <div class="notification-avatar {{ $avatarClass }}">
                                <i class="{{ $icon }}"></i>
                            </div>
                            <div class="notification-main">
                                <div class="notification-header-row">
                                    <h3 class="notification-title">{{ $notification->title }}</h3>
                                    <span class="notification-timestamp">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="notification-excerpt">{{ Str::limit($notification->message, 150) }}</p>
                                <div class="notification-footer">
                                    <span class="notification-tag {{ $tagClass }}">{{ $tagText }}</span>
                                    <span class="notification-timestamp sm-only">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                        <div class="notification-actions">
                            <button class="more-options-btn" data-notification-id="{{ App\Services\UrlObfuscator::encode($notification->id) }}" data-notification-type-slug="{{ $notification->notificationType ? $notification->notificationType->slug : '' }}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" data-action="delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                @if($notification->notificationType)
                                <button class="dropdown-item" data-action="mute">
                                    <i class="fas fa-bell-slash"></i> Mute similar
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="notification-row">
                        <div class="notification-main">
                            <p class="notification-excerpt">No notifications found.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="pagination-container" style="margin-top: 2rem; text-align: center;">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function markAsRead(notificationId) {
            fetch(`/api/notifications/${notificationId}/read`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).catch(error => {
                console.error('Failed to mark notification as read:', error);
            });
        }

        function toggleDropdown(button) {
            // Close any open dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });

            // Toggle the clicked dropdown
            const dropdown = button.nextElementSibling;
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.notification-actions')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        function deleteNotification(notificationId) {
            if (confirm('Are you sure you want to delete this notification?')) {
                fetch(`/api/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Auto-refresh the page for better UX
                        window.location.reload();
                    } else {
                        alert('Failed to delete notification: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Failed to delete notification:', error);
                    alert('Failed to delete notification.');
                });
            }
        }

        function muteNotificationType(typeId) {
            if (confirm('Are you sure you want to mute notifications of this type?')) {
                fetch('/api/notifications/preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        preferences: [{
                            type: typeId,
                            is_enabled: false,
                            channels: []
                        }]
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide all notifications of this type
                        document.querySelectorAll('.more-options-btn[data-notification-type-slug="' + typeId + '"]').forEach(btn => {
                            btn.closest('.notification-row').style.display = 'none';
                        });
                        alert('Notification type muted successfully.');
                    } else {
                        alert('Failed to mute notification type: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Failed to mute notification type:', error);
                    alert('Failed to mute notification type.');
                });
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Mark as read on link click
            document.querySelectorAll('.notification-link[data-mark-as-read]').forEach(link => {
                link.addEventListener('click', function() {
                    const id = this.closest('.notification-row').dataset.notificationId;
                    markAsRead(id);
                });
            });

            // Toggle dropdown
            document.querySelectorAll('.more-options-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    toggleDropdown(this);
                });
            });

            // Dropdown actions
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const row = this.closest('.notification-row');
                    const id = row.dataset.notificationId;
                    if (action === 'delete') {
                        deleteNotification(id);
                    } else if (action === 'mute') {
                        const typeSlug = this.closest('.notification-actions').querySelector('.more-options-btn').dataset.notificationTypeSlug;
                        muteNotificationType(typeSlug);
                    }
                });
            });
        });
    </script>
    @endsection
</body>
</html>
