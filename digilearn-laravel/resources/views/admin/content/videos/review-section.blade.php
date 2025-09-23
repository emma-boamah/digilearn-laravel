<!-- Video Review Section -->
@if($pendingCount > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Videos Pending Review</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $pendingCount }} video(s) waiting for approval</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                {{ $pendingCount }} Pending
            </span>
        </div>
    </div>
    
    <div class="p-6">
        <div class="space-y-4">
            @foreach($pendingVideos as $video)
            <div class="border border-gray-200 rounded-lg p-4 flex items-center space-x-4">
                <!-- Thumbnail -->
                <div class="flex-shrink-0 w-24 h-16 bg-gray-100 rounded overflow-hidden">
                    @if($video->thumbnail_path)
                        <img src="{{ asset('storage/' . $video->thumbnail_path) }}" 
                             alt="{{ $video->title }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-video text-gray-400"></i>
                        </div>
                    @endif
                </div>
                
                <!-- Video Info -->
                <div class="flex-grow">
                    <h3 class="font-semibold text-gray-900">{{ $video->title }}</h3>
                    <div class="text-sm text-gray-600 mt-1">
                        <span>By {{ $video->uploader->name }}</span> • 
                        <span>{{ $video->created_at->diffForHumans() }}</span> • 
                        <span>{{ $video->getFormattedFileSize() }}</span>
                        @if($video->grade_level)
                        • <span>{{ $video->grade_level }}</span>
                        @endif
                    </div>
                    @if($video->description)
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $video->description }}</p>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="flex-shrink-0 flex items-center space-x-2">
                    <button data-action="preview" data-video-id="{{ $video->id }}" 
                            class="video-action-btn px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button data-action="approve" data-video-id="{{ $video->id }}" 
                            class="video-action-btn px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                    <button data-action="reject" data-video-id="{{ $video->id }}" 
                            class="video-action-btn px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Video Preview Modal -->
<div id="videoPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold" id="previewTitle">Video Preview</h3>
                    <button id="closePreviewBtn" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Video Player -->
                <div class="aspect-video bg-black rounded mb-4">
                    <video id="previewPlayer" class="w-full h-full" controls preload="metadata">
                        <source id="videoSource" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                
                <!-- Video Details -->
                <div id="previewDetails" class="space-y-2 text-sm text-gray-600">
                    <!-- Details will be populated by JavaScript -->
                </div>
                
                <!-- Review Actions -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button id="closePreviewModalBtn" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                        Close
                    </button>
                    <button id="previewRejectBtn" 
                            class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                        Reject
                    </button>
                    <button id="previewApproveBtn" 
                            class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Approve & Upload to Vimeo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    let currentVideoId = null;

    // Add event listeners for video action buttons
    document.querySelectorAll('.video-action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const videoId = this.dataset.videoId;
            
            if (action === 'preview') {
                previewVideo(videoId);
            } else if (action === 'approve') {
                approveVideo(videoId);
            } else if (action === 'reject') {
                rejectVideo(videoId);
            }
        });
    });

    // Close modal event listeners
    document.getElementById('closePreviewBtn').addEventListener('click', closePreviewModal);
    document.getElementById('closePreviewModalBtn').addEventListener('click', closePreviewModal);

    function previewVideo(videoId) {
        currentVideoId = videoId;
        
        fetch(`/admin/content/videos/${videoId}/preview`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('previewTitle').textContent = data.title;
                
                // Set video source properly
                const videoPlayer = document.getElementById('previewPlayer');
                const videoSource = document.getElementById('videoSource');
                
                videoSource.src = data.video_url;
                videoPlayer.load(); // Reload the video element
                
                document.getElementById('previewDetails').innerHTML = `
                    <div><strong>Description:</strong> ${data.description || 'No description'}</div>
                    <div><strong>Grade Level:</strong> ${data.grade_level || 'Not specified'}</div>
                    <div><strong>Duration:</strong> ${Math.floor(data.duration / 60)}:${(data.duration % 60).toString().padStart(2, '0')}</div>
                    <div><strong>File Size:</strong> ${data.file_size}</div>
                    <div><strong>Uploaded by:</strong> ${data.uploaded_by}</div>
                    <div><strong>Upload Date:</strong> ${data.uploaded_at}</div>
                    ${data.expires_at ? `<div class="text-red-600"><strong>Expires:</strong> ${data.expires_at}</div>` : ''}
                `;
                
                // Update modal action buttons
                document.getElementById('previewApproveBtn').onclick = () => approveVideo(videoId);
                document.getElementById('previewRejectBtn').onclick = () => rejectVideo(videoId);
                
                document.getElementById('videoPreviewModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading video preview:', error);
                alert('Error loading video preview');
            });
    }

    function closePreviewModal() {
        document.getElementById('videoPreviewModal').classList.add('hidden');
        
        // Properly reset video
        const videoPlayer = document.getElementById('previewPlayer');
        const videoSource = document.getElementById('videoSource');
        
        videoPlayer.pause();
        videoSource.src = '';
        videoPlayer.load();
        
        currentVideoId = null;
    }

    function approveVideo(videoId) {
        if (confirm('Are you sure you want to approve this video? It will be uploaded to Vimeo.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/content/videos/${videoId}/approve`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectVideo(videoId) {
        const reason = prompt('Please provide a reason for rejection (optional):');
        if (reason !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/content/videos/${videoId}/reject`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            if (reason) {
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'review_notes';
                reasonInput.value = reason;
                form.appendChild(reasonInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>
