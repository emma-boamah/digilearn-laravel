@extends('layouts.dashboard-simple')

@section('content')
    @if(isset($isChanging) && $isChanging)
        <button class="back-button" id="backToDashboard">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </button>
    @else
        <button class="back-button" id="backButton">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </button>
    @endif

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="shoutout-logo">
                        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                        </svg>
                        <div class="brand-section sidebar-logo">
                            <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
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
            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Level Selection Grid -->
            <div class="level-selection-grid">
                <!-- Grade/Primary 1-3 -->
                <div class="level-group-card">
                    <div class="level-header">
                        <h3 class="level-title">Grade/Primary 1-3</h3>
                    </div>
                    <div class="level-image-container">
                        <div class="level-placeholder-image"></div>
                    </div>
                    <p class="level-description">Lower primary or Elementary school</p>
                    <button type="button" class="level-select-btn" onclick="showLevelOptions('primary-lower')">
                        Select
                    </button>
                </div>

                <!-- Grade/Primary 4-6 -->
                <div class="level-group-card">
                    <div class="level-header">
                        <h3 class="level-title">Grade/Primary 4-6</h3>
                    </div>
                    <div class="level-image-container">
                        <div class="level-placeholder-image"></div>
                    </div>
                    <p class="level-description">Upper primary or elementary school</p>
                    <button type="button" class="level-select-btn" onclick="showLevelOptions('primary-upper')">
                        Select
                    </button>
                </div>

                <!-- Grade/JHS 7-9 -->
                <div class="level-group-card">
                    <div class="level-header">
                        <h3 class="level-title">Grade/JHS 7-9</h3>
                    </div>
                    <div class="level-image-container">
                        <div class="level-illustration">
                            <!-- Illustration matching the mockup -->
                            <svg width="120" height="80" viewBox="0 0 120 80" fill="none">
                                <!-- Computer/Chart illustration -->
                                <rect x="20" y="15" width="80" height="50" rx="4" fill="#E5E7EB" stroke="#9CA3AF"/>
                                <rect x="25" y="20" width="70" height="35" fill="#F3F4F6"/>
                                <!-- Chart elements -->
                                <rect x="30" y="35" width="8" height="15" fill="#3B82F6"/>
                                <rect x="42" y="30" width="8" height="20" fill="#EF4444"/>
                                <rect x="54" y="25" width="8" height="25" fill="#10B981"/>
                                <circle cx="75" r="8" fill="#F59E0B"/>
                                <path d="M67 40 L83 40 L75 32 Z" fill="#8B5CF6"/>
                                <!-- Person figure -->
                                <circle cx="85" cy="45" r="6" fill="#F97316"/>
                                <rect x="82" y="52" width="6" height="12" fill="#F97316"/>
                                <rect x="79" y="58" width="4" height="8" fill="#1F2937"/>
                                <rect x="87" y="58" width="4" height="8" fill="#1F2937"/>
                            </svg>
                        </div>
                    </div>
                    <p class="level-description">Junior High School or Middle school</p>
                    <button type="button" class="level-select-btn" onclick="showLevelOptions('jhs')">
                        Select
                    </button>
                </div>

                <!-- Grade/SHS 1-3 -->
                <div class="level-group-card">
                    <div class="level-header">
                        <h3 class="level-title">Grade/SHS 1-3</h3>
                    </div>
                    <div class="level-image-container">
                        <div class="level-placeholder-image"></div>
                    </div>
                    <p class="level-description">High school or Senior High School</p>
                    <button type="button" class="level-select-btn" onclick="showLevelOptions('shs')">
                        Select
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Level Selection Modal -->
    <div id="levelModal" class="level-modal" style="display: none;">
        <div class="modal-overlay" onclick="closeLevelModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Select Specific Level</h3>
                <button class="modal-close" onclick="closeLevelModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="levelOptions" class="level-options-grid"></div>
            </div>
        </div>
    </div>

    <style>
        .level-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .level-group-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .level-group-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .level-header {
            margin-bottom: 1rem;
        }

        .level-title {
            color: #DC2626;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .level-image-container {
            width: 100%;
            height: 160px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            overflow: hidden;
        }

        .level-placeholder-image {
            width: 100%;
            height: 100%;
            background-color: #A1A1AA;
            border-radius: 8px;
        }

        .level-illustration {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: #F8FAFC;
            border-radius: 8px;
            border: 2px solid #E2E8F0;
        }

        .level-description {
            color: #6B7280;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .level-select-btn {
            background-color: #DC2626;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
            font-size: 1rem;
        }

        .level-select-btn:hover {
            background-color: #B91C1C;
        }

        /* Modal Styles */
        .level-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6B7280;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #374151;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .level-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .level-option-btn {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .level-option-btn:hover {
            border-color: #DC2626;
            background-color: #FEF2F2;
        }

        .level-option-btn h4 {
            margin: 0 0 0.5rem 0;
            color: #111827;
            font-size: 1rem;
            font-weight: 600;
        }

        .level-option-btn p {
            margin: 0;
            color: #6B7280;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .level-selection-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem 0;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .level-selection-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .level-selection-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Level groupings
    const levelGroups = {
        'primary-lower': [
            { id: 'primary-1', title: 'Primary 1', description: 'Foundation learning for young minds' },
            { id: 'primary-2', title: 'Primary 2', description: 'Building on fundamentals' },
            { id: 'primary-3', title: 'Primary 3', description: 'Developing critical thinking skills' }
        ],
        'primary-upper': [
            { id: 'primary-4', title: 'Primary 4', description: 'Advanced primary education' },
            { id: 'primary-5', title: 'Primary 5', description: 'Preparing for junior high transition' },
            { id: 'primary-6', title: 'Primary 6', description: 'BECE preparation focus' }
        ],
        'jhs': [
            { id: 'jhs-1', title: 'JHS 1', description: 'Introduction to junior high curriculum' },
            { id: 'jhs-2', title: 'JHS 2', description: 'Intermediate junior high studies' },
            { id: 'jhs-3', title: 'JHS 3', description: 'Final JHS year with BECE preparation' }
        ],
        'shs': [
            { id: 'shs-1', title: 'SHS 1', description: 'Senior high foundation with specialized tracks' },
            { id: 'shs-2', title: 'SHS 2', description: 'Advanced senior high studies' },
            { id: 'shs-3', title: 'SHS 3', description: 'Final SHS year with WASSCE preparation' }
        ]
    };

    function showLevelOptions(groupKey) {
        const modal = document.getElementById('levelModal');
        const modalTitle = document.getElementById('modalTitle');
        const levelOptions = document.getElementById('levelOptions');
        
        const group = levelGroups[groupKey];
        if (!group) return;
        
        // Set modal title
        const groupTitles = {
            'primary-lower': 'Select Primary Level (1-3)',
            'primary-upper': 'Select Primary Level (4-6)', 
            'jhs': 'Select JHS Level (1-3)',
            'shs': 'Select SHS Level (1-3)'
        };
        modalTitle.textContent = groupTitles[groupKey];
        
        // Clear and populate options
        levelOptions.innerHTML = '';
        group.forEach(level => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'level-option-btn';
            optionDiv.onclick = () => selectLevel(level.id);
            optionDiv.innerHTML = `
                <h4>${level.title}</h4>
                <p>${level.description}</p>
            `;
            levelOptions.appendChild(optionDiv);
        });
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeLevelModal() {
        const modal = document.getElementById('levelModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function selectLevel(levelId) {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('dashboard.select-level', '') }}/${levelId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Handle back button navigation
        const backButton = document.getElementById('backButton');
        if (backButton) {
            backButton.addEventListener('click', function() {
                window.history.back();
            });
        }

        // Handle dashboard back navigation
        const backToDashboard = document.getElementById('backToDashboard');
        if (backToDashboard) {
            backToDashboard.addEventListener('click', function() {
                window.location.href = '{{ route("dashboard.main") }}';
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLevelModal();
            }
        });
    });
</script>
@endpush