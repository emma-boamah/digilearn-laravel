@extends('layouts.admin')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')
@section('page-description', 'View detailed information about this notification')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .notification-detail {
        max-width: 800px;
        margin: 0 auto;
    }

    .detail-card {
        background: var(--white);
        border-radius: 12px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .detail-header {
        padding: 2rem;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .detail-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 0.5rem 0;
    }

    .detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .detail-body {
        padding: 2rem;
    }

    .detail-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .detail-label {
        font-weight: 500;
        color: var(--gray-700);
        font-size: 0.875rem;
    }

    .detail-value {
        color: var(--gray-900);
        font-size: 0.875rem;
        word-break: break-word;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .status-badge.read {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.unread {
        background: #fef3c7;
        color: #92400e;
    }

    .data-json {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        padding: 1rem;
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 0.875rem;
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 300px;
        overflow-y: auto;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-bottom: 2rem;
    }

    .back-button:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
        color: var(--gray-900);
    }

    .notification-avatar {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .notification-avatar-bg {
        background-color: {{ $notification->notificationType?->color ?? '#2563eb' }};
    }
</style>
@endpush

@section('content')
<div class="notification-detail">
    <a href="{{ route('admin.notifications.index') }}" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Notifications
    </a>

    <div class="detail-card">
        <div class="detail-header">
            <div class="notification-avatar notification-avatar-bg">
                <i class="{{ $notification->notificationType?->icon ?? 'fas fa-bell' }}"></i>
            </div>
            <h1 class="detail-title">{{ $notification->title }}</h1>
            <div class="detail-meta">
                <span><i class="fas fa-calendar"></i> {{ $notification->created_at->format('M j, Y g:i A') }}</span>
                <span><i class="fas fa-user"></i> {{ class_basename($notification->notifiable_type) }} #{{ $notification->notifiable_id }}</span>
                <span class="status-badge {{ $notification->read_at ? 'read' : 'unread' }}">
                    {{ $notification->read_at ? 'Read' : 'Unread' }}
                </span>
            </div>
        </div>

        <div class="detail-body">
            <div class="detail-section">
                <h2 class="section-title">Notification Details</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">ID</div>
                        <div class="detail-value">{{ $notification->id }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Type</div>
                        <div class="detail-value">{{ $notification->type }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Notification Type</div>
                        <div class="detail-value">{{ $notification->notificationType?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Read At</div>
                        <div class="detail-value">{{ $notification->read_at ? $notification->read_at->format('M j, Y g:i A') : 'Not read yet' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h2 class="section-title">Message</h2>
                <div class="detail-value">{{ $notification->message }}</div>
            </div>

            @if($notification->url)
            <div class="detail-section">
                <h2 class="section-title">URL</h2>
                <div class="detail-value">
                    <a href="{{ $notification->url }}" target="_blank" rel="noopener noreferrer">
                        {{ $notification->url }}
                        <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
            </div>
            @endif

            <div class="detail-section">
                <h2 class="section-title">Raw Data</h2>
                <div class="data-json">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection