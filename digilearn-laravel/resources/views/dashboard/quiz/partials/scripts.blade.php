<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
        initializeSearch();
    });

    function initializeSidebar() {
        // Sidebar functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        const youtubeSidebar = document.getElementById('youtubeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Desktop header toggle
        if (desktopSidebarToggle) {
            desktopSidebarToggle.addEventListener('click', function() {
                youtubeSidebar.classList.toggle('collapsed');
            });
        }

        // Mobile sidebar toggle
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', function() {
                youtubeSidebar.classList.toggle('mobile-open');
                sidebarOverlay.classList.toggle('active');
                document.body.style.overflow = youtubeSidebar.classList.contains('mobile-open') ? 'hidden' : '';
            });
        }

        // Close mobile sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            youtubeSidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
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
                    }
                }, 300);
            });
        }
    }

    function openQuiz(quizId) {
        // Add visual feedback
        const card = document.querySelector(`[data-quiz-id="${quizId}"]`);
        if (card) {
            card.style.opacity = '0.7';
            card.style.transform = 'scale(0.98)';
        }
        
        // Navigate to quiz instructions page with proper route
        window.location.href = `/dashboard/quiz/${quizId}/instructions`;
    }
</script>
