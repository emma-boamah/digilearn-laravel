<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Become a Tutor - ShoutOutGh</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS (CDN for development/dynamic class loading) -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.tailwindcss.com?plugins=forms"></script>
    
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .step-container { display: none; }
        .step-container.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .progress-indicator { transition: all 0.3s ease; }
        .progress-indicator.completed .circle { background-color: #E11E2D; border-color: #E11E2D; color: white; }
        .progress-indicator.active .circle { border-color: #E11E2D; color: #E11E2D; font-weight: 600; }
        .progress-indicator.active .label { color: #111827; font-weight: 600; }
    </style>
</head>
<body class="antialiased text-gray-900 min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <img class="h-8 w-auto" src="{{ asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
                    <span class="font-bold text-xl tracking-tight text-gray-900">Tutor Onboarding</span>
                </div>
                <div>
                    <a href="{{ route('tutors.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Exit</a>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full grid grid-cols-1 md:grid-cols-12 gap-8">
        
        <!-- Sidebar Progress -->
        <div class="md:col-span-4 lg:col-span-3 hidden md:block">
            <div class="sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Application Steps</h3>
                <nav aria-label="Progress">
                    <ol role="list" class="overflow-hidden">
                        
                        <li class="relative pb-10 progress-indicator active" id="nav-step-1">
                            <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></div>
                            <div class="relative flex items-center group">
                                <span class="h-9 flex items-center">
                                    <span class="circle relative z-10 w-8 h-8 flex items-center justify-center bg-white border-2 border-gray-300 rounded-full text-sm font-medium text-gray-500">1</span>
                                </span>
                                <span class="ml-4 min-w-0 flex flex-col">
                                    <span class="label text-sm font-medium text-gray-500">Professional Profile</span>
                                    <span class="text-xs text-gray-400">Bio & Headshot</span>
                                </span>
                            </div>
                        </li>

                        <li class="relative pb-10 progress-indicator" id="nav-step-2">
                            <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></div>
                            <div class="relative flex items-center group">
                                <span class="h-9 flex items-center">
                                    <span class="circle relative z-10 w-8 h-8 flex items-center justify-center bg-white border-2 border-gray-300 rounded-full text-sm font-medium text-gray-500">2</span>
                                </span>
                                <span class="ml-4 min-w-0 flex flex-col">
                                    <span class="label text-sm font-medium text-gray-500">Credentials</span>
                                    <span class="text-xs text-gray-400">ID & Certificates</span>
                                </span>
                            </div>
                        </li>

                        <li class="relative pb-10 progress-indicator" id="nav-step-3">
                            <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></div>
                            <div class="relative flex items-center group">
                                <span class="h-9 flex items-center">
                                    <span class="circle relative z-10 w-8 h-8 flex items-center justify-center bg-white border-2 border-gray-300 rounded-full text-sm font-medium text-gray-500">3</span>
                                </span>
                                <span class="ml-4 min-w-0 flex flex-col">
                                    <span class="label text-sm font-medium text-gray-500">Scheduling</span>
                                    <span class="text-xs text-gray-400">Availability</span>
                                </span>
                            </div>
                        </li>

                        <li class="relative progress-indicator" id="nav-step-4">
                            <div class="relative flex items-center group">
                                <span class="h-9 flex items-center">
                                    <span class="circle relative z-10 w-8 h-8 flex items-center justify-center bg-white border-2 border-gray-300 rounded-full text-sm font-medium text-gray-500">4</span>
                                </span>
                                <span class="ml-4 min-w-0 flex flex-col">
                                    <span class="label text-sm font-medium text-gray-500">Subjects & Rates</span>
                                    <span class="text-xs text-gray-400">Pricing setup</span>
                                </span>
                            </div>
                        </li>

                    </ol>
                </nav>
            </div>
        </div>

        <!-- Main Form Area -->
        <div class="md:col-span-8 lg:col-span-9 bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-10">
            
            @if($errors->any())
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">There were errors with your submission</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('tutors.storeApplication') }}" method="POST" enctype="multipart/form-data" id="onboardingForm" novalidate>
                @csrf
                
                <!-- STEP 1: Professional Profile -->
                <div class="step-container active" id="step-1">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Professional Profile</h2>
                        <p class="text-sm text-gray-500 mt-1">This information will be displayed publicly on your tutor card.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="tagline" class="block text-sm font-medium text-gray-700">Professional Tagline</label>
                            <p class="text-xs text-gray-500 mb-1">e.g., "Certified Math Teacher with 10 years experience"</p>
                            <input type="text" name="tagline" id="tagline" value="{{ old('tagline') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="Your headline...">
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Biography</label>
                            <p class="text-xs text-gray-500 mb-1">Write a compelling description of who you are and your teaching style.</p>
                            <textarea name="bio" id="bio" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="Hi, I'm... ">{{ old('bio') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Professional Headshot <span class="text-red-500">*</span></label>
                            <p class="text-xs text-gray-500 mb-2">A friendly, professional portrait with good lighting and clear view of your face. No selfies or group photos.</p>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors bg-gray-50 cursor-pointer" id="headshot_dropzone" onclick="document.getElementById('headshot_file').click()">
                                <div class="space-y-2 text-center" id="headshot_placeholder">
                                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 mx-auto flex items-center justify-center text-xl">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span class="font-semibold text-blue-600 hover:text-blue-500">
                                            <span>Click to upload headshot</span>
                                            <input id="headshot_file" name="headshot_file" type="file" class="sr-only" accept="image/*" required onchange="handleHeadshotPreview(this)">
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400">PNG, JPG up to 5MB</p>
                                </div>

                                <!-- Headshot Live Preview Box -->
                                <div id="headshot_preview_box" class="hidden flex-col items-center justify-center">
                                    <img id="headshot_preview_img" src="" alt="Headshot Preview" class="w-24 h-24 rounded-full object-cover mb-2 border-2 border-blue-500 shadow-md">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-700" id="headshot_filename"></span>
                                        <button type="button" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-white px-2 py-0.5 rounded border border-gray-200 shadow-2xs" onclick="event.stopPropagation(); document.getElementById('headshot_file').click()">Change</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Credentials & Verification -->
                <div class="step-container" id="step-2">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Identity & Credentials</h2>
                        <p class="text-sm text-gray-500 mt-1">Verify your identity to build trust with students.</p>
                    </div>

                    <div class="space-y-5">
                        <!-- Legal Full Name -->
                        <div>
                            <label for="legal_name" class="block text-sm font-semibold text-gray-800 mb-1">Legal Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="legal_name" id="legal_name" value="{{ old('legal_name') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="As shown on your government ID">
                        </div>

                        <!-- ID Type Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Government ID Type <span class="text-red-500">*</span></label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <!-- National ID / Ghana Card -->
                                <label class="id-type-card flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer transition bg-blue-50/40 border-blue-600 shadow-xs" id="id_type_label_national_id">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid fa-id-card"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-bold text-gray-900 truncate">National ID Card</span>
                                    </div>
                                    <input type="radio" name="id_type" value="national_id" class="text-blue-600 focus:ring-blue-500" {{ old('id_type', 'national_id') === 'national_id' ? 'checked' : '' }} onchange="handleIdTypeChange('national_id')">
                                </label>

                                <!-- Driver's License -->
                                <label class="id-type-card flex items-center gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer transition hover:border-gray-300 bg-white" id="id_type_label_drivers_license">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid fa-id-badge"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-bold text-gray-900 truncate">Driver's License</span>
                                    </div>
                                    <input type="radio" name="id_type" value="drivers_license" class="text-blue-600 focus:ring-blue-500" {{ old('id_type') === 'drivers_license' ? 'checked' : '' }} onchange="handleIdTypeChange('drivers_license')">
                                </label>

                                <!-- Passport -->
                                <label class="id-type-card flex items-center gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer transition hover:border-gray-300 bg-white" id="id_type_label_passport">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid fa-passport"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-bold text-gray-900 truncate">Passport</span>
                                    </div>
                                    <input type="radio" name="id_type" value="passport" class="text-blue-600 focus:ring-blue-500" {{ old('id_type') === 'passport' ? 'checked' : '' }} onchange="handleIdTypeChange('passport')">
                                </label>
                            </div>
                        </div>

                        <!-- ID Upload Zones (Front and Back) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-1">
                            <!-- Front Side Upload -->
                            <div class="flex flex-col">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5" id="id_front_label">
                                    Front Side (Photo & Bio) <span class="text-red-500">*</span>
                                </label>
                                
                                <div class="relative flex-1 min-h-[135px] border-2 border-gray-200 border-dashed rounded-xl p-3 flex flex-col items-center justify-center text-center bg-gray-50/70 hover:bg-blue-50/30 hover:border-blue-400 transition cursor-pointer" id="id_front_dropzone" onclick="document.getElementById('id_document_file').click()">
                                    <input id="id_document_file" name="id_document_file" type="file" class="sr-only" accept=".pdf,image/*" required onchange="handleIdPreview(this, 'front')">
                                    
                                    <div id="id_front_placeholder" class="space-y-1">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 mx-auto flex items-center justify-center text-sm mb-1.5">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <div class="text-xs font-semibold text-blue-600">
                                            <span>Upload Front Side</span>
                                        </div>
                                        <p class="text-[11px] text-gray-400">JPG, PNG, PDF (max 10MB)</p>
                                    </div>

                                    <!-- Preview Box -->
                                    <div id="id_front_preview_box" class="hidden flex-col items-center justify-center w-full">
                                        <img id="id_front_preview_img" src="" alt="Front ID Preview" class="max-h-20 max-w-full rounded-lg object-contain mb-1.5 shadow-2xs border border-gray-200">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-medium text-gray-700 truncate max-w-[160px]" id="id_front_filename"></span>
                                            <button type="button" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-white px-2 py-0.5 rounded border border-gray-200" onclick="event.stopPropagation(); document.getElementById('id_document_file').click()">Change</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Back Side Upload -->
                            <div class="flex flex-col" id="id_back_container">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5" id="id_back_label">
                                    Back Side <span class="text-red-500">*</span>
                                </label>
                                
                                <div class="relative flex-1 min-h-[135px] border-2 border-gray-200 border-dashed rounded-xl p-3 flex flex-col items-center justify-center text-center bg-gray-50/70 hover:bg-blue-50/30 hover:border-blue-400 transition cursor-pointer" id="id_back_dropzone" onclick="document.getElementById('id_document_back_file').click()">
                                    <input id="id_document_back_file" name="id_document_back_file" type="file" class="sr-only" accept=".pdf,image/*" required onchange="handleIdPreview(this, 'back')">
                                    
                                    <div id="id_back_placeholder" class="space-y-1">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center text-sm mb-1.5">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <div class="text-xs font-semibold text-indigo-600">
                                            <span>Upload Back Side</span>
                                        </div>
                                        <p class="text-[11px] text-gray-400">JPG, PNG, PDF (max 10MB)</p>
                                    </div>

                                    <!-- Preview Box -->
                                    <div id="id_back_preview_box" class="hidden flex-col items-center justify-center w-full">
                                        <img id="id_back_preview_img" src="" alt="Back ID Preview" class="max-h-20 max-w-full rounded-lg object-contain mb-1.5 shadow-2xs border border-gray-200">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-medium text-gray-700 truncate max-w-[160px]" id="id_back_filename"></span>
                                            <button type="button" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-white px-2 py-0.5 rounded border border-gray-200" onclick="event.stopPropagation(); document.getElementById('id_document_back_file').click()">Change</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Passport Notice Container -->
                            <div class="hidden flex-col justify-center p-4 bg-blue-50/50 border border-blue-200 rounded-xl" id="passport_notice_container">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-xs">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <p class="text-xs text-blue-800 leading-relaxed">
                                        For passports, only the primary photo/bio data page is required.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Educational Qualifications -->
                        <div>
                            <label for="qualifications" class="block text-sm font-semibold text-gray-800 mb-1">Educational Qualifications <span class="text-red-500">*</span></label>
                            <textarea name="qualifications" id="qualifications" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="e.g. BSc. Mathematics (KNUST), 5+ years teaching experience...">{{ old('qualifications') }}</textarea>
                        </div>

                        <!-- Teaching Certificates -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-1">Teaching Certificates <span class="text-xs font-normal text-gray-500">(Optional)</span></label>
                            <input type="file" name="certificates_file" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" accept=".pdf,image/*">
                        </div>
                        
                        <!-- Video Intro -->
                        <div>
                            <label for="intro_video_url" class="block text-sm font-semibold text-gray-800 mb-1">Intro Video URL <span class="text-xs font-normal text-gray-500">(Optional)</span></label>
                            <input type="url" name="intro_video_url" id="intro_video_url" value="{{ old('intro_video_url') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Setup & Scheduling -->
                <div class="step-container" id="step-3">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Logistics & Scheduling</h2>
                        <p class="text-sm text-gray-500 mt-1">Configure how students will book and communicate with you.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-1">Scheduling Preference</label>
                            <p class="text-xs text-gray-500 mb-3">Choose how students will schedule 1-on-1 booking sessions with you.</p>

                            <div class="space-y-3">
                                <!-- In-App Calendar Option (Recommended) -->
                                <label class="flex items-start p-3.5 border-2 rounded-xl cursor-pointer hover:border-blue-400 transition bg-blue-50/30 border-blue-500" id="pref_label_in_app">
                                    <input type="radio" name="scheduling_preference" id="pref_in_app" value="in_app" class="mt-1 text-blue-600 focus:ring-blue-500" {{ old('scheduling_preference', 'in_app') === 'in_app' ? 'checked' : '' }} onchange="toggleSchedulingPreference('in_app')">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-gray-900">
                                            DigiLearn Built-In Calendar <span class="bg-blue-100 text-blue-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full ml-1 uppercase">Recommended</span>
                                        </span>
                                        <span class="block text-xs text-gray-500 mt-0.5">Use DigiLearn's interactive availability manager. You can easily configure your weekly time slots in your Tutor Schedule Dashboard.</span>
                                    </div>
                                </label>

                                <!-- External Calendly Option -->
                                <label class="flex items-start p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 transition bg-white" id="pref_label_external">
                                    <input type="radio" name="scheduling_preference" id="pref_external" value="external" class="mt-1 text-blue-600 focus:ring-blue-500" {{ old('scheduling_preference') === 'external' ? 'checked' : '' }} onchange="toggleSchedulingPreference('external')">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-gray-900">External Scheduling Link (e.g. Calendly)</span>
                                        <span class="block text-xs text-gray-500 mt-0.5">If you already manage your appointments using Calendly, select this option and enter your link below.</span>
                                    </div>
                                </label>
                            </div>

                            <!-- External Link Input Container (Hidden by default unless 'external' chosen) -->
                            <div id="external_link_container" class="{{ old('scheduling_preference') === 'external' ? '' : 'hidden' }} mt-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label for="scheduling_link" class="block text-xs font-semibold text-gray-700 mb-1">Calendly / External Scheduling URL</label>
                                <input type="url" name="scheduling_link" id="scheduling_link" value="{{ old('scheduling_link') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="https://calendly.com/your-username">
                            </div>
                        </div>
                        
                        <div>
                            <label for="communication_handle" class="block text-sm font-medium text-gray-700">Backup Communication Handle</label>
                            <p class="text-xs text-gray-500 mb-1">Skype, Zoom, or Google Meet ID as a backup method for the lessons.</p>
                            <input type="text" name="communication_handle" id="communication_handle" value="{{ old('communication_handle') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g. Skype: live.john.doe" required>
                        </div>
                        
                        <div>
                            <label for="payout_method" class="block text-sm font-medium text-gray-700">Preferred Payout Method</label>
                            <p class="text-xs text-gray-500 mb-1">How would you like to receive your earnings?</p>
                            <select name="payout_method" id="payout_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                <option value="" disabled selected>Select a method</option>
                                <option value="momo">Mobile Money (MoMo)</option>
                                <option value="bank">Local Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div id="momo_fields" class="hidden space-y-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-800">Mobile Money Details</h4>
                            <div>
                                <label for="payout_momo_network" class="block text-sm font-medium text-gray-700">Network</label>
                                <select name="payout_momo_network" id="payout_momo_network" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="" disabled selected>Select Network</option>
                                    <option value="MTN">MTN MoMo</option>
                                    <option value="Telecel">Telecel Cash</option>
                                    <option value="AT">AT Money</option>
                                </select>
                            </div>
                            <div>
                                <label for="payout_momo_number" class="block text-sm font-medium text-gray-700">MoMo Number</label>
                                <input type="text" name="payout_momo_number" id="payout_momo_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="024XXXXXXX">
                            </div>
                        </div>

                        <div id="bank_fields" class="hidden space-y-4 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-800">Bank Transfer Details</h4>
                            <div>
                                <label for="payout_bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                                <input type="text" name="payout_bank_name" id="payout_bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g. Ecobank Ghana">
                            </div>
                            <div>
                                <label for="payout_bank_account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                                <input type="text" name="payout_bank_account_name" id="payout_bank_account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Must match your legal name">
                            </div>
                            <div>
                                <label for="payout_bank_account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                                <input type="text" name="payout_bank_account_number" id="payout_bank_account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="payout_bank_branch" class="block text-sm font-medium text-gray-700">Branch Name / Code</label>
                                <input type="text" name="payout_bank_branch" id="payout_bank_branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Subjects & Rates -->
                <div class="step-container" id="step-4">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Subjects & Rates</h2>
                        <p class="text-sm text-gray-500 mt-1">Select your expertise domains and set your hourly rates.</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 max-h-96 overflow-y-auto space-y-3">
                        @forelse($subjects as $subject)
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-100 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <input id="subject_{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" type="checkbox" class="subject-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-id="{{ $subject->id }}">
                                    <label for="subject_{{ $subject->id }}" class="font-medium text-gray-700 select-none cursor-pointer">
                                        {{ $subject->name }}
                                    </label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500 font-medium">GHS</span>
                                    <input type="number" name="rates[{{ $subject->id }}]" id="rate_{{ $subject->id }}" step="0.01" min="0" disabled class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:opacity-50 disabled:bg-gray-100" placeholder="0.00">
                                    <span class="text-sm text-gray-500 font-medium">/hr</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">
                                <i class="fas fa-book-open text-2xl text-gray-400 mb-2"></i>
                                <p class="text-sm font-semibold">No subjects available yet.</p>
                                <p class="text-xs text-gray-400 mt-1">Please contact an administrator to seed standard tutoring subjects.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-10 flex justify-between pt-6 border-t border-gray-200">
                    <button type="button" id="btn-prev" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 hidden">
                        Back
                    </button>
                    <div class="ml-auto">
                        <button type="button" id="btn-next" class="bg-gray-900 border border-transparent rounded-md shadow-sm py-2 px-6 inline-flex justify-center text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                            Continue to Next Step
                        </button>
                        <button type="submit" id="btn-submit" class="bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-8 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 hidden">
                            Submit Application
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // File upload label update
            const headshotInput = document.getElementById('headshot_file');
            const headshotFilename = document.getElementById('headshot_filename');
            if (headshotInput) {
                headshotInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        headshotFilename.textContent = e.target.files[0].name;
                        headshotFilename.classList.add('text-blue-600', 'font-medium');
                    }
                });
            }

            // Rates toggling logic
            const checkboxes = document.querySelectorAll('.subject-checkbox');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const id = this.getAttribute('data-id');
                    const rateInput = document.getElementById('rate_' + id);
                    rateInput.disabled = !this.checked;
                    if (this.checked && !rateInput.value) {
                        rateInput.value = '50.00';
                        rateInput.focus();
                    } else if (!this.checked) {
                        rateInput.value = '';
                    }
                });
            });

            // Multi-step logic
            let currentStep = 1;
            const totalSteps = 4;
            
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            const btnSubmit = document.getElementById('btn-submit');
            const onboardingForm = document.getElementById('onboardingForm');
            
            function updateUI() {
                // Hide all steps
                document.querySelectorAll('.step-container').forEach(el => el.classList.remove('active'));
                // Show current step
                document.getElementById('step-' + currentStep).classList.add('active');
                
                // Update navigation indicators
                for (let i = 1; i <= totalSteps; i++) {
                    const navItem = document.getElementById('nav-step-' + i);
                    if (i < currentStep) {
                        navItem.classList.add('completed');
                        navItem.classList.remove('active');
                        // Change circle to checkmark for completed
                        navItem.querySelector('.circle').innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                    } else if (i === currentStep) {
                        navItem.classList.add('active');
                        navItem.classList.remove('completed');
                        navItem.querySelector('.circle').innerHTML = i;
                    } else {
                        navItem.classList.remove('active', 'completed');
                        navItem.querySelector('.circle').innerHTML = i;
                    }
                }
                
                // Update buttons
                if (currentStep === 1) {
                    btnPrev.classList.add('hidden');
                } else {
                    btnPrev.classList.remove('hidden');
                }
                
                if (currentStep === totalSteps) {
                    btnNext.classList.add('hidden');
                    btnSubmit.classList.remove('hidden');
                } else {
                    btnNext.classList.remove('hidden');
                    btnSubmit.classList.add('hidden');
                }
            }

            // Step validation logic
            function validateStep(stepNum) {
                const container = document.getElementById('step-' + stepNum);
                if (!container) return true;

                const requiredInputs = container.querySelectorAll('input[required], textarea[required], select[required]');
                let isValid = true;
                
                requiredInputs.forEach(input => {
                    // Skip inputs that are currently hidden (e.g. passport back ID or hidden scheduling link)
                    if (input.offsetParent === null || input.closest('.hidden')) {
                        return;
                    }

                    let fieldValid = true;
                    if (input.type === 'file') {
                        if (input.files.length === 0) {
                            fieldValid = false;
                        }
                    } else if (input.type === 'radio') {
                        const name = input.name;
                        const anyChecked = container.querySelector(`input[name="${name}"]:checked`);
                        if (!anyChecked) {
                            fieldValid = false;
                        }
                    } else {
                        if (!input.value.trim()) {
                            fieldValid = false;
                        }
                    }

                    if (!fieldValid) {
                        isValid = false;
                        input.classList.add('border-red-500', 'ring-red-500');
                        // Also highlight parent dropzone if file input
                        const dropzone = input.closest('#id_front_dropzone, #id_back_dropzone, #headshot_dropzone');
                        if (dropzone) {
                            dropzone.classList.add('border-red-500');
                        }
                    } else {
                        input.classList.remove('border-red-500', 'ring-red-500');
                        const dropzone = input.closest('#id_front_dropzone, #id_back_dropzone, #headshot_dropzone');
                        if (dropzone) {
                            dropzone.classList.remove('border-red-500');
                        }
                    }
                });
                
                // For step 4, require at least one subject
                if (stepNum === 4) {
                    const checkedSubjects = document.querySelectorAll('.subject-checkbox:checked');
                    if (checkedSubjects.length === 0) {
                        isValid = false;
                    }
                }

                return isValid;
            }

            btnNext.addEventListener('click', () => {
                if (validateStep(currentStep)) {
                    currentStep++;
                    updateUI();
                    window.scrollTo(0, 0);
                } else {
                    if (currentStep === 2) {
                        alert('Please complete all required fields and upload your government ID document(s).');
                    } else if (currentStep === 4) {
                        alert('Please select at least one subject to teach.');
                    } else {
                        alert('Please fill out all required fields before proceeding.');
                    }
                }
            });

            btnPrev.addEventListener('click', () => {
                currentStep--;
                updateUI();
                window.scrollTo(0, 0);
            });

            // Form Submit Listener
            if (onboardingForm) {
                onboardingForm.addEventListener('submit', function(e) {
                    for (let s = 1; s <= totalSteps; s++) {
                        if (!validateStep(s)) {
                            e.preventDefault();
                            currentStep = s;
                            updateUI();
                            window.scrollTo(0, 0);
                            if (s === 2) {
                                alert('Please complete all required ID documents in Step 2 before submitting.');
                            } else if (s === 4) {
                                alert('Please select at least one subject to teach before submitting.');
                            } else {
                                alert('Please complete all required fields in Step ' + s + ' before submitting.');
                            }
                            return false;
                        }
                    }
                });
            }
            
            // Payout Method Toggle
            const payoutMethod = document.getElementById('payout_method');
            const momoFields = document.getElementById('momo_fields');
            const bankFields = document.getElementById('bank_fields');
            
            if (payoutMethod) {
                payoutMethod.addEventListener('change', (e) => {
                    if (e.target.value === 'momo') {
                        momoFields.classList.remove('hidden');
                        bankFields.classList.add('hidden');
                        
                        // Toggle required
                        document.getElementById('payout_momo_network').required = true;
                        document.getElementById('payout_momo_number').required = true;
                        
                        document.getElementById('payout_bank_name').required = false;
                        document.getElementById('payout_bank_account_name').required = false;
                        document.getElementById('payout_bank_account_number').required = false;
                        document.getElementById('payout_bank_branch').required = false;
                    } else if (e.target.value === 'bank') {
                        bankFields.classList.remove('hidden');
                        momoFields.classList.add('hidden');
                        
                        // Toggle required
                        document.getElementById('payout_bank_name').required = true;
                        document.getElementById('payout_bank_account_name').required = true;
                        document.getElementById('payout_bank_account_number').required = true;
                        document.getElementById('payout_bank_branch').required = true;
                        
                        document.getElementById('payout_momo_network').required = false;
                        document.getElementById('payout_momo_number').required = false;
                    }
                });
            }
            
            // Initialize UI
            updateUI();
        });

        // ID Type Change Handler
        function handleIdTypeChange(type) {
            const idTypes = ['national_id', 'drivers_license', 'passport'];
            idTypes.forEach(t => {
                const label = document.getElementById('id_type_label_' + t);
                if (label) {
                    const iconDiv = label.querySelector('.w-9');
                    if (t === type) {
                        label.classList.add('border-blue-600', 'bg-blue-50/40', 'border-2', 'shadow-sm');
                        label.classList.remove('border-gray-200', 'hover:border-gray-300', 'bg-white');
                        if (iconDiv) {
                            iconDiv.classList.add('bg-blue-100', 'text-blue-600');
                            iconDiv.classList.remove('bg-gray-100', 'text-gray-600');
                        }
                    } else {
                        label.classList.remove('border-blue-600', 'bg-blue-50/40', 'border-2', 'shadow-sm');
                        label.classList.add('border-gray-200', 'hover:border-gray-300', 'bg-white');
                        if (iconDiv) {
                            iconDiv.classList.remove('bg-blue-100', 'text-blue-600');
                            iconDiv.classList.add('bg-gray-100', 'text-gray-600');
                        }
                    }
                }
            });

            const backContainer = document.getElementById('id_back_container');
            const backInput = document.getElementById('id_document_back_file');
            const passportNotice = document.getElementById('passport_notice_container');
            const frontLabel = document.getElementById('id_front_label');

            if (type === 'passport') {
                if (backContainer) backContainer.classList.add('hidden');
                if (passportNotice) {
                    passportNotice.classList.remove('hidden');
                    passportNotice.classList.add('flex');
                }
                if (backInput) {
                    backInput.required = false;
                    backInput.classList.remove('border-red-500', 'ring-red-500');
                }
                if (frontLabel) frontLabel.innerHTML = 'Passport Photo / Bio Data Page <span class="text-red-500">*</span>';
            } else {
                if (backContainer) backContainer.classList.remove('hidden');
                if (passportNotice) {
                    passportNotice.classList.add('hidden');
                    passportNotice.classList.remove('flex');
                }
                if (backInput) {
                    backInput.required = true;
                }
                if (frontLabel) frontLabel.innerHTML = (type === 'drivers_license' ? "Driver's License (Front)" : "National ID Card (Front)") + ' <span class="text-red-500">*</span>';
            }
        }

        // Live Image Preview for ID Documents
        function handleIdPreview(input, side) {
            const file = input.files[0];
            const placeholder = document.getElementById(`id_${side}_placeholder`);
            const previewBox = document.getElementById(`id_${side}_preview_box`);
            const previewImg = document.getElementById(`id_${side}_preview_img`);
            const filenameEl = document.getElementById(`id_${side}_filename`);
            const dropzone = document.getElementById(`id_${side}_dropzone`);

            if (!file) return;

            filenameEl.textContent = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    previewBox.classList.remove('hidden');
                    previewBox.classList.add('flex');
                    dropzone.classList.remove('border-red-500');
                    dropzone.classList.add('border-blue-500', 'bg-blue-50/20');
                };
                reader.readAsDataURL(file);
            } else {
                // PDF or other doc
                previewImg.classList.add('hidden');
                placeholder.classList.add('hidden');
                previewBox.classList.remove('hidden');
                previewBox.classList.add('flex');
                dropzone.classList.remove('border-red-500');
                dropzone.classList.add('border-blue-500', 'bg-blue-50/20');
            }
        }

        // Live Image Preview for Headshot Portrait
        function handleHeadshotPreview(input) {
            const file = input.files[0];
            const placeholder = document.getElementById('headshot_placeholder');
            const previewBox = document.getElementById('headshot_preview_box');
            const previewImg = document.getElementById('headshot_preview_img');
            const filenameEl = document.getElementById('headshot_filename');
            const dropzone = document.getElementById('headshot_dropzone');

            if (!file) return;

            filenameEl.textContent = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    placeholder.classList.add('hidden');
                    previewBox.classList.remove('hidden');
                    previewBox.classList.add('flex');
                    dropzone.classList.remove('border-red-500');
                    dropzone.classList.add('border-blue-500', 'bg-blue-50/20');
                };
                reader.readAsDataURL(file);
            }
        }

        function toggleSchedulingPreference(mode) {
            const container = document.getElementById('external_link_container');
            const linkInput = document.getElementById('scheduling_link');
            const labelInApp = document.getElementById('pref_label_in_app');
            const labelExternal = document.getElementById('pref_label_external');

            if (mode === 'external') {
                container.classList.remove('hidden');
                linkInput.required = true;
                labelExternal.classList.add('border-blue-500', 'bg-blue-50/30', 'border-2');
                labelExternal.classList.remove('border-gray-200');
                labelInApp.classList.remove('border-blue-500', 'bg-blue-50/30', 'border-2');
                labelInApp.classList.add('border-gray-200');
            } else {
                container.classList.add('hidden');
                linkInput.required = false;
                linkInput.value = '';
                labelInApp.classList.add('border-blue-500', 'bg-blue-50/30', 'border-2');
                labelInApp.classList.remove('border-gray-200');
                labelExternal.classList.remove('border-blue-500', 'bg-blue-50/30', 'border-2');
                labelExternal.classList.add('border-gray-200');
            }
        }
    </script>
</body>
</html>
