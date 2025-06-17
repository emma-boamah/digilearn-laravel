@extends('layouts.dashboard-simple')

@section('content')
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <button class="back-button" onclick="window.history.back()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </button>
                    
                    <div class="shoutout-logo">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                        </svg>
                        <div class="brand-section sidebar-logo">
                            <img src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                        </div>
                    </div>
                </div>
                
                <h1 class="page-title">Dashboard</h1>
                
                <div class="header-right">
                    <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                    </svg>
                    
                    <div class="header-divider"></div>
                    
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="four-column-grid">
                @foreach($levels as $level)
                <div class="card">
                    <div class="card-image">
                        @if($level['has_illustration'])
                            <img src="https://via.placeholder.com/400x200/3B82F6/ffffff?text=Educational+Illustration" alt="{{ $level['title'] }}">
                        @else
                            <img src="https://via.placeholder.com/400x200/9CA3AF/ffffff?text={{ urlencode($level['title']) }}" alt="{{ $level['title'] }}">
                        @endif
                    </div>
                    <h3 class="card-title">{{ $level['title'] }}</h3>
                    <p class="card-description">{{ $level['description'] }}</p>
                    <form action="{{ route('dashboard.select-level', $level['id']) }}" method="POST">
                        @csrf
                        <button type="submit" class="card-button">Select</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </main>
@endsection
