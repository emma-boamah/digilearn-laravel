<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications - DigiLearn</title>
    <link rel="icon" type="image/x-icon" href="{{ secure_asset('images/favicon.ico') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
            --secondary-blue-hover: #1e5a8a;
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-purple: #8b5cf6;
            --white: #ffffff;
            --gray-25: #fcfcfd;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --sidebar-width-expanded: 280px;
            --sidebar-width-collapsed: 72px;

            /* Modern UI Variables */
            --foreground: var(--gray-900);
            --foreground-muted: var(--gray-600);
            --foreground-subtle: var(--gray-500);
            --surface: var(--white);
            --surface-elevated: var(--gray-50);
            --surface-hover: var(--gray-100);
            --border: var(--gray-200);
            --border-hover: var(--gray-300);
            --radius: 0.5rem;
            --radius-md: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-25);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }



        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
            height: 64px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
        }

        .notification-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background-color: var(--accent-red);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
            min-width: 1.25rem;
            text-align: center;
        }

        /* Modern Notifications Content */
        .notifications-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .notifications-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--foreground);
            margin: 0;
        }

        .notifications-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--secondary-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .btn-secondary {
            background-color: var(--surface-elevated);
            color: var(--foreground-muted);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
            border-color: var(--border-hover);
        }

        .btn-danger {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--primary-red-hover);
        }

        /* Modern Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            border-color: var(--border-hover);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card-icon {
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .stat-card-change {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-card-change.positive {
            color: var(--accent-green);
        }

        .stat-card-change.negative {
            color: var(--accent-red);
        }

        /* Modern Filters */
        .filters-section {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground-muted);
        }

        .filter-select {
            background-color: var(--surface-elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            color: var(--foreground);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        /* Modern Notifications List */
        .notifications-list {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: var(--surface-hover);
        }

        .notification-item.unread {
            background-color: rgba(38, 119, 184, 0.05);
            border-left: 3px solid var(--secondary-blue);
        }

        .notification-item.unread:hover {
            background-color: rgba(38, 119, 184, 0.08);
        }

        .notification-icon-wrapper {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-message {
            font-size: 0.875rem;
            color: var(--foreground-muted);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--foreground-subtle);
        }

        .notification-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .notification-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            color: var(--foreground-subtle);
        }

        .notification-action-btn:hover {
            background-color: var(--surface-hover);
            color: var(--foreground);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-new {
            background-color: var(--secondary-blue);
            color: white;
        }

        .badge-success {
            background-color: var(--accent-green);
            color: white;
        }

        .badge-warning {
            background-color: var(--accent-orange);
            color: white;
        }

        .badge-danger {
            background-color: var(--primary-red);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--foreground-muted);
        }

        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            background-color: var(--surface-elevated);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--foreground-subtle);
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .empty-state-message {
            font-size: 0.875rem;
            color: var(--foreground-muted);
        }

        /* Mobile Layout Reset - Fix left gap issue */
        @media (max-width: 768px) {
            .main-content {
                width: 100vw !important;
                max-width: 100vw !important;
                margin-left: 0 !important;
            }

            .youtube-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 1200;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0;
                width: 100vw;
                max-width: 100vw;
            }

            .notifications-container {
                padding: 1rem;
            }

            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .notifications-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .notification-item {
                padding: 1rem;
            }

            .notification-actions {
                flex-direction: column;
            }
        }

        /* User Avatar Component */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid var(--border);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            box-shadow: var(--shadow-md);
        }
    </style>
