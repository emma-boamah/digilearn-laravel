<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ShoutoutGH</title>

    <!-- Tailwind CSS -->
    @if(app()->environment('production'))
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @else
        <!-- Development fallback: CDN (not for production) -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link-text {
            display: block;
        }
        .sidebar.collapsed .sidebar-link-text {
            display: none;
        }
        .sidebar.collapsed .sidebar-toggle-icon {
            transform: rotate(180deg);
        }
        .sidebar-toggle-icon {
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-gray-900 text-white w-64 min-h-screen p-4 flex flex-col relative transition-all duration-300 ease-in-out">
            <div class="flex items-center mb-8 justify-between">
                <div class="flex items-center">
                    <div class="bg-blue-600 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold sidebar-link-text">ShoutoutGH Admin</h1>
                </div>
                <button id="sidebar-toggle" class="text-white focus:outline-none absolute right-4 top-4">
                    <i class="fas fa-chevron-left sidebar-toggle-icon"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>

                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Users</span>
                </a>

                <!-- Content Management Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('admin.content*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 rounded-lg {{ request()->routeIs('admin.content*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                        <div class="flex items-center">
                            <i class="fas fa-folder-open w-5 h-5 mr-3"></i>
                            <span class="sidebar-link-text">Content Management</span>
                        </div>
                        <i class="fas fa-chevron-down sidebar-link-text transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-cloak class="ml-6 mt-1 space-y-1 sidebar-link-text">
                        <a href="{{ route('admin.content.videos.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.content.videos*') ? 'bg-blue-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-video w-4 h-4 mr-3"></i>
                            Manage Learning Videos
                        </a>
                        <a href="{{ route('admin.content.quizzes.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.content.quizzes*') ? 'bg-blue-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-question-circle w-4 h-4 mr-3"></i>
                            Manage Quizzes
                        </a>
                        <a href="{{ route('admin.content.documents.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.content.documents*') ? 'bg-blue-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-file-alt w-4 h-4 mr-3"></i>
                            Manage Documents
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.revenue') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.revenue*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Revenue Analytics</span>
                </a>

                <a href="#" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.analytics*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-chart-line w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Reports & Web Analytics</span>
                </a>

                <a href="#" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.security*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-shield-alt w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Security</span>
                </a>

                <a href="#" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.settings*') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-cog w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Settings</span>
                </a>

                @if(Auth::user()->is_superuser)
                <a href="{{ route('admin.classes.create') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.classes.create') ? 'bg-blue-600' : 'hover:bg-gray-800' }}">
                    <i class="fas fa-chalkboard-teacher w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Create Class</span>
                </a>
                @endif
            </nav>

            <div class="mt-auto pt-4 border-t border-gray-700">
                <a href="{{ route('dashboard.main') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800">
                    <i class="fas fa-arrow-left w-5 h-5 mr-3"></i>
                    <span class="sidebar-link-text">Back to Site</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 w-full text-left">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        <span class="sidebar-link-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-gray-600">@yield('page-description', 'Welcome to the admin dashboard')</p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-bell w-6 h-6"></i>
                                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                                </button>

                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="open = false"
                                     x-cloak
                                     class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50">
                                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                        <strong>Recent Notifications</strong>
                                    </div>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="font-medium">New user registered</div>
                                        <div class="text-gray-500">john@example.com - 2 minutes ago</div>
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="font-medium">Security alert</div>
                                        <div class="text-gray-500">Multiple failed login attempts - 5 minutes ago</div>
                                    </a>
                                </div>
                            </div>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="ml-2 text-gray-700">{{ Auth::user()->name }}</span>
                                </button>

                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="open = false"
                                     x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer crossorigin="anonymous">
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('#sidebar-toggle');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-chevron-left');
                icon.classList.toggle('fa-chevron-right');
            });
        });

        setInterval(() => {
            if (document.visibilityState === 'visible') {
                fetch('/ping', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
        }, 300000); // 5 minutes
    </script>
    @stack('scripts')
</body>
</html>
