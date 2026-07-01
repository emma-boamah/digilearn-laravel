<!-- Image Lightbox Overlay -->
<div id="imageLightbox"
    style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.85); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <button id="closeLightbox"
        style="position: absolute; top: 20px; right: 30px; background: none; border: none; color: white; font-size: 2.5rem; cursor: pointer; padding: 10px; transition: transform 0.2s;">&times;</button>
    <img id="lightboxImg" src="" draggable="false"
        style="max-width: 90%; max-height: 90vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); user-select: none; -webkit-user-drag: none;">
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        const lightbox = document.getElementById('imageLightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        const closeBtn = document.getElementById('closeLightbox');

        // Security: Explicitly prevent dragging or right-clicking the lightbox image
        lightboxImg.addEventListener('contextmenu', e => e.preventDefault());
        lightboxImg.addEventListener('dragstart', e => e.preventDefault());

        // Find all images within quiz content areas
        const selectors = [
            '.question-text img',
            '.preamble-box img',
            '.preamble img',
            '.sub-text img',
            '.option-text img',
            '.option-item img',
            '.sample-answer-box img',
            '.question-image'
        ];

        function attachLightbox() {
            const images = document.querySelectorAll(selectors.join(', '));
            images.forEach(img => {
                // Ensure we override the 'Click to resize' title from admin
                img.title = "Click to enlarge";
                img.style.cursor = 'zoom-in';

                // Prevent attaching multiple times
                if (!img.dataset.lightboxAttached) {
                    img.dataset.lightboxAttached = 'true';
                    img.addEventListener('click', function (e) {
                        e.stopPropagation();
                        e.preventDefault(); // Prevents click-through issues
                        lightboxImg.src = this.src;
                        lightbox.style.display = 'flex';
                        document.body.style.overflow = 'hidden'; // prevent scrolling behind
                    });
                }
            });
        }

        // Initial attachment
        setTimeout(attachLightbox, 100);

        // If content is dynamically loaded or changed, re-attach (using MutationObserver)
        const observer = new MutationObserver(() => attachLightbox());
        const targetNode = document.querySelector('.main-layout') || document.querySelector('.workspace') || document.body;
        observer.observe(targetNode, { childList: true, subtree: true });

        // Close logic
        function closeImgLightbox() {
            lightbox.style.display = 'none';
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }

        closeBtn.addEventListener('click', closeImgLightbox);
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) {
                closeImgLightbox();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                closeImgLightbox();
            }
        });
    });
</script>