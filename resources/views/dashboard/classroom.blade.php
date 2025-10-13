@extends('layouts.dashboard-simple') {{-- Assuming you have a basic dashboard layout --}}

@section('content')
<style>
    :root {
        --primary-red: #E11E2D;
        --secondary-blue: #2677B8;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --success-green: #10b981;
    }
    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--gray-100);
        color: var(--gray-900);
    }
    .classroom-container {
        display: flex;
        min-height: calc(100vh - 64px); /* Assuming a 64px fixed header */
        background-color: var(--gray-50);
        color: var(--gray-900);
    }
    .main-classroom-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 1rem;
        gap: 1rem;
    }
    .video-grid {
        flex: 1;
        background-color: var(--gray-800); /* Dark background for video area */
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .video-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: var(--gray-900);
        color: var(--gray-400);
        font-size: 1.25rem;
        text-align: center;
    }
    .video-placeholder svg {
        margin-bottom: 1rem;
    }
    .control-bar {
        background-color: var(--gray-800);
        padding: 1rem;
        border-radius: 0.75rem;
        display: flex;
        justify-content: center;
        gap: 1.5rem;
    }
    .control-button {
        background: var(--gray-700);
        color: var(--white);
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s ease;
    }
    .control-button:hover {
        background: var(--secondary-blue);
    }
    .control-button.active {
        background: var(--primary-red);
    }
    .classroom-sidebar {
        width: 320px;
        background-color: var(--white);
        border-left: 1px solid var(--gray-200);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .sidebar-section {
        background-color: var(--gray-50);
        border-radius: 0.5rem;
        padding: 1rem;
        border: 1px solid var(--gray-200);
    }
    .sidebar-section h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: var(--gray-800);
    }
    .participants-list, .chat-window, .lms-materials {
        font-size: 0.9rem;
        color: var(--gray-600);
    }
    .participant-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .participant-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: var(--primary-red);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 500;
        flex-shrink: 0;
    }
    .chat-message {
        margin-bottom: 0.5rem;
    }
    .chat-message strong {
        color: var(--primary-red);
    }
    .lms-materials ul {
        list-style: none;
        padding: 0;
    }
    .lms-materials li {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--gray-700);
    }
    .lms-materials a {
        color: var(--secondary-blue);
        text-decoration: none;
    }
    .lms-materials a:hover {
        text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .classroom-container {
            flex-direction: column;
        }
        .classroom-sidebar {
            width: 100%;
            border-left: none;
            border-top: 1px solid var(--gray-200);
            padding-top: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .main-classroom-area {
            padding: 0.75rem;
        }
        .control-bar {
            flex-wrap: wrap;
            padding: 0.75rem;
        }
        .control-button {
            width: calc(50% - 0.75rem); /* Two buttons per row */
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.9rem;
        }
    }
</style>

<header class="header">
    <div class="header-left">
        <button class="back-button" onclick="window.location.href='{{ route('dashboard.main') }}'">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="sidebar-logo">
            <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
        </div>
    </div>
    <h1 class="page-title">Virtual Classroom: {{ $virtualClass->topic ?? 'Live Session' }}</h1>
    <div class="header-right">
        <div class="user-avatar-header">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>
</header>

<div class="classroom-container">
    <div class="main-classroom-area">
        <div class="video-grid">
            <div class="video-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-webcam"><circle cx="12" cy="10" r="8"/><circle cx="12" cy="10" r="3"/><path d="M7 22h10c1.5 0 3-2 3-2L19 4H5L4 20c0 0 1.5 2 3 2Z"/></svg>
                <span>Live Class for {{ $virtualClass->grade_level ?? 'your level' }}</span>
                <p>Room ID: <strong>{{ $virtualClass->room_id }}</strong></p>
                <p>Waiting for instructor...</p>
            </div>
            {{-- This is where live video streams would be displayed --}}
        </div>
        <div class="control-bar">
            <button class="control-button" id="toggleMute">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                <span>Mute</span>
            </button>
            <button class="control-button" id="toggleVideo">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-video"><path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="8" width="14" height="8" rx="2" ry="2"/></svg>
                <span>Stop Video</span>
            </button>
            <button class="control-button" id="screenShare">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor-dot"><circle cx="12" cy="12" r="1"/><rect width="20" height="14" x="2" y="3" rx="2"/><path d="M12 17v4"/><path d="M8 21h8"/></svg>
                <span>Share Screen</span>
            </button>
            <button class="control-button" id="raiseHand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hand-raised"><path d="M7 12V6a3 3 0 0 1 6 0v4h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2H4a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h1.5L7 12Z"/></svg>
                <span>Raise Hand</span>
            </button>
            <button class="control-button" style="background-color: var(--primary-red);" onclick="leaveClass()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="17 16 22 12 17 8"/><line x1="22" x2="10" y1="12" y2="12"/></svg>
                <span>Leave Class</span>
            </button>
        </div>
    </div>
    <div class="classroom-sidebar">
        <div class="sidebar-section">
            <h4>Participants ({{ count($virtualClass->participants) }})</h4>
            <div class="participants-list">
                @foreach($virtualClass->participants as $participant)
                    <div class="participant-item">
                        <div class="participant-avatar">{{ substr($participant->name, 0, 1) }}</div>
                        <span>{{ $participant->name }} @if($participant->id === Auth::id()) (You) @endif</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="sidebar-section">
            <h4>Class Chat</h4>
            <div class="chat-window" style="max-height: 200px; overflow-y: auto;">
                {{-- Placeholder chat messages --}}
                <p class="chat-message"><strong>System:</strong> Welcome to the class!</p>
                <p class="chat-message"><strong>Teacher:</strong> Hello everyone, let's begin!</p>
                <p class="chat-message"><strong>{{ Auth::user()->name }}:</strong> Hi everyone!</p>
            </div>
            <div class="chat-input-area" style="margin-top: 1rem;">
                <input type="text" placeholder="Type your message..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.5rem;">
                <button style="background-color: var(--secondary-blue); color: var(--white); border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; margin-top: 0.5rem; cursor: pointer;">Send</button>
            </div>
        </div>
        <div class="sidebar-section">
            <h4>LMS Materials</h4>
            <div class="lms-materials">
                <ul>
                    <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>Lesson Notes (PDF)</a></li>
                    <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation"><path d="M2 3h20"/><path d="M20 17H4"/><path d="M12 17V3"/><path d="m8 21 4-4 4 4"/></svg>Presentation Slides</a></li>
                    <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>External Resources</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Basic JS for control buttons (for visual feedback, no actual functionality)
    document.getElementById('toggleMute')?.addEventListener('click', function() {
        this.classList.toggle('active');
        this.querySelector('span').textContent = this.classList.contains('active') ? 'Unmute' : 'Mute';
        this.querySelector('svg').innerHTML = this.classList.contains('active') ? '<path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/><line x1="2" x2="22" y1="2" y2="22"/>' : '<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/>';
    });

    document.getElementById('toggleVideo')?.addEventListener('click', function() {
        this.classList.toggle('active');
        this.querySelector('span').textContent = this.classList.contains('active') ? 'Start Video' : 'Stop Video';
        this.querySelector('svg').innerHTML = this.classList.contains('active') ? '<path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="8" width="14" height="8" rx="2" ry="2"/><line x1="2" x2="22" y1="2" y2="22"/>' : '<path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="8" width="14" height="8" rx="2" ry="2"/>';
    });

    document.getElementById('screenShare')?.addEventListener('click', function() {
        alert('Screen sharing initiated (placeholder).');
        this.classList.toggle('active'); // Toggle for visual feedback
    });

    document.getElementById('raiseHand')?.addEventListener('click', function() {
        alert('Hand raised (placeholder).');
        this.classList.toggle('active'); // Toggle for visual feedback
    });

    function leaveClass() {
        if (confirm('Are you sure you want to leave the class?')) {
            // In a real application, you'd send an AJAX request to update user status
            // For now, redirect to dashboard.
            window.location.href = '{{ route('dashboard.main') }}';
            // Optionally, update user's is_online status on backend
            // fetch('/api/user/leave-class', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        }
    }
    
    // Ping server every minute to keep user status updated
    setInterval(() => {
        fetch('{{ route('ping') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => console.log('Status updated'));
    }, 60000); // Ping every minute
</script>
@endsection
