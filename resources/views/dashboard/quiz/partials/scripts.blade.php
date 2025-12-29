<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
        initializeSearch();
        initializeDropdowns();
    });

    function initializeSidebar() {
        // Sidebar functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const youtubeSidebar = document.getElementById('youtubeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Delegate clicks for quiz cards and buttons
        document.body.addEventListener('click', function(e) {
            const card = e.target.closest('.quiz-card');
            if (!card) return;

            const quizId = card.getAttribute('data-quiz-id');
            const encodedQuizId = card.getAttribute('data-encoded-quiz-id');
            const startBtn = e.target.closest('.quiz-start-btn');
            const reviseBtn = e.target.closest('.quiz-preview-btn');

            if (startBtn) {
                e.preventDefault();
                e.stopPropagation();
                openQuiz(encodedQuizId);
                return;
            }

            if (reviseBtn) {
                e.preventDefault();
                e.stopPropagation();
                reviseNotes(encodedQuizId);
                return;
            }

            // Card is no longer clickable - only specific buttons work
        });

        console.log('Sidebar elements:', {
            sidebarToggle,
            youtubeSidebar,
            sidebarOverlay
        });

        // Sidebar toggle (works for both desktop and mobile)
        if (sidebarToggle && youtubeSidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Sidebar toggle clicked');

                if (window.innerWidth <= 768) {
                    // Mobile behavior
                    const isOpen = youtubeSidebar.classList.contains('mobile-open');
                    console.log('Sidebar currently open:', isOpen);

                    if (isOpen) {
                        // Close sidebar
                        youtubeSidebar.classList.remove('mobile-open');
                        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                        console.log('Closing sidebar');
                    } else {
                        // Open sidebar
                        youtubeSidebar.classList.add('mobile-open');
                        if (sidebarOverlay) sidebarOverlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                        console.log('Opening sidebar');
                    }
                } else {
                    // Desktop behavior
                    youtubeSidebar.classList.toggle('collapsed');
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
        if (sidebarToggle) {
            sidebarToggle.addEventListener('touchstart', function(e) {
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

    function openQuiz(encodedQuizId) {
        // Add visual feedback
        const card = document.querySelector(`[data-encoded-quiz-id="${encodedQuizId}"]`);
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
            window.location.href = `/quiz/${encodedQuizId}/instructions`;
        }, 500);
    }

    function reviseNotes(encodedQuizId) {
        // Add visual feedback
        const card = document.querySelector(`[data-encoded-quiz-id="${encodedQuizId}"]`);
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
            window.location.href = `/quiz/results?quiz=${encodedQuizId}`;
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

    // Reviews Modal Functions
    function openReviewsModal(encodedQuizId) {
        const modal = document.getElementById(`reviewsModal-${encodedQuizId}`);
        const content = document.getElementById(`reviewsContent-${encodedQuizId}`);

        if (modal && content) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Load reviews
            loadReviews(encodedQuizId);
        }
    }

    function closeReviewsModal(encodedQuizId) {
        const modal = document.getElementById(`reviewsModal-${encodedQuizId}`);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function loadReviews(encodedQuizId) {
        const content = document.getElementById(`reviewsContent-${encodedQuizId}`);
        if (!content) return;

        // Show loading
        content.innerHTML = `
            <div class="reviews-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading reviews...</p>
            </div>
        `;

        // Fetch reviews from API
        fetch(`/api/quiz/${encodedQuizId}/reviews`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReviews(encodedQuizId, data.reviews, data.average_rating, data.total_ratings);
                } else {
                    content.innerHTML = `
                        <div class="reviews-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Failed to load reviews. Please try again.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading reviews:', error);
                content.innerHTML = `
                    <div class="reviews-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load reviews. Please try again.</p>
                    </div>
                `;
            });
    }

    function displayReviews(quizId, reviews, averageRating, totalRatings) {
        const content = document.getElementById(`reviewsContent-${quizId}`);
        if (!content) return;

        let html = '';

        // Overall rating summary
        if (totalRatings > 0) {
            html += `
                <div class="reviews-summary">
                    <div class="summary-rating">
                        <div class="rating-stars">
                            ${generateStars(averageRating)}
                        </div>
                        <span class="rating-score">${averageRating.toFixed(1)}</span>
                        <span class="rating-count">(${totalRatings} ${totalRatings === 1 ? 'review' : 'reviews'})</span>
                    </div>
                </div>
            `;
        }

        // Individual reviews
        if (reviews && reviews.length > 0) {
            html += '<div class="reviews-list">';
            reviews.forEach(review => {
                html += `
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    ${review.user_name ? review.user_name.charAt(0).toUpperCase() : 'U'}
                                </div>
                                <div class="reviewer-details">
                                    <span class="reviewer-name">${review.user_name || 'Anonymous'}</span>
                                    <div class="review-rating">
                                        ${generateStars(review.rating)}
                                    </div>
                                </div>
                            </div>
                            <span class="review-date">${formatDate(review.created_at)}</span>
                        </div>
                        ${review.review ? `<div class="review-text">${review.review}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
        } else {
            html += `
                <div class="no-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <p>No reviews yet. Be the first to review this quiz!</p>
                </div>
            `;
        }

        content.innerHTML = html;
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="fas fa-star ${i <= rating ? 'filled' : ''}"></i>`;
        }
        return stars;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('reviews-modal-overlay')) {
            const encodedQuizId = e.target.id.replace('reviewsModal-', '');
            closeReviewsModal(encodedQuizId);
        }
    });

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
