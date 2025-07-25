@extends('layouts.admin')

@section('title', 'Manage Learning Videos')
@section('page-title', 'Manage Learning Videos')
@section('page-description', 'Upload, edit, and organize educational videos.')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 flex items-center justify-between border border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Videos</h3>
                <p class="text-4xl font-bold text-blue-600 mt-2">{{ $totalVideos }}</p>
            </div>
            <i class="fas fa-video text-5xl text-blue-200"></i>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex items-center justify-between border border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Most Watched Video</h3>
                @if($mostWatchedVideo)
                    <p class="text-xl font-bold text-gray-800 mt-2 truncate">{{ $mostWatchedVideo->title }}</p>
                    <p class="text-sm text-gray-500">Views: {{ number_format($mostWatchedVideo->views) }}</p>
                @else
                    <p class="text-xl font-bold text-gray-800 mt-2">N/A</p>
                    <p class="text-sm text-gray-500">No videos yet</p>
                @endif
            </div>
            <i class="fas fa-eye text-5xl text-green-200"></i>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 flex items-center justify-between border border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Average Duration</h3>
                <p class="text-4xl font-bold text-purple-600 mt-2">{{ $averageDuration }}</p>
            </div>
            <i class="fas fa-clock text-5xl text-purple-200"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">All Videos</h2>
            <button onclick="openUploadModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                <i class="fas fa-upload mr-2"></i> Upload New Video
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <form action="{{ route('admin.content.videos.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
                <div class="col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Title/Description</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search videos..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div class="col-span-1">
                    <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-1">Filter by Grade</label>
                    <select name="grade_level" id="grade_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Grades</option>
                        @foreach($gradeLevels as $grade)
                            <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="is_featured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                    <select name="is_featured" id="is_featured" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All</option>
                        <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>Featured</option>
                        <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="upload_date" class="block text-sm font-medium text-gray-700 mb-1">Upload Date</label>
                    <input type="date" name="upload_date" id="upload_date" value="{{ request('upload_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div class="col-span-full md:col-span-1 flex justify-end md:justify-start">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition-colors flex items-center">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.content.videos.index') }}" class="ml-2 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Videos Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thumbnail</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Grade</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Duration</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Views</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Featured</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Uploaded Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($videos as $video)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($video->thumbnail_path)
                                <img src="{{ Storage::url($video->thumbnail_path) }}" alt="{{ $video->title }} Thumbnail" class="h-16 w-24 object-cover rounded-md">
                            @else
                                <div class="h-16 w-24 bg-gray-200 rounded-md flex items-center justify-center text-gray-500 text-xs">No Thumbnail</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $video->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $video->grade_level ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ gmdate("H:i:s", $video->duration_seconds) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($video->views) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.content.videos.toggle-feature', $video) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="text-sm font-medium {{ $video->is_featured ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600' }}">
                                    <i class="fas {{ $video->is_featured ? 'fa-star' : 'fa-star-half-alt' }}"></i>
                                    <span class="sr-only">{{ $video->is_featured ? 'Unfeature' : 'Feature' }}</span>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $video->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openEditModal({{ $video->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <form action="{{ route('admin.content.videos.destroy', $video) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">No videos found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $videos->links() }}
        </div>
    </div>

    <!-- Upload/Edit Video Modal -->
    <div id="videoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Upload New Video</h3>
                <button onclick="closeVideoModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-semibold">&times;</button>
            </div>
            <div class="mt-4">
                <form id="videoForm" method="POST" action="{{ route('admin.content.videos.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="video_id" id="videoId">

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Video Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="video_file" class="block text-sm font-medium text-gray-700">Upload Video File <span class="text-red-500">*</span></label>
                        <input type="file" name="video_file" id="video_file" accept="video/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Max file size: 500MB. Recommended duration: up to 60 minutes.</p>
                    </div>

                    <div class="mb-4">
                        <label for="thumbnail_file" class="block text-sm font-medium text-gray-700">Upload Thumbnail</label>
                        <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Max file size: 2MB. Formats: JPG, PNG, GIF.</p>
                        <div id="currentThumbnail" class="mt-2 hidden">
                            <p class="text-sm text-gray-600">Current Thumbnail:</p>
                            <img src="/placeholder.svg" alt="Current Thumbnail" class="h-24 w-auto object-cover rounded-md mt-1">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="grade_level" class="block text-sm font-medium text-gray-700">Grade Selection</label>
                        <select name="grade_level" id="grade_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Select Grade</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}">{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                    </div>

                    @if(Auth::user()->is_superuser)
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_featured" id="is_featured" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_featured" class="ml-2 block text-sm font-medium text-gray-700">Is Featured (Super Admin Only)</label>
                    </div>
                    @endif

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeVideoModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer>
        function openUploadModal() {
            resetModal();
            document.getElementById('modalTitle').textContent = 'Upload New Video';
            document.getElementById('videoForm').action = "{{ route('admin.content.videos.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('video_file').required = true;
            document.getElementById('videoModal').classList.remove('hidden');
        }

        function openEditModal(videoId) {
            resetModal();
            document.getElementById('modalTitle').textContent = 'Edit Video';
            document.getElementById('videoId').value = videoId;
            document.getElementById('video_file').required = false;
            
            fetch(`/admin/content/videos/${videoId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('title').value = data.title;
                    document.getElementById('grade_level').value = data.grade_level || '';
                    document.getElementById('description').value = data.description || '';
                    document.getElementById('is_featured').checked = data.is_featured;
                    
                    if (data.thumbnail_path) {
                        const img = document.querySelector('#currentThumbnail img');
                        img.src = `/storage/${data.thumbnail_path}`;
                        document.getElementById('currentThumbnail').classList.remove('hidden');
                    }
                    
                    document.getElementById('videoForm').action = `/admin/content/videos/${videoId}`;
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('videoModal').classList.remove('hidden');
                });
        }

        function closeVideoModal() {
            document.getElementById('videoModal').classList.add('hidden');
        }

        function resetModal() {
            document.getElementById('videoForm').reset();
            document.getElementById('currentThumbnail').classList.add('hidden');
            const img = document.querySelector('#currentThumbnail img');
            if (img) img.src = '';
        }
    </script>
@endpush
@endsection
