<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mt-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Grade Level Notifications</h3>
            <p class="text-gray-600 text-sm mt-1">
                Select the grade levels you want to receive new content notifications for. Uncheck to opt-out.
            </p>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($allGradeLevels as $grade)
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="grade_notif_{{ \Illuminate\Support\Str::slug($grade) }}" 
                    value="{{ $grade }}"
                    class="grade-notification-toggle w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                    {{ !in_array($grade, $gradeOptOuts) ? 'checked' : '' }}
                    onchange="toggleGradeNotification('{{ $grade }}', this.checked)"
                >
                <label for="grade_notif_{{ \Illuminate\Support\Str::slug($grade) }}" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer select-none">
                    {{ $grade }}
                </label>
            </div>
        @endforeach
    </div>
    <div id="grade-notification-feedback" class="mt-3 text-sm hidden"></div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function toggleGradeNotification(gradeLevel, isChecked) {
        // If checked, optOut is false (user wants notifications). 
        // If unchecked, optOut is true (user opts out).
        const optOut = !isChecked;
        const feedbackEl = document.getElementById('grade-notification-feedback');
        
        fetch('{{ route('api.notifications.grade-opt-out') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                grade_level: gradeLevel,
                opt_out: optOut
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                feedbackEl.textContent = 'Preferences saved.';
                feedbackEl.className = 'mt-3 text-sm text-green-600';
                feedbackEl.classList.remove('hidden');
                setTimeout(() => {
                    feedbackEl.classList.add('hidden');
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to update');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackEl.textContent = 'Failed to save preference. Please try again.';
            feedbackEl.className = 'mt-3 text-sm text-red-600';
            feedbackEl.classList.remove('hidden');
            
            // Revert checkbox state visually
            const slug = gradeLevel.toLowerCase().replace(/ /g, '-');
            const checkbox = document.getElementById('grade_notif_' + slug);
            if(checkbox) checkbox.checked = !isChecked;
        });
    }
</script>