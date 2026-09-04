@extends('layouts.admin')

@section('title', 'Tutor Verification Detail')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.tutors.index') }}" class="text-sm text-blue-600 dark:text-blue-400 font-medium hover:underline flex items-center gap-1 mb-1">
                <i class="fas fa-arrow-left"></i> Back to Tutor Applications
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Tutor: {{ $tutorProfile->user->name ?? 'Applicant' }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @if($tutorProfile->is_approved)
                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full">
                    <i class="fas fa-check-circle mr-1"></i> Approved
                </span>
                <form method="POST" action="{{ route('admin.tutors.reject', $tutorProfile->id) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-xs font-semibold rounded-lg hover:bg-amber-700 transition">
                        Revoke Approval
                    </button>
                </form>
            @else
                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-full">
                    <i class="fas fa-clock mr-1"></i> Pending Verification
                </span>
                <form method="POST" action="{{ route('admin.tutors.approve', $tutorProfile->id) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition shadow">
                        <i class="fas fa-check mr-1"></i> Approve Tutor Application
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Profile Info & Docs -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">Profile & Bio</h3>
                
                <div class="flex items-start gap-4 mb-6">
                    @if($tutorProfile->headshot_path)
                        <img src="{{ asset('storage/' . $tutorProfile->headshot_path) }}" alt="Headshot" class="w-20 h-20 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm">
                    @else
                        <div class="w-20 h-20 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-2xl">
                            {{ strtoupper(substr($tutorProfile->user->name ?? 'T', 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $tutorProfile->legal_name ?? $tutorProfile->user->name }}</h4>
                        <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">{{ $tutorProfile->tagline ?? 'Tutor Applicant' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $tutorProfile->user->email }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Hourly Rate</span>
                        <div class="text-sm font-bold text-emerald-600">{{ $tutorProfile->rate_range }}/hr</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Submission Date</span>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $tutorProfile->created_at ? $tutorProfile->created_at->format('M d, Y') : 'N/A' }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Biography & Background</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line leading-relaxed">{{ $tutorProfile->bio ?? 'No biography provided.' }}</p>
                </div>

                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Educational Qualifications</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line leading-relaxed">{{ $tutorProfile->qualifications ?? 'No qualification text provided.' }}</p>
                </div>
            </div>

            <!-- Selected Teaching Subjects & Rates Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Selected Teaching Subjects & Rates</h3>
                    <span class="bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $tutorProfile->tutorSubjects->count() }} Subject(s)
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($tutorProfile->tutorSubjects as $tutorSubject)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fas fa-book"></i>
                                </div>
                                <span class="font-semibold text-sm text-gray-900 dark:text-white">
                                    {{ $tutorSubject->subject->name ?? 'Subject #' . $tutorSubject->subject_id }}
                                </span>
                            </div>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-md">
                                GHS {{ number_format($tutorSubject->hourly_rate, 2) }}/hr
                            </span>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-4 text-sm text-gray-500 italic">
                            No subject rates configured for this applicant.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Documents & Identity Verification Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">Attached Documents & Verification Files</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Headshot File -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Applicant Headshot Photo</div>
                        @if($tutorProfile->headshot_path)
                            <div class="flex items-center gap-3">
                                <img src="{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'headshot']) }}" class="w-12 h-12 rounded object-cover border border-gray-200 dark:border-gray-700">
                                <button type="button" onclick="openDocModal('Applicant Headshot Photo', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'headshot']) }}', 'image')" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-expand mr-1"></i> Preview Headshot
                                </button>
                            </div>
                        @else
                            <span class="text-sm text-gray-400 italic">Not uploaded</span>
                        @endif
                    </div>

                    <!-- ID Document -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Government ID Verification</div>
                            @if($tutorProfile->id_type)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 uppercase">
                                    {{ str_replace('_', ' ', $tutorProfile->id_type) }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2">
                            @if($tutorProfile->id_document_path)
                                <button type="button" onclick="openDocModal('Government ID (Front / Primary)', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'id_document']) }}', 'auto')" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                    <i class="fas fa-id-card text-blue-500 text-base"></i> View ID (Front / Main Page)
                                </button>
                            @else
                                <span class="text-sm text-gray-400 italic">Front side not uploaded</span>
                            @endif

                            @if($tutorProfile->id_document_back_path)
                                <button type="button" onclick="openDocModal('Government ID (Back Side)', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'id_document_back']) }}', 'auto')" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                    <i class="fas fa-id-card-clip text-indigo-500 text-base"></i> View ID (Back Side)
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Tax Document -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Tax / TIN Document</div>
                        @if($tutorProfile->tax_document_path)
                            <button type="button" onclick="openDocModal('Tax / TIN Document', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'tax_document']) }}', 'auto')" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                <i class="fas fa-file-invoice text-emerald-500 text-lg"></i> View Tax Document
                            </button>
                        @else
                            <span class="text-sm text-gray-400 italic">Not uploaded</span>
                        @endif
                    </div>

                    <!-- Certificates -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Teaching Certificates</div>
                        @if($tutorProfile->certificates_path)
                            <button type="button" onclick="openDocModal('Teaching Certificates', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'certificates']) }}', 'auto')" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                <i class="fas fa-certificate text-amber-500 text-lg"></i> View Certificates
                            </button>
                        @else
                            <span class="text-sm text-gray-400 italic">Not uploaded</span>
                        @endif
                    </div>

                    <!-- Intro / Sample Video -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700 md:col-span-2">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Sample Teaching Video</div>
                        @if($tutorProfile->intro_video_url)
                            <a href="{{ $tutorProfile->intro_video_url }}" target="_blank" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5">
                                <i class="fas fa-video text-purple-500 text-lg"></i> Watch Intro Video Link ({{ $tutorProfile->intro_video_url }})
                            </a>
                        @elseif($tutorProfile->test_video_path)
                            <button type="button" onclick="openDocModal('Sample Teaching Video', '{{ route('admin.tutors.document', ['id' => $tutorProfile->id, 'type' => 'test_video']) }}', 'video')" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                <i class="fas fa-video text-purple-500 text-lg"></i> Preview Uploaded Video File
                            </button>
                        @else
                            <span class="text-sm text-gray-400 italic">Not provided</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Payout Details & Meta -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">Payout Setup</h3>

                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Payout Method</span>
                        <div class="font-semibold text-gray-900 dark:text-white uppercase">{{ $tutorProfile->payout_method ?? 'Not configured' }}</div>
                    </div>

                    @if($tutorProfile->payout_method === 'momo')
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Network</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tutorProfile->payout_momo_network }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">MoMo Number</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tutorProfile->payout_momo_number }}</div>
                        </div>
                    @elseif($tutorProfile->payout_method === 'bank')
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Bank Name</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tutorProfile->payout_bank_name }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Account Name</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tutorProfile->payout_bank_account_name }}</div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Account Number</span>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tutorProfile->payout_bank_account_number }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scheduling Mode -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3">Scheduling Preference</h3>
                <div class="text-sm">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">Calendar Type</span>
                    <div class="font-semibold text-gray-900 dark:text-white mt-1">
                        @if($tutorProfile->scheduling_preference === 'external')
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-external-link-alt text-amber-500"></i>
                                External Link
                            </span>
                            @if($tutorProfile->scheduling_link)
                                <a href="{{ $tutorProfile->scheduling_link }}" target="_blank" class="block text-xs text-blue-600 dark:text-blue-400 mt-1 hover:underline truncate">{{ $tutorProfile->scheduling_link }}</a>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-calendar-check text-blue-500"></i>
                                DigiLearn In-App Calendar
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- In-App Document / Image Viewer Modal -->
<div id="docViewerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm transition-opacity">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <i class="fas fa-file-alt" id="docModalIcon"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white" id="docModalTitle">Document Viewer</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tutor Applicant Verification Document</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="docModalExternalBtn" href="#" target="_blank" class="px-3 py-1.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center gap-1">
                    <i class="fas fa-external-link-alt"></i> Open Raw File
                </a>
                <button type="button" onclick="closeDocModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center text-lg font-bold transition">&times;</button>
            </div>
        </div>

        <!-- Modal Content Body -->
        <div class="flex-1 bg-gray-100 dark:bg-gray-900 p-4 flex items-center justify-center overflow-auto min-h-[60vh]">
            <img id="docImagePreview" class="hidden max-h-[70vh] max-w-full rounded-lg shadow object-contain">
            
            <iframe id="docPdfPreview" class="hidden w-full h-[70vh] rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm" frameborder="0"></iframe>
            
            <video id="docVideoPreview" class="hidden max-h-[70vh] w-full rounded-lg" controls></video>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    function openDocModal(title, url, typeHint = 'auto') {
        const modal = document.getElementById('docViewerModal');
        const modalTitle = document.getElementById('docModalTitle');
        const modalExternalBtn = document.getElementById('docModalExternalBtn');
        const img = document.getElementById('docImagePreview');
        const iframe = document.getElementById('docPdfPreview');
        const video = document.getElementById('docVideoPreview');

        modalTitle.textContent = title;
        modalExternalBtn.href = url;

        // Reset visibility
        img.classList.add('hidden');
        iframe.classList.add('hidden');
        video.classList.add('hidden');
        img.src = '';
        iframe.src = '';
        video.src = '';

        // Detect type
        if (typeHint === 'image') {
            img.src = url;
            img.classList.remove('hidden');
        } else if (typeHint === 'video') {
            video.src = url;
            video.classList.remove('hidden');
        } else {
            // PDF or default iframe document stream
            iframe.src = url;
            iframe.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDocModal() {
        const modal = document.getElementById('docViewerModal');
        const img = document.getElementById('docImagePreview');
        const iframe = document.getElementById('docPdfPreview');
        const video = document.getElementById('docVideoPreview');

        modal.classList.add('hidden');
        img.src = '';
        iframe.src = '';
        if (video.src) {
            video.pause();
            video.src = '';
        }
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDocModal();
    });
</script>
@endsection
