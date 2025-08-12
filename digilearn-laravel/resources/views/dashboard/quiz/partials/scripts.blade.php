<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
        initializeSearch();
        initializeDropdowns();
    });

    function initializeSidebar() {
        // Sidebar functionality
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        const youtubeSidebar = document.getElementById('youtubeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Delegate clicks for quiz cards and buttons
        document.body.addEventListener('click', function(e) {
            const card = e.target.closest('.quiz-card');
            if (!card) return;

            const quizId = card.getAttribute('data-quiz-id');
            const startBtn = e.target.closest('.quiz-start-btn');
            const reviseBtn = e.target.closest('.quiz-preview-btn');

            if (startBtn) {
                e.preventDefault();
                e.stopPropagation();
                openQuiz(quizId);
                return;
            }

            if (reviseBtn) {
                e.preventDefault();
                e.stopPropagation();
                reviseNotes(quizId);
                return;
            }

            // Click on card anywhere opens quiz
            if (card.hasAttribute('data-open-quiz')) {
                e.preventDefault();
                openQuiz(quizId);
            }
        });

        console.log('Sidebar elements:', {
            desktopSidebarToggle,
            mobileSidebarToggle,
            youtubeSidebar,
            sidebarOverlay
        });

        // Desktop header toggle
        if (desktopSidebarToggle && youtubeSidebar) {
            desktopSidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Desktop sidebar toggle clicked');
                youtubeSidebar.classList.toggle('collapsed');
            });
        }

        // Mobile sidebar toggle
        if (mobileSidebarToggle && youtubeSidebar && sidebarOverlay) {
            mobileSidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Mobile sidebar toggle clicked');
                
                const isOpen = youtubeSidebar.classList.contains('mobile-open');
                console.log('Sidebar currently open:', isOpen);
                
                if (isOpen) {
                    // Close sidebar
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                    console.log('Closing sidebar');
                } else {
                    // Open sidebar
                    youtubeSidebar.classList.add('mobile-open');
                    sidebarOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    console.log('Opening sidebar');
                }
            });
        }

        // Close mobile sidebar when clicking overlay
        if (sidebarOverlay && youtubeSidebar) {
            sidebarOverlay.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Overlay clicked - closing sidebar');
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                if (youtubeSidebar) {
                    youtubeSidebar.classList.remove('mobile-open');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
                document.body.style.overflow = '';
            }
        });

        // Add touch event handling for better mobile experience
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('touchstart', function(e) {
                e.preventDefault();
                this.click();
            }, { passive: false });
        }
    }

    function initializeSearch() {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = this.value.trim();
                    if (query.length > 2) {
                        console.log('Searching for quizzes:', query);
                        // Implement search functionality here
                        filterQuizzes(query);
                    } else if (query.length === 0) {
                        showAllQuizzes();
                    }
                }, 300);
            });
        }
    }

    function initializeDropdowns() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.closest('.custom-dropdown');
                
                // Close other dropdowns
                document.querySelectorAll('.custom-dropdown').forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('open');
                    }
                });
                
                dropdown.classList.toggle('open');
            });
        });
        
        // Handle option selection
        document.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', function() {
                const text = this.querySelector('span') ? this.querySelector('span').textContent : this.textContent;
                const toggle = this.closest('.custom-dropdown').querySelector('.dropdown-toggle span');
                toggle.textContent = text;
                this.closest('.custom-dropdown').classList.remove('open');
                
                // Handle level or subject filtering
                const level = this.getAttribute('data-level');
                const subject = this.getAttribute('data-subject');
                
                if (level) {
                    filterByLevel(level);
                } else if (subject) {
                    filterBySubject(subject);
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-dropdown')) {
                document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
                    dropdown.classList.remove('open');
                });
            }
        });
    }

    function openQuiz(quizId) {
        // Add visual feedback
        const card = document.querySelector(`[data-quiz-id="${quizId}"]`);
        if (card) {
            card.style.opacity = '0.7';
            card.style.transform = 'scale(0.98)';
            
            // Add loading state
            const startBtn = card.querySelector('.quiz-start-btn');
            if (startBtn) {
                startBtn.innerHTML = `
                    <svg class="btn-icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Loading...
                `;
            }
        }
        
        // Navigate to quiz instructions page with proper route
        setTimeout(() => {
            window.location.href = `/quiz/${quizId}/instructions`;
        }, 500);
    }

    function reviseNotes(quizId) {
        // Add visual feedback
        const card = document.querySelector(`[data-quiz-id="${quizId}"]`);
        if (card) {
            const btn = card.querySelector('.quiz-preview-btn');
            if (btn) {
                btn.innerHTML = `
                    <svg class="btn-icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Opening...
                `;
            }
        }
        
        // Navigate to last results page (revise notes)
        setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            // Ideally, fetch the last attempt details via API; fallback to results route with quiz id only
            window.location.href = `/quiz/results?quiz=${quizId}`;
        }, 300);
    }

    function filterQuizzes(query) {
        const quizCards = document.querySelectorAll('.quiz-card');
        quizCards.forEach(card => {
            const title = card.querySelector('.quiz-title').textContent.toLowerCase();
            const subject = card.querySelector('.quiz-subject').textContent.toLowerCase();
            const description = card.querySelector('.quiz-description').textContent.toLowerCase();
            
            if (title.includes(query.toLowerCase()) || 
                subject.includes(query.toLowerCase()) || 
                description.includes(query.toLowerCase())) {
                card.style.display = 'block';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 100);
            } else {
                card.style.display = 'none';
            }
        });
    }

    function showAllQuizzes() {
        const quizCards = document.querySelectorAll('.quiz-card');
        quizCards.forEach(card => {
            card.style.display = 'block';
            card.style.opacity = '1';
        });
    }

    function filterByLevel(level) {
        console.log('Filtering by level:', level);
        // Implement level filtering logic here
    }

    function filterBySubject(subject) {
        console.log('Filtering by subject:', subject);
        // Implement subject filtering logic here
    }

    // Add CSS animation for spinning
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);
</script>
