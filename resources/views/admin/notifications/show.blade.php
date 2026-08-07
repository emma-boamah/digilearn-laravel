@extends('layouts.admin')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')
@section('page-description', 'Review notification content, metadata, and external resource actions')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .notification-detail-wrapper {
        max-width: 960px;
        margin: 0 auto;
    }

    .detail-card-header {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .header-banner {
        padding: 2rem;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(15, 23, 42, 0.05) 100%);
        border-bottom: 1px solid var(--gray-200);
    }

    .avatar-icon-box {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .badge-status-read {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .badge-status-unread {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .external-action-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-radius: 0.875rem;
        padding: 1.25rem 1.5rem;
    }

    [data-theme="dark"] .external-action-box {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(30, 64, 175, 0.2) 100%);
        border-color: rgba(59, 130, 246, 0.4);
    }

    .code-container {
        background-color: #0f172a;
        color: #e2e8f0;
        border-radius: 0.75rem;
        font-family: 'JetBrains Mono', 'Fira Code', 'Monaco', monospace;
        font-size: 0.8125rem;
        line-height: 1.6;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
    }
</style>
@endpush

@section('content')
@php
    $title = $notification->title ?? $notification->data['title'] ?? 'Notification Details';
    $message = $notification->message ?? $notification->data['message'] ?? 'No detail message provided.';
    $actionUrl = $notification->url ?? $notification->data['url'] ?? null;
    $typeColor = $notification->notificationType?->color ?? '#2563eb';
    $typeIcon = $notification->notificationType?->icon ?? 'fas fa-bell';
    $isRead = !is_null($notification->read_at);
@endphp

<div class="notification-detail-wrapper space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.notifications.index') }}" class="btn-action bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Notifications</span>
        </a>

        <div class="flex items-center gap-3">
            <button id="deleteNotificationBtn" onclick="deleteNotification('{{ $notification->id }}')" class="btn-action bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40">
                <i class="fas fa-trash-alt"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="detail-card-header">
        <!-- Hero Header -->
        <div class="header-banner flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="avatar-icon-box flex-shrink-0" style="background-color: {{ $typeColor }};">
                    <i class="{{ $typeIcon }}"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            {{ $notification->notificationType?->name ?? 'System Alert' }}
                        </span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $isRead ? 'badge-status-read' : 'badge-status-unread' }}">
                            <i class="fas {{ $isRead ? 'fa-check-circle' : 'fa-envelope' }} mr-1 text-[10px]"></i>
                            {{ $isRead ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                        {{ $title }}
                    </h1>
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-col sm:items-end gap-1">
                <span><i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
                <span>{{ $notification->created_at->format('M j, Y • g:i A') }}</span>
            </div>
        </div>

        <!-- Content Body -->
        <div class="p-6 sm:p-8 space-y-6">
            <!-- Message Block -->
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Message Body</h3>
                <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 rounded-xl p-5 text-gray-800 dark:text-gray-200 text-sm leading-relaxed whitespace-pre-line">
                    {{ $message }}
                </div>
            </div>

            <!-- External Link / Action Callout -->
            @if($actionUrl)
            <div class="external-action-box flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 bg-blue-600 text-white rounded-lg flex-shrink-0 mt-0.5">
                        <i class="fas fa-external-link-alt text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Associated Resource / External Target</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 break-all max-w-lg">
                            {{ $actionUrl }}
                        </p>
                    </div>
                </div>
                <a href="{{ $actionUrl }}" target="_blank" rel="noopener noreferrer" class="btn-action bg-blue-600 hover:bg-blue-700 text-white shadow-md flex-shrink-0 w-full sm:w-auto text-center justify-center">
                    <span>Launch Resource</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            @endif

            <!-- Metadata Grid -->
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Notification Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Notification ID</span>
                        <span class="text-sm font-mono font-semibold text-gray-900 dark:text-white mt-1 block truncate" title="{{ $notification->id }}">
                            {{ $notification->id }}
                        </span>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Class / Event Type</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1 block truncate" title="{{ $notification->type }}">
                            {{ class_basename($notification->type) }}
                        </span>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Recipient Entity</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1 block">
                            {{ class_basename($notification->notifiable_type) }} #{{ $notification->notifiable_id }}
                        </span>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Read Timestamp</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1 block">
                            {{ $notification->read_at ? $notification->read_at->format('M j, Y • g:i A') : 'Not read yet' }}
                        </span>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Created Timestamp</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1 block">
                            {{ $notification->created_at->format('M j, Y • g:i A') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Data Payload Block -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Raw Payload Data</h3>
                    <button type="button" onclick="copyJsonData()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                        <i class="far fa-copy"></i> Copy JSON
                    </button>
                </div>
                <div class="code-container p-4 overflow-x-auto shadow-inner border border-gray-800">
                    <pre id="jsonPayload"><code class="language-json">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    async function deleteNotification(id) {
        if (!confirm('Are you sure you want to delete this notification?')) return;

        try {
            const response = await fetch(`/api/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                window.location.href = '{{ route("admin.notifications.index") }}';
            } else {
                alert('Failed to delete notification: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error('Error deleting notification:', e);
            alert('An error occurred while deleting the notification.');
        }
    }

    function copyJsonData() {
        const jsonText = document.getElementById('jsonPayload').innerText;
        navigator.clipboard.writeText(jsonText).then(() => {
            alert('Payload copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endsection