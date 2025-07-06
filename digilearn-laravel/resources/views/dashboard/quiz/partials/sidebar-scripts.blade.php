<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
    });

    // YouTube-style sidebar functionality
    function initializeSidebar() {
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
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                youtubeSidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
</script>
