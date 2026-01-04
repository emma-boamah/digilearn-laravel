@extends('layouts.dashboard-components')

@section('title', 'My Notes - ' . config('app.name', 'ShoutOutGh'))

@section('content')
    <!-- Notes Content -->
    <div class="content-header">
        <h1 class="page-title">Notes</h1>
        <p class="page-subtitle">Manage your saved lesson notes</p>

        <div class="filter-tabs">
            <div class="search-container">
                <input type="text" id="notes-search" placeholder="Search notes..." class="search-input">
                <i class="fas fa-search search-icon"></i>
            </div>
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="dates">Dates</button>
        </div>
    </div>

    <div class="notes-container">
        @if(isset($notes) && count($notes) > 0)
            <div class="notes-grid">
                @foreach($notes as $note)
                <div class="note-card" data-note-id="{{ $note['id'] }}" data-encoded-id="{{ $note['encoded_id'] }}" data-title="{{ $note['title'] }}" data-content="{{ $note['content'] }}">
                    <h3 class="note-title">{{ $note['title'] }}</h3>
                    <p class="note-subject">{{ $note['subject'] }}</p>
                    <p class="note-date">{{ $note['created_at'] }}</p>

                    <div class="note-actions">
                        <button class="note-action-btn open" onclick="window.location.href='{{ route('dashboard.notes.view', $note['encoded_id']) }}'">
                            <i class="fas fa-eye"></i>
                            Open
                        </button>
                        <button class="note-action-btn delete" onclick="deleteNote({{ $note['id'] }}, '{{ $note['encoded_id'] }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- Sample notes for demo -->
            <div class="notes-grid">
                @for($i = 1; $i <= 12; $i++)
                <div class="note-card" data-note-id="{{ $i }}" data-encoded-id="{{ \App\Services\UrlObfuscator::encode($i) }}" data-title="Living and Non Living organism" data-content="Detailed notes about living and non-living organisms...">
                    <h3 class="note-title">Living and Non Living organism</h3>
                    <p class="note-subject">(Science -Note G1-3)</p>
                    <p class="note-date">April 2025</p>

                    <div class="note-actions">
                        <button class="note-action-btn open" onclick="window.location.href='{{ route('dashboard.notes.view', \App\Services\UrlObfuscator::encode($i)) }}'">
                            <i class="fas fa-eye"></i>
                            Open
                        </button>
                        <button class="note-action-btn delete" onclick="deleteNote({{ $i }}, '{{ \App\Services\UrlObfuscator::encode($i) }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endfor
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .top-header {
        padding-left: var(--sidebar-width-expanded);
    }
    .content-header {
        margin-top: 60px;
        background-color: var(--white);
        padding: 2rem 2rem 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: var(--gray-600);
        font-size: 1rem;
    }

    /* Filter Tabs */
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
        align-items: center;
    }

    .search-container {
        position: relative;
        flex: 1;
        max-width: 300px;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid var(--gray-200);
        border-radius: 2rem;
        font-size: 0.875rem;
        background-color: var(--white);
        transition: all 0.2s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--secondary-blue);
        box-shadow: 0 0 0 3px rgba(38, 162, 220, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 0.875rem;
    }

    .filter-tab {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: var(--white);
        color: var(--gray-600);
        border: 2px solid var(--gray-200);
    }

    .filter-tab.active {
        background-color: var(--secondary-blue);
        color: var(--white);
        border-color: var(--secondary-blue);
    }

    .filter-tab:hover:not(.active) {
        background-color: var(--gray-50);
        border-color: var(--gray-300);
    }

    /* Notes Grid */
    .notes-container {
        padding: 2rem;
    }

    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .note-card {
        background-color: var(--white);
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .note-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .note-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .note-subject {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
    }

    .note-date {
        font-size: 0.75rem;
        color: var(--gray-500);
        margin-bottom: 1.5rem;
    }

    .note-actions {
        display: flex;
        gap: 0.75rem;
    }

    .note-action-btn {
        flex: 1;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .note-action-btn.open {
        background-color: var(--secondary-blue);
        color: var(--white);
    }

    .note-action-btn.open:hover {
        background-color: #1e5a8a;
    }

    .note-action-btn.delete {
        background-color: var(--white);
        color: var(--primary-red);
        border: 2px solid var(--primary-red);
    }

    .note-action-btn.delete:hover {
        background-color: var(--primary-red);
        color: var(--white);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content-header {
            padding: 1.5rem 1rem 1rem;
        }

        .notes-container {
            padding: 1rem;
        }

        .notes-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .filter-tabs {
            flex-wrap: wrap;
        }

        .search-container {
            flex-basis: 100%;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    let currentSearchTerm = '';
    let currentFilter = 'all';

    document.addEventListener('DOMContentLoaded', function() {
        initializeFilterTabs();
        initializeSearch();
        initializeNoteActions();
    });

    function initializeFilterTabs() {
        const filterTabs = document.querySelectorAll('.filter-tab');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                currentFilter = this.dataset.filter;
                filterNotes(currentSearchTerm, currentFilter);
            });
        });
    }

    function initializeSearch() {
        const searchInput = document.getElementById('notes-search');

        searchInput.addEventListener('input', function() {
            currentSearchTerm = this.value.trim().toLowerCase();
            filterNotes(currentSearchTerm, currentFilter);
        });
    }

    function filterNotes(searchTerm, filter) {
        const noteCards = document.querySelectorAll('.note-card');

        noteCards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            const content = card.dataset.content.toLowerCase();
            const date = card.querySelector('.note-date').textContent.toLowerCase();

            // Check search match
            const matchesSearch = !searchTerm ||
                title.includes(searchTerm) ||
                content.includes(searchTerm);

            // Check filter match
            let matchesFilter = true;
            if (filter === 'dates') {
                // For now, show all for dates - can implement date filtering later
                matchesFilter = true;
            }

            // Show card if both search and filter match
            card.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
        });
    }

    function initializeNoteActions() {
        const noteCards = document.querySelectorAll('.note-card');

        noteCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.note-action-btn')) {
                    return;
                }

                const encodedId = this.dataset.encodedId;
                window.location.href = `/dashboard/notes/${encodedId}`;
            });
        });
    }

    function deleteNote(noteId) {
        if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
            // Add loading state
            const noteCard = document.querySelector(`[data-note-id="${noteId}"]`);
            noteCard.style.opacity = '0.5';

            // Simulate API call
            setTimeout(() => {
                noteCard.remove();

                // Show success message
                showSuccessMessage('Note deleted successfully!');
            }, 500);
        }
    }

    function showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        successDiv.textContent = message;

        document.body.appendChild(successDiv);

        setTimeout(() => {
            successDiv.remove();
        }, 3000);
    }
</script>
@endpush
