<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Notes - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-red: #E11E2D;
            --primary-red-hover: #c41e2a;
            --secondary-blue: #2677B8;
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
            min-height: 100vh;
        }

        /* Header */
        .header {
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            height: 60px;
        }

        .header-left {
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-right: 1px solid var(--gray-200);
            height: 100%;
        }

        .hamburger-menu {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
        }

        .hamburger-menu:hover {
            background-color: var(--gray-100);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
        }

        .header-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .shoutout-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shoutout-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .shoutout-tagline {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 280px;
            height: calc(100vh - 60px);
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            overflow-y: auto;
            z-index: 50;
        }

        .sidebar-content {
            padding: 1.5rem 0;
        }

        .sidebar-section {
            margin-bottom: 2rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 600;
        }

        .sidebar-menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-menu-text {
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: calc(100vh - 60px);
            background-color: var(--gray-25);
        }

        .content-header {
            background-color: var(--white);
            padding: 2rem 2rem 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: var(--white);
            color: var(--gray-600);
            border: 2px solid var(--gray-200);
        }

        .filter-tab.active {
            background-color: var(--primary-red);
            color: var(--white);
            border-color: var(--primary-red);
        }

        .filter-tab:hover:not(.active) {
            background-color: var(--gray-50);
            border-color: var(--gray-300);
        }

        /* Notes Grid */
        .notes-container {
            padding: 2rem;
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .note-card {
            background-color: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .note-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .note-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .note-subject {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .note-date {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
        }

        .note-actions {
            display: flex;
            gap: 0.75rem;
        }

        .note-action-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .note-action-btn.open {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .note-action-btn.open:hover {
            background-color: #1e5a8a;
        }

        .note-action-btn.delete {
            background-color: var(--white);
            color: var(--primary-red);
            border: 2px solid var(--primary-red);
        }

        .note-action-btn.delete:hover {
            background-color: var(--primary-red);
            color: var(--white);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            color: var(--gray-300);
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .empty-state-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .empty-state-btn:hover {
            background-color: var(--primary-red-hover);
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .notes-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .content-header {
                padding: 1.5rem 1rem 1rem;
            }

            .notes-container {
                padding: 1rem;
            }

            .notes-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .filter-tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <button class="hamburger-menu" id="hamburgerMenu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <a href="{{ route('dashboard.main') }}" class="logo">
                    <span class="logo-text">DigiLearn</span>
                </a>
            </div>
            
            <div class="header-right">
                <div class="shoutout-logo">
                    <div>
                        <div class="shoutout-text">ShoutOutGh</div>
                        <div class="shoutout-tagline">Educating through Entertainment</div>
                    </div>
                </div>
                
                <div class="user-menu">
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-section">
                <a href="{{ route('dashboard.main') }}" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-menu-text">Home</span>
                </a>
                
                <a href="{{ route('dashboard.digilearn') }}" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="sidebar-menu-text">Lessons</span>
                </a>
                
                <a href="{{ route('dashboard.notes') }}" class="sidebar-menu-item active">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="sidebar-menu-text">Notes</span>
                </a>
                
                <a href="#" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="sidebar-menu-text">Projects</span>
                </a>
                
                <a href="#" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="sidebar-menu-text">Personalized</span>
                </a>
                
                <a href="#" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6a2 2 0 002 2h4a2 2 0 002-2v-6M8 11h8"/>
                    </svg>
                    <span class="sidebar-menu-text">Shop</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Account</div>
                <a href="#" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-menu-text">Help & Information</span>
                </a>
                
                <a href="#" class="sidebar-menu-item">
                    <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-menu-text">Settings</span>
                </a>
                
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="sidebar-menu-item" style="border: none; background: none; width: 100%; text-align: left;">
                        <svg class="sidebar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="sidebar-menu-text">Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1 class="page-title">Notes</h1>
            <p class="page-subtitle">Manage your saved lesson notes</p>
            
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All</button>
                <button class="filter-tab" data-filter="dates">Dates</button>
            </div>
        </div>

        <div class="notes-container">
            @if(isset($notes) && count($notes) > 0)
                <div class="notes-grid">
                    @foreach($notes as $note)
                    <div class="note-card" data-note-id="{{ $note['id'] }}">
                        <h3 class="note-title">{{ $note['title'] }}</h3>
                        <p class="note-subject">{{ $note['subject'] }}</p>
                        <p class="note-date">{{ $note['created_at'] }}</p>
                        
                        <div class="note-actions">
                            <button class="note-action-btn open" onclick="window.location.href='{{ route('dashboard.notes.view', $note['id']) }}'">
                                <i class="fas fa-eye"></i>
                                Open
                            </button>
                            <button class="note-action-btn delete" onclick="deleteNote({{ $note['id'] }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- Sample notes for demo -->
                <div class="notes-grid">
                    @for($i = 1; $i <= 12; $i++)
                    <div class="note-card" data-note-id="{{ $i }}">
                        <h3 class="note-title">Living and Non Living organism</h3>
                        <p class="note-subject">(Science -Note G1-3)</p>
                        <p class="note-date">April 2025</p>
                        
                        <div class="note-actions">
                            <button class="note-action-btn open" onclick="window.location.href='{{ route('dashboard.notes.view', $i) }}'">
                                <i class="fas fa-eye"></i>
                                Open
                            </button>
                            <button class="note-action-btn delete" onclick="deleteNote({{ $i }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endfor
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            initializeFilterTabs();
            initializeNoteActions();
        });

        function initializeSidebar() {
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('sidebar');

            hamburgerMenu.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !hamburgerMenu.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        }

        function initializeFilterTabs() {
            const filterTabs = document.querySelectorAll('.filter-tab');
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    filterNotes(filter);
                });
            });
        }

        function filterNotes(filter) {
            const noteCards = document.querySelectorAll('.note-card');
            
            noteCards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else if (filter === 'dates') {
                    // Implement date-based filtering logic here
                    card.style.display = 'block';
                }
            });
        }

        function initializeNoteActions() {
            const noteCards = document.querySelectorAll('.note-card');
            
            noteCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on action buttons
                    if (e.target.closest('.note-action-btn')) {
                        return;
                    }
                    
                    const noteId = this.dataset.noteId;
                    window.location.href = `/dashboard/notes/${noteId}`;
                });
            });
        }

        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                // Add loading state
                const noteCard = document.querySelector(`[data-note-id="${noteId}"]`);
                noteCard.style.opacity = '0.5';
                
                // Simulate API call
                setTimeout(() => {
                    noteCard.remove();
                    
                    // Show success message
                    showSuccessMessage('Note deleted successfully!');
                }, 500);
            }
        }

        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #10b981;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            successDiv.textContent = message;
            
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }
    </script>
</body>
</html>
