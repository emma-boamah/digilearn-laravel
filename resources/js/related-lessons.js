// Enhanced Related Lessons Component for Phase 1
function relatedLessonsComponent() {
    return {
        lessons: @json($relatedLessons ?? []),
        filteredLessons: [],
        filters: {
            sortBy: 'related_score',
            showDebugInfo: false
        },
        loading: false,
        
        init() {
            this.filteredLessons = [...this.lessons];
            this.$nextTick(() => {
                this.initializeVideoCards();
            });
        },
        
        updateRelatedLessons() {
            this.loading = true;
            
            // Apply sorting
            let filtered = this.applySorting(this.lessons, this.filters.sortBy);
            
            this.filteredLessons = filtered;
            
            this.loading = false;
            
            // Reinitialize video facade for new elements
            this.$nextTick(() => {
                this.initializeVideoCards();
            });
        },
        
        applySorting(lessons, sortBy) {
            return [...lessons].sort((a, b) => {
                switch(sortBy) {
                    case 'related_score':
                        return (b.related_score || 0) - (a.related_score || 0);
                    case 'popularity':
                        return (b.views || 0) - (a.views || 0);
                    case 'difficulty':
                        const aScore = this.getDifficultyScore(a.level);
                        const bScore = this.getDifficultyScore(b.level);
                        return aScore - bScore;
                    case 'instructor':
                        return (a.instructor || '').localeCompare(b.instructor || '');
                    default:
                        return 0;
                }
            });
        },
        
        getDifficultyScore(level) {
            const difficultyMap = {
                'primary-1': 1, 'primary-2': 2, 'primary-3': 3,
                'primary-4': 4, 'primary-5': 5, 'primary-6': 6,
                'jhs-1': 7, 'jhs-2': 8, 'jhs-3': 9,
                'shs-1': 10, 'shs-2': 11, 'shs-3': 12,
                'university': 13
            };
            return difficultyMap[level] || 5;
        },
        
        resetFilters() {
            this.filters.sortBy = 'related_score';
            this.updateRelatedLessons();
        },
        
        showUpgradeModal(upgradePrompt) {
            // Dispatch event for upgrade modal component
            window.dispatchEvent(new CustomEvent('show-upgrade-modal', {
                detail: upgradePrompt
            }));
        },
        
        hasDebugMode() {
            return new URLSearchParams(window.location.search).has('debug');
        },
        
        toggleDebugInfo() {
            this.filters.showDebugInfo = !this.filters.showDebugInfo;
        },
        
        initializeVideoCards() {
            // Integrate with existing VideoFacadeManager
            if (window.videoFacadeManager) {
                // Defer to ensure DOM is ready
                setTimeout(() => {
                    window.videoFacadeManager.initializeCards();
                }, 100);
            }
        }
    };
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof relatedLessonsComponent === 'function') {
        // Make component available globally
        window.relatedLessonsComponent = relatedLessonsComponent;
    }
});