</head>
<body>
    <div class="main-container">
        @include('components.dashboard-sidebar')

        <!-- Main Content -->
        <main class="main-content">
            @include('components.dashboard-header')

            <!-- Modern Notifications Content -->
            <div class="notifications-container">
                <div class="notifications-header">
                    <h1 class="notifications-title">Notifications</h1>
                    <div class="notifications-actions">
                        <button type="button" class="btn btn-secondary" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i>
                            Mark All Read
                        </button>
                        <button type="button" class="btn btn-danger" onclick="clearAllNotifications()">
                            <i class="fas fa-trash"></i>
                            Clear All
                        </button>
                    </div>
                </div>

                <!-- Modern Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Total Notifications</span>
                            <div class="stat-card-icon" style="background-color: rgba(59, 130, 246, 0.1); color: var(--accent-blue);">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ isset($notifications) ? $notifications->total() : 0 }}</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 12% from last week
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Unread</span>
                            <div class="stat-card-icon" style="background-color: rgba(239, 68, 68, 0.1); color: var(--accent-red);">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->unreadNotifications->count() }}</div>
                        <div class="stat-card-change negative">
                            <i class="fas fa-arrow-down"></i> 5% from yesterday
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">This Week</span>
                            <div class="stat-card-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--accent-green);">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->notifications()->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 8% increase
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <span class="stat-card-title">Response Rate</span>
                            <div class="stat-card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--accent-purple);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">94%</div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up"></i> 2% improvement
                        </div>
                    </div>
                </div>

                <!-- Modern Filters -->
                <div class="filters-section">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="filter-select" id="notificationFilter">
                                <option value="all">All Notifications</option>
                                <option value="unread">Unread Only</option>
                                <option value="read">Read Only</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Type</label>
                            <select class="filter-select" id="typeFilter">
                                <option value="all">All Types</option>
                                @foreach(\App\Models\NotificationType::active()->get() ?? [] as $type)
                                <option value="{{ $type->slug }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Date Range</label>
                            <select class="filter-select" id="dateFilter">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modern Notifications List -->
                <div class="notifications-list" id="notificationsContainer">
                    @if(isset($notifications) && $notifications->count() > 0)
                        @foreach($notifications as $notification)
                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                             data-id="{{ $notification->id }}"
                             data-type="{{ $notification->notificationType?->slug ?? 'general' }}">
                            
                            <div class="notification-icon-wrapper" 
                                 style="background-color: {{ $notification->notificationType?->color ?? 'rgba(59, 130, 246, 0.1)' }};">
                                <i class="{{ $notification->notificationType?->icon ?? 'fas fa-bell' }}" 
                                   style="color: {{ $notification->notificationType?->color ?? 'var(--accent-blue)' }};"></i>
                            </div>

                            <div class="notification-content">
                                <div class="notification-title">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                    @if(!$notification->read_at)
                                        <span class="badge badge-new">New</span>
                                    @endif
                                </div>
                                <div class="notification-message">
                                    {{ Str::limit($notification->data['message'] ?? '', 150) }}
                                </div>
                                <div class="notification-meta">
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if($notification->notificationType)
                                        <span>
                                            <i class="fas fa-tag"></i>
                                            {{ $notification->notificationType->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="notification-actions">
                                @if($notification->data['url'] ?? false)
                                    <a href="{{ $notification->data['url'] }}" 
                                       class="notification-action-btn" 
                                       target="_blank" 
                                       title="Open Link">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button type="button" 
                                            class="notification-action-btn" 
                                            onclick="markAsRead({{ $notification->id }})"
                                            title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                <button type="button" 
                                        class="notification-action-btn" 
                                        onclick="deleteNotification({{ $notification->id }})"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        @if($notifications->hasPages())
                        <div style="padding: 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: center;">
                            {{ $notifications->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell fa-2x"></i>
                            </div>
                            <h3 class="empty-state-title">No notifications yet</h3>
                            <p class="empty-state-message">You'll receive notifications about important updates and activities here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        $(document).ready(function() {
            // Sidebar toggle functionality
            $('#sidebarToggle, #sidebarToggleMain').click(function() {
                $('#youtubeSidebar').toggleClass('collapsed');
            });

            // Filter notifications
            $('#notificationFilter, #typeFilter, #dateFilter').change(function() {
                filterNotifications();
            });

            // Mark notification as read when clicked
            $('.notification-item').click(function(e) {
                if (!$(e.target).closest('.notification-actions').length) {
                    const notificationId = $(this).data('id');
                    if ($(this).hasClass('unread')) {
                        markAsRead(notificationId);
                    }
                }
            });

            // Mobile sidebar overlay
            $(document).click(function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('.youtube-sidebar, .sidebar-toggle-btn').length) {
                        $('#youtubeSidebar').removeClass('mobile-open');
                    }
                }
            });

            // Mobile sidebar toggle
            $('#sidebarToggle, #sidebarToggleMain').click(function() {
                if ($(window).width() <= 768) {
                    $('#youtubeSidebar').toggleClass('mobile-open');
                }
            });
        });

        function filterNotifications() {
            const statusFilter = $('#notificationFilter').val();
            const typeFilter = $('#typeFilter').val();
            const dateFilter = $('#dateFilter').val();

            $('.notification-item').each(function() {
                const $item = $(this);
                const isUnread = $item.hasClass('unread');
                const itemType = $item.data('type');
                
                let showItem = true;

                // Status filter
                if (statusFilter === 'unread' && !isUnread) {
                    showItem = false;
                } else if (statusFilter === 'read' && isUnread) {
                    showItem = false;
                }

                // Type filter
                if (typeFilter !== 'all' && itemType !== typeFilter) {
                    showItem = false;
                }

                // Date filter would require additional data attributes
                // Implementation depends on your specific needs

                $item.toggle(showItem);
            });
        }

        function markAsRead(notificationId) {
            $.ajax({
                url: `/notifications/${notificationId}/read`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $(`.notification-item[data-id="${notificationId}"]`)
                            .removeClass('unread')
                            .find('.badge-new')
                            .remove();
                        updateNotificationCount();
                        toastr.success('Notification marked as read');
                    }
                },
                error: function() {
                    toastr.error('Failed to mark notification as read');
                }
            });
        }

        function markAllAsRead() {
            if (!confirm('Mark all notifications as read?')) return;

            $.ajax({
                url: '/notifications/mark-all-read',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $('.notification-item.unread')
                            .removeClass('unread')
                            .find('.badge-new')
                            .remove();
                        updateNotificationCount();
                        toastr.success('All notifications marked as read');
                    }
                },
                error: function() {
                    toastr.error('Failed to mark all notifications as read');
                }
            });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;

            $.ajax({
                url: `/notifications/${notificationId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $(`.notification-item[data-id="${notificationId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        updateNotificationCount();
                        toastr.success('Notification deleted');
                    }
                },
                error: function() {
                    toastr.error('Failed to delete notification');
                }
            });
        }

        function clearAllNotifications() {
            if (!confirm('Clear all notifications? This action cannot be undone.')) return;

            $.ajax({
                url: '/notifications/clear-all',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        $('.notification-item').fadeOut(300, function() {
                            $('#notificationsContainer').html(`
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-bell fa-2x"></i>
                                    </div>
                                    <h3 class="empty-state-title">No notifications yet</h3>
                                    <p class="empty-state-message">You'll receive notifications about important updates and activities here.</p>
                                </div>
                            `);
                        });
                        updateNotificationCount();
                        toastr.success('All notifications cleared');
                    }
                },
                error: function() {
                    toastr.error('Failed to clear all notifications');
                }
            });
        }

        function updateNotificationCount() {
            const unreadCount = $('.notification-item.unread').length;
            $('.notification-badge').text(unreadCount > 0 ? unreadCount : '0');
            if (unreadCount === 0) {
                $('.notification-badge').hide();
            }
        }

        // Configure toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000"
        };
    </script>
</body>
</html>
