@include('dashboard.quiz.partials.styles')

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Responsive Grid layout for document cards (Max 3 Columns) */
    .content-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1.25rem !important;
        max-width: 100% !important;
        margin: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    @media (min-width: 640px) {
        .content-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (min-width: 1024px) {
        .content-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    /* Horizontal Document Card with Cover Thumbnail */
    .document-card {
        background-color: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 1rem;
        padding: 1rem;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        gap: 1rem;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.25s ease;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        width: 100%;
        min-height: 155px;
        box-sizing: border-box;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .document-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        border-color: var(--secondary-blue, #2677B8);
        color: inherit;
    }

    /* Left Column: Document Cover Thumbnail Frame */
    .document-cover-frame {
        width: 95px;
        min-width: 95px;
        height: 135px;
        border-radius: 0.625rem;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
        background-color: var(--gray-100);
        border: 1px solid var(--gray-200);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
        display: block;
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

    .doc-card-arrow {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-400);
        font-size: 0.6875rem;
        transition: all 0.25s ease;
        flex-shrink: 0;
    }

    .document-card:hover .doc-card-arrow {
        background-color: var(--secondary-blue, #2677B8);
        color: #ffffff;
        transform: translateX(3px);
    }

    /* Format Pills */
    .doc-format-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.625rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
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
        font-size: 0.6875rem;
        color: var(--gray-400);
        font-weight: 600;
    }

    .document-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.3;
        margin-bottom: 0.35rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }

    .document-card:hover .document-card-title {
        color: var(--secondary-blue, #2677B8);
    }

    .document-tags-row {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-wrap: wrap;
        margin-bottom: 0.35rem;
    }

    .doc-grade-tag {
        display: inline-flex;
        align-items: center;
        font-size: 0.625rem;
        font-weight: 600;
        padding: 0.12rem 0.45rem;
        border-radius: 0.3rem;
        background-color: #f5f3ff;
        color: #7c3aed;
    }

    .doc-subject-tag {
        display: inline-flex;
        align-items: center;
        font-size: 0.625rem;
        font-weight: 600;
        padding: 0.12rem 0.45rem;
        border-radius: 0.3rem;
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    .document-meta-row {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.6875rem;
        color: var(--gray-600);
        font-weight: 600;
    }

    .document-meta-row i,
    .document-meta-row svg {
        color: var(--gray-400);
        font-size: 0.75rem;
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

    [data-theme="dark"] .document-card-title {
        color: var(--text-main);
    }

    [data-theme="dark"] .doc-card-arrow {
        background-color: #26292e;
        color: #94a3b8;
    }

    [data-theme="dark"] .document-card:hover .doc-card-arrow {
        background-color: var(--secondary-blue, #2677B8);
        color: #ffffff;
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
