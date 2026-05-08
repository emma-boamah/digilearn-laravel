<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Search Autocomplete Styles */
    .search-autocomplete-container {
        position: relative;
        width: 100%;
    }

    .search-autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        margin-top: 0.25rem;
        z-index: 1050;
        overflow: hidden;
        display: none;
        max-height: 300px;
        overflow-y: auto;
    }

    .search-autocomplete-dropdown.active {
        display: block;
    }

    .search-autocomplete-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: background-color 0.2s;
        color: var(--text-main, #111827);
        font-size: 0.875rem;
    }

    .search-autocomplete-item:hover,
    .search-autocomplete-item.selected {
        background-color: var(--gray-100, #f3f4f6);
    }

    .search-autocomplete-icon {
        color: var(--gray-400, #9ca3af);
        flex-shrink: 0;
    }

    .search-autocomplete-text {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        // Configuration
        const API_SUGGESTIONS_URL = '/api/search/suggestions';
        const API_TRACK_URL = '/api/search/track';
        const DEBOUNCE_DELAY = 300;

        // Find all search containers with a specified domain
        const searchContainers = document.querySelectorAll('[data-search-domain]');

        searchContainers.forEach(container => {
            const domain = container.getAttribute('data-search-domain');
            const input = container.querySelector('.search-input');
            const searchButton = container.querySelector('.search-button');

            if (!input) return;

            // Wrap input and create dropdown
            const wrapper = document.createElement('div');
            wrapper.className = 'search-autocomplete-container';

            // Move input inside wrapper
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            // Create dropdown element
            const dropdown = document.createElement('div');
            dropdown.className = 'search-autocomplete-dropdown';
            wrapper.appendChild(dropdown);

            let debounceTimer;
            let selectedIndex = -1;
            let currentSuggestions = [];

            // Track a query
            const trackSearch = (query) => {
                if (!query || query.trim().length === 0) return;

                fetch(API_TRACK_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        query: query,
                        domain: domain
                    })
                }).catch(err => console.error('Error tracking search:', err));
            };

            // Render dropdown items
            const renderSuggestions = (suggestions) => {
                currentSuggestions = suggestions;
                dropdown.innerHTML = '';
                selectedIndex = -1;

                if (suggestions.length === 0) {
                    dropdown.classList.remove('active');
                    return;
                }

                suggestions.forEach((suggestion, index) => {
                    const item = document.createElement('div');
                    item.className = 'search-autocomplete-item';
                    item.dataset.index = index;
                    item.innerHTML = `
                    <i class="fas fa-search search-autocomplete-icon"></i>
                    <span class="search-autocomplete-text">${escapeHtml(suggestion)}</span>
                `;

                    item.addEventListener('click', () => {
                        input.value = suggestion;
                        dropdown.classList.remove('active');
                        trackSearch(suggestion);

                        // If there's an existing performSearch function (e.g. digilearn.blade.php)
                        if (typeof performSearch === 'function') {
                            performSearch(suggestion);
                        } else if (searchButton) {
                            searchButton.click();
                        } else {
                            // Fallback: try to find a form and submit
                            const form = input.closest('form');
                            if (form) form.submit();
                        }
                    });

                    dropdown.appendChild(item);
                });

                dropdown.classList.add('active');
            };

            // Fetch suggestions
            const fetchSuggestions = (query) => {
                const url = new URL(API_SUGGESTIONS_URL, window.location.origin);
                url.searchParams.append('domain', domain);
                if (query) {
                    url.searchParams.append('q', query);
                }

                fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        renderSuggestions(data.suggestions || []);
                    })
                    .catch(err => console.error('Error fetching suggestions:', err));
            };

            // Input event (typing)
            input.addEventListener('input', (e) => {
                const query = e.target.value.trim();

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    // If query is empty, maybe fetch popular ones or just hide
                    if (query.length > 0) {
                        fetchSuggestions(query);
                    } else {
                        dropdown.classList.remove('active');
                    }
                }, DEBOUNCE_DELAY);
            });

            // Handle focus
            input.addEventListener('focus', () => {
                const query = input.value.trim();
                if (query.length > 0 && currentSuggestions.length > 0) {
                    dropdown.classList.add('active');
                } else if (query.length === 0) {
                    // Optional: fetch default popular suggestions when empty
                    fetchSuggestions('');
                }
            });

            // Click outside to close
            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });

            // Keyboard navigation
            input.addEventListener('keydown', (e) => {
                const items = dropdown.querySelectorAll('.search-autocomplete-item');

                if (dropdown.classList.contains('active') && items.length > 0) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                        updateSelection(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = Math.max(selectedIndex - 1, -1);
                        updateSelection(items);
                    } else if (e.key === 'Enter') {
                        if (selectedIndex >= 0) {
                            e.preventDefault(); // Prevent standard search if selecting an item
                            items[selectedIndex].click();
                            return; // item click handles tracking and search
                        }
                    }
                }

                // Standard Enter (not selecting an autocomplete item)
                if (e.key === 'Enter') {
                    const query = input.value.trim();
                    if (query) {
                        dropdown.classList.remove('active');
                        trackSearch(query);
                    }
                }
            });

            // If there's a search button, track on click
            if (searchButton) {
                searchButton.addEventListener('click', () => {
                    const query = input.value.trim();
                    if (query) {
                        trackSearch(query);
                    }
                });
            }

            function updateSelection(items) {
                items.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.classList.add('selected');
                        // Ensure visible in scroll
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('selected');
                    }
                });

                // Optional: update input text with current selection
                // if (selectedIndex >= 0) {
                //     input.value = currentSuggestions[selectedIndex];
                // }
            }
        });

        // Helper function
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>