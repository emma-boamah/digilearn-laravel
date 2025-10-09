<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Ratings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rating-stars {
            display: flex;
            gap: 0.125rem;
        }

        .star {
            width: 16px;
            height: 16px;
            color: #d1d5db;
            transition: color 0.2s ease;
        }

        .star.filled {
            color: #f59e0b;
        }

        .rating-bar {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #f97316);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
            </div>
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="{{ route('admin.content') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-file-alt mr-3"></i> Content
                </a>
                <a href="{{ route('admin.content.quizzes.index') }}" class="flex items-center px-6 py-3 bg-blue-50 text-blue-700">
                    <i class="fas fa-question-circle mr-3"></i> Quizzes
                </a>
                <a href="{{ route('admin.content.quizzes.ratings') }}" class="flex items-center px-6 py-3 bg-blue-100 text-blue-800 border-r-4 border-blue-500">
                    <i class="fas fa-star mr-3"></i> Quiz Ratings
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-users mr-3"></i> Users
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">Quiz Ratings Overview</h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                            <a href="{{ route('logout') }}" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-question-circle text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Quizzes</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_quizzes'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Rated Quizzes</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['rated_quizzes'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avg Rating</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Ratings</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_ratings']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating Distribution Chart -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Rating Distribution</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @for($i = 5; $i >= 1; $i--)
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-1 w-16">
                                    <span class="text-sm font-medium">{{ $i }}</span>
                                    <i class="fas fa-star text-yellow-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: {{ $ratingDistribution[$i] ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div class="w-12 text-right">
                                    <span class="text-sm text-gray-600">{{ $ratingDistribution[$i] ?? 0 }}%</span>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Quiz Ratings Table -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Quiz Ratings</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ratings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($quizzes as $quiz)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $quiz->title }}</div>
                                                <div class="text-sm text-gray-500">{{ $quiz->grade_level }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $quiz->subject ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($quiz->average_rating > 0)
                                        <div class="flex items-center space-x-2">
                                            <div class="rating-stars">
                                                @php
                                                    $rating = round($quiz->average_rating);
                                                @endphp
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="star {{ $i <= $rating ? 'filled' : '' }}" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-sm font-medium">{{ number_format($quiz->average_rating, 1) }}</span>
                                        </div>
                                        @else
                                        <span class="text-sm text-gray-400">No ratings</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $quiz->total_ratings }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($quiz->attempts_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.content.quizzes.edit', $quiz) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="{{ route('admin.content.quizzes.show', $quiz) }}" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No quizzes found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Add any interactive functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Rating distribution animation
            const ratingBars = document.querySelectorAll('.rating-bar-fill');
            ratingBars.forEach((bar, index) => {
                setTimeout(() => {
                    bar.style.width = bar.style.width;
                }, index * 100);
            });
        });
    </script>
</body>
</html>