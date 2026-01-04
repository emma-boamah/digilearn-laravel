@extends('layouts.dashboard-components')

@section('head')
    <title>{{ $notification->title }} - Digital Learning Platform</title>
@endsection

@push('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* Notification Show Styles */
        .notification-detail-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            margin-top: 60px;
        }

        .notification-detail-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .notification-header {
            padding: 2rem;
            border-bottom: 1px solid var(--gray-100);
            background: var(--gray-50);
        }

        .notification-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .notification-body {
            padding: 2rem;
        }

        .notification-message {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--gray-700);
            margin-bottom: 2rem;
        }


        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--primary-red);
        }

        @media (max-width: 640px) {
            .notification-detail-container { padding: 1rem; }
            .notification-header, .notification-body { padding: 1.5rem; }
        }
    </style>
@endpush

@section('content')
    <div class="notification-detail-container">
        <a href="{{ route('dashboard.notifications') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Notifications
        </a>

        <div class="notification-detail-card">
            <div class="notification-header">
                <h1 class="notification-title">{{ $notification->title }}</h1>
                <div class="notification-meta">
                    <span><i class="fas fa-clock"></i> {{ $notification->created_at->format('M j, Y \a\t g:i A') }}</span>
                    @if($notification->isRead())
                        <span><i class="fas fa-check"></i> Read</span>
                    @else
                        <span><i class="fas fa-envelope"></i> Unread</span>
                    @endif
                </div>
            </div>

            <div class="notification-body">
                <div class="notification-message">
                    {!! nl2br(e($notification->message)) !!}
                </div>
            </div>
        </div>
    </div>
    @endsection
</body>
</html>
