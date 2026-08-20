@include('dashboard.quiz.partials.styles')

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Grid layout for document cards */
    .content-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(360px, 460px)) !important;
        gap: 1.5rem !important;
        max-width: 100% !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
        justify-content: flex-start !important;
    }

    /* Horizontal Document Card with Cover Thumbnail */
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
        width: 100%;
        max-width: 460px;
        min-height: 190px;
        box-sizing: border-box;
    }

    .document-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        border-color: var(--secondary-blue, #2677B8);
    }

    /* Left Column: Document Cover Thumbnail Frame */
    .document-cover-frame {
        width: 115px;
        min-width: 115px;
        height: 160px;
        border-radius: 0.75rem;
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
        top: 0.5rem;
        left: 0.5rem;
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

    @media (max-width: 480px) {
        .content-grid {
            grid-template-columns: 1fr !important;
        }

        .document-card {
            flex-direction: column !important;
            max-width: 100% !important;
            gap: 1rem !important;
        }

        .document-cover-frame {
            width: 100% !important;
            height: 150px !important;
            border-radius: 1rem !important;
        }

        .doc-action-btn {
            width: 100% !important;
        }
    }

    /* Dark theme adjustments */
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
</style>
