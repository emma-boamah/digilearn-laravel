@include('dashboard.quiz.partials.styles')

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    :root {
        --safe-area-inset-top: env(safe-area-inset-top, 0px);
    }

    /* Search/Filter Bar */
    .filter-bar {
        position: fixed !important;
        top: calc(60px + var(--safe-area-inset-top)) !important;
        left: 0;
        width: 100vw;
        padding-left: calc(var(--sidebar-width-expanded, 240px) + 0.75rem);
        padding-right: 0.75rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        z-index: 998 !important;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        background-color: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(10px) saturate(160%);
        -webkit-backdrop-filter: blur(10px) saturate(160%);
        border-bottom: 1px solid var(--gray-200);
        box-sizing: border-box;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .youtube-sidebar.collapsed~.main-content .filter-bar {
        padding-left: calc(var(--sidebar-width-collapsed, 80px) + 0.75rem);
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        max-width: 100%;
    }

    .search-container {
        position: relative;
        flex: 1;
        min-width: 200px;
        display: flex;
    }

    .search-box {
        position: relative;
        width: 100%;
        display: flex;
    }

    .current-level-display {
        display: inline-flex;
        align-items: center;
        gap: 1.25rem;
        margin-left: 0.5rem;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 2px 4px;
    }

    .current-level-display::-webkit-scrollbar {
        display: none;
    }

    .grade-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--gray-100);
        border: 2px solid var(--gray-200);
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
        height: 40px;
        box-sizing: border-box;
    }

    .grade-tab:hover:not(.locked) {
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        background: rgba(38, 119, 184, 0.05);
    }

    .grade-tab.active {
        background: rgba(38, 119, 184, 0.05);
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.2);
    }

    .grade-tab.locked {
        opacity: 0.6;
        background-color: var(--gray-50);
        cursor: not-allowed;
        pointer-events: none;
    }

    .grade-tab i {
        font-size: 0.75rem;
    }

    .grade-tab.locked i {
        color: var(--gray-400);
    }

    /* Responsive grade name handling */
    .grade-full-name {
        display: inline;
    }

    .grade-short-name {
        display: none;
    }

    .search-input {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 0.5rem;
        width: 100%;
        font-size: 0.875rem;
        padding-right: 3.5rem;
        background: var(--white);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--secondary-blue);
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    .search-button {
        position: absolute;
        right: 1px;
        top: 1px;
        height: calc(100% - 2px);
        width: 2.5rem;
        background-color: var(--secondary-blue, #2677B8);
        border: none;
        border-top-right-radius: 0.4rem;
        border-bottom-right-radius: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .search-button:hover {
        background-color: var(--secondary-blue-hover, #1e5a8a);
    }

    .search-icon {
        color: white;
        stroke: currentColor;
    }

    .mobile-search-toggle {
        display: none;
        background: var(--secondary-blue);
        border: none;
        border-radius: 0.5rem;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--white);
        align-items: center;
        justify-content: center;
    }

    .search-close {
        display: none;
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0.5rem;
        cursor: pointer;
        color: var(--gray-400);
        z-index: 5;
    }

    .search-close:hover {
        color: var(--gray-600);
    }

    /* Horizontal Subjects Filter */
    .subjects-filter-container {
        position: fixed !important;
        left: 0 !important;
        top: calc(116px + var(--safe-area-inset-top)) !important;
        width: 100vw !important;
        background-color: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding-left: calc(var(--sidebar-width-expanded, 240px) + 0.75rem);
        padding-right: 1rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        overflow: hidden;
        box-sizing: border-box;
        z-index: 997 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .youtube-sidebar.collapsed~.main-content .subjects-filter-container {
        padding-left: calc(var(--sidebar-width-collapsed, 80px) + 0.75rem);
    }

    .subjects-filter {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 0.5rem 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-behavior: smooth;
        width: 100%;
    }

    .subjects-filter::-webkit-scrollbar {
        display: none;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .subject-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background-color: var(--gray-100);
        border: 2px solid transparent;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
        text-decoration: none;
    }

    .subject-chip i {
        font-size: 0.875rem;
    }

    .subject-chip:hover {
        background-color: var(--gray-200);
        color: var(--secondary-blue);
    }

    .subject-chip.active {
        background: rgba(38, 119, 184, 0.05);
        border-color: var(--secondary-blue);
        color: var(--secondary-blue);
        box-shadow: 0 4px 12px rgba(38, 119, 184, 0.2);
    }

    /* =============================================
     HORIZONTAL DOCUMENT CARD STYLES
     ============================================= */
    .document-card {
        background-color: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 1.25rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        gap: 1.25rem;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        min-height: 190px;
    }

    .document-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        border-color: var(--secondary-blue, #2677B8);
    }

    /* Left Column: Cover Frame */
    .document-cover-frame {
        width: 115px;
        min-width: 115px;
        height: 160px;
        border-radius: 0.625rem;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
        background-color: var(--gray-100);
        border: 1px solid var(--gray-200);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
        display: block;
        text-decoration: none;
    }

    .document-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        transition: transform 0.35s ease;
        display: block;
    }

    .document-card:hover .document-cover-img {
        transform: scale(1.06);
    }

    .document-cover-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.75rem;
        text-align: center;
        background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        position: relative;
    }

    .cover-fallback-badge {
        position: absolute;
        top: 0.625rem;
        left: 0.625rem;
        font-size: 0.5625rem;
        font-weight: 800;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .cover-fallback-icon {
        font-size: 2.25rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0.5rem;
    }

    .cover-fallback-title {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #e2e8f0;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Right Column: Card Body */
    .document-card-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .document-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    /* Format Pills */
    .doc-format-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6875rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .doc-format-pill.pdf {
        background-color: #fef2f2;
        color: #ef4444;
    }

    .doc-format-pill.pptx,
    .doc-format-pill.ppt {
        background-color: #fffbeb;
        color: #d97706;
    }

    .doc-format-pill.doc,
    .doc-format-pill.docx {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .doc-format-pill.epub {
        background-color: #ecfdf5;
        color: #059669;
    }

    .doc-format-pill.default {
        background-color: #f3f4f6;
        color: #4b5563;
    }

    .doc-file-size {
        font-size: 0.75rem;
        color: var(--gray-400);
        font-weight: 600;
    }

    .document-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.35;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .document-card-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .document-card-title a:hover {
        color: var(--secondary-blue, #2677B8);
    }

    .document-tags-row {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        flex-wrap: wrap;
        margin-bottom: 0.5rem;
    }

    .doc-grade-tag {
        display: inline-flex;
        align-items: center;
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 0.375rem;
        background-color: #f5f3ff;
        color: #7c3aed;
    }

    .doc-subject-tag {
        display: inline-flex;
        align-items: center;
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 0.375rem;
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    .document-meta-row {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        color: var(--gray-600);
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .document-meta-row i,
    .document-meta-row svg {
        color: var(--gray-400);
        font-size: 0.75rem;
    }

    .doc-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        border: 1.5px solid var(--gray-200);
        background-color: var(--white);
        color: var(--gray-800);
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        width: fit-content;
        max-width: 100%;
    }

    .doc-action-btn i {
        font-size: 0.625rem;
        transition: transform 0.2s ease;
    }

    .doc-action-btn:hover {
        border-color: var(--secondary-blue, #2677B8);
        color: var(--secondary-blue, #2677B8);
        background-color: #f0f7ff;
    }

    .doc-action-btn:hover i {
        transform: translateX(2px);
    }

    /* Content Grid Configuration for Horizontal Cards */
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.5rem;
        max-width: 100%;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    @media (min-width: 1280px) {
        .content-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1680px) {
        .content-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 480px) {
        .document-card {
            flex-direction: column;
            gap: 1rem;
        }

        .document-cover-frame {
            width: 100%;
            height: 150px;
            border-radius: 1rem;
        }

        .doc-action-btn {
            width: 100%;
        }
    }

    /* Dark theme adjustments */
    [data-theme="dark"] .filter-bar {
        background-color: #000000;
    }

    [data-theme="dark"] .subjects-filter-container {
        background-color: #16181c;
        border-color: var(--border-color);
    }

    [data-theme="dark"] .grade-tab {
        background-color: #202327;
        border-color: #2f3336;
        color: #e2e8f0;
    }

    [data-theme="dark"] .grade-tab:hover:not(.locked) {
        border-color: var(--secondary-blue);
        color: #60a5fa;
        background: rgba(38, 119, 184, 0.15);
    }

    [data-theme="dark"] .grade-tab.active {
        background: rgba(38, 119, 184, 0.15);
        border-color: var(--secondary-blue);
        color: #60a5fa;
    }

    [data-theme="dark"] .grade-tab.locked {
        background-color: #16181c;
        border-color: #2f3336;
    }

    [data-theme="dark"] .subject-chip {
        background-color: #202327;
        color: #e2e8f0;
        border-color: transparent;
    }

    [data-theme="dark"] .subject-chip:hover {
        background-color: #2f3336;
        color: #60a5fa;
    }

    [data-theme="dark"] .subject-chip.active {
        background: rgba(38, 119, 184, 0.15);
        border-color: var(--secondary-blue);
        color: #60a5fa;
    }

    [data-theme="dark"] .filter-label {
        color: #a4b1cd;
    }

    [data-theme="dark"] .search-input {
        background-color: #16181c;
        border-color: #2f3336;
        color: #ffffff;
    }

    [data-theme="dark"] .document-card {
        background-color: var(--bg-surface);
        border-color: var(--border-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    [data-theme="dark"] .document-card:hover {
        border-color: var(--secondary-blue, #2677B8);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.6);
    }

    [data-theme="dark"] .document-cover-frame {
        background-color: #202327;
        border-color: var(--border-color);
    }

    [data-theme="dark"] .doc-action-btn {
        background-color: var(--bg-surface);
        border-color: var(--border-color);
        color: var(--text-main);
    }

    [data-theme="dark"] .doc-action-btn:hover {
        background-color: #1e293b;
        color: #60a5fa;
        border-color: #3b82f6;
    }

    [data-theme="dark"] .doc-format-pill.pdf {
        background-color: rgba(239, 68, 68, 0.15);
        color: #f87171;
    }

    [data-theme="dark"] .doc-format-pill.pptx,
    [data-theme="dark"] .doc-format-pill.ppt {
        background-color: rgba(217, 119, 6, 0.15);
        color: #fbbf24;
    }

    [data-theme="dark"] .doc-format-pill.doc,
    [data-theme="dark"] .doc-format-pill.docx {
        background-color: rgba(37, 99, 235, 0.15);
        color: #60a5fa;
    }

    [data-theme="dark"] .doc-format-pill.epub {
        background-color: rgba(5, 150, 105, 0.15);
        color: #34d399;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .filter-bar {
            position: fixed !important;
            top: calc(76px + var(--safe-area-inset-top)) !important;
            left: 0 !important;
            width: 100vw !important;
            height: 56px !important;
            padding: 0 0.75rem !important;
            gap: 0.5rem !important;
            overflow: hidden !important;
            z-index: 998 !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }

        .filter-row {
            flex-wrap: nowrap !important;
            gap: 0.5rem !important;
            justify-content: flex-start !important;
            width: 100% !important;
            height: 100% !important;
        }

        .subjects-filter-container {
            left: 0 !important;
            width: 100vw !important;
            padding: 0.5rem 0 !important;
            height: auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            position: fixed !important;
            top: calc(132px + var(--safe-area-inset-top)) !important;
            z-index: 997 !important;
            gap: 0 !important;
            background-color: var(--white);
        }

        .search-container {
            display: none;
        }

        .mobile-search-toggle {
            display: flex;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            padding: 0.75rem !important;
        }

        #filterBar.search-active .search-container {
            display: flex;
            position: absolute;
            inset: 0;
            z-index: 10;
            background: var(--white);
            padding: 0.5rem 0.75rem;
            align-items: center;
        }

        #filterBar.search-active .search-box {
            background: var(--gray-100);
            border-radius: 0.5rem;
            width: 100%;
            display: flex;
        }

        #filterBar.search-active .search-close {
            display: flex;
        }

        #filterBar.search-active .level-container,
        #filterBar.search-active .current-level-display,
        #filterBar.search-active .mobile-search-toggle {
            display: none;
        }

        .search-box {
            min-width: 0;
            flex: 1;
        }

        .level-container {
            flex-shrink: 0;
            width: auto;
        }

        .current-level-display {
            flex: 1 !important;
            min-width: 0 !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            padding: 0 !important;
            height: 100% !important;
            -webkit-overflow-scrolling: touch;
        }

        .grade-tab {
            padding: 0.5rem 0.75rem;
            height: 36px;
        }

        .grade-full-name {
            display: none;
        }

        .grade-short-name {
            display: inline;
        }
    }

    @supports (padding-top: env(safe-area-inset-top)) {
        .filter-bar {
            padding-top: calc(0.75rem + var(--safe-area-inset-top));
        }
    }
</style>
