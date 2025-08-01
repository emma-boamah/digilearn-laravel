<style>
    :root {
        --primary-red: #E11E2D;
        --primary-red-hover: #c41e2a;
        --secondary-blue: #2677B8;
        --secondary-blue-hover: #1e5a8a;
        --white: #ffffff;
        --gray-25: #fcfcfd;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --sidebar-width-expanded: 280px;
        --sidebar-width-collapsed: 72px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background-color: var(--gray-25);
        color: var(--gray-900);
        line-height: 1.6;
    }

    /* Main Layout Container */
        .main-container {
            display: flex;
            min-height: 100vh;
        }

        /* YouTube-style Sidebar */
        .youtube-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width-expanded);
            height: 100vh;
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            z-index: 1000;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .youtube-sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            height: 64px;
            min-height: 64px;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            margin-right: 1rem;
        }

        .sidebar-toggle-btn:hover {
            background-color: var(--gray-100);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-700);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-logo {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-logo img {
            height: 32px;
            width: auto;
        }

        .sidebar-brand {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-red);
            letter-spacing: -0.025em;
            white-space: nowrap;
        }

        .sidebar-content {
            padding: 1rem 0;
            overflow-y: auto;
            height: calc(100vh - 64px);
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            padding: 0.5rem 1.5rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
            position: relative;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item {
            padding: 0.75rem;
            justify-content: center;
            gap: 0;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border-left: none;
        }

        .sidebar-menu-item:hover {
            background-color: var(--gray-50);
            color: var(--gray-900);
            border-left-color: var(--gray-300);
        }

        .youtube-sidebar.collapsed .sidebar-menu-item:hover {
            border-left-color: transparent;
        }

        .sidebar-menu-item.active {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            border-left-color: var(--primary-red);
            font-weight: 600;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item.active {
            border-left-color: transparent;
            background-color: var(--primary-red);
            color: var(--white);
        }

        .sidebar-menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-menu-text {
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .youtube-sidebar.collapsed .sidebar-menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Tooltip for collapsed state */
        .sidebar-menu-item .tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--gray-800);
            color: var(--white);
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 1001;
            pointer-events: none;
        }

        .youtube-sidebar.collapsed .sidebar-menu-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width-expanded);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .youtube-sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background-color: var(--gray-100);
        }

        .notification-icon {
            width: 20px;
            height: 20px;
            color: var(--gray-600);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        /* Search/Filter Bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
            display: flex;
        }

        .search-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
            padding-right: 3.5rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .search-button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 2.5rem;
            background-color: var(--primary-red);
            border: none;
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #c41e2a;
        }

        .search-icon {
            color: white;
            stroke: currentColor;
        }

        /* Custom Dropdown Styles */
        .custom-dropdown {
            position: relative;
            min-width: 120px;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--primary-red);
            font-size: 0.875rem;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .dropdown-toggle:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .dropdown-chevron {
            width: 16px;
            height: 16px;
            color: var(--primary-red);
            transition: transform 0.2s ease;
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-button.question {
            background-color: var(--primary-red);
            color: var(--white);
        }

        .filter-button.quiz {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .filter-button:hover {
            opacity: 0.9;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 300px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 400;
            color: var(--white);
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.5rem;
            color: var(--white);
            margin-top: 0.5rem;
            opacity: 0.9;
        }

        .hero-view-button {
            background-color: var(--primary-red);
            color: var(--white);
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .hero-view-button:hover {
            background-color: var(--primary-red-hover);
        }

        /* Content Section */
        .content-section {
            padding: 2rem 1rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .quiz-card {
            background-color: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .quiz-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .quiz-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
        }

        .quiz-icon-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.3);
        }

        .quiz-icon {
            width: 80px;
            height: 80px;
            color: var(--white);
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
        }

        .quiz-duration {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            background-color: rgba(0, 0, 0, 0.8);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .quiz-level-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .quiz-info {
            padding: 1.25rem;
        }

        .quiz-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .quiz-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .quiz-subject {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        .quiz-actions {
            display: flex;
            gap: 0.75rem;
        }

        .quiz-open-btn {
            flex: 1;
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quiz-open-btn:hover {
            background-color: var(--primary-red-hover);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .youtube-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
                width: 280px;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0;
            }

            .top-header {
                display: flex;
            }

            .mobile-header {
                display: block;
                z-index: 1001;
            }

            .filter-bar {
                flex-direction: column;
                gap: 12px;
                padding: 16px;
                position: relative;
            }

            .search-box {
                min-width: 100%;
            }

            .content-section {
                padding: 1rem 0.5rem;
            }

            .hero-section {
                height: 200px;
            }

            .hero-content h1 {
                font-size: 24px;
                margin-bottom: 8px;
            }
            
            .hero-content p {
                font-size: 16px;
                margin-bottom: 16px;
            }

            .hero-view-button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.6);
                z-index: 999;
                opacity: 0;
                transition: all 0.3s ease;
            }

            .sidebar-overlay.active {
                opacity: 1;
                display: block;
            }
        }

        /* Mobile Header */
        .mobile-header {
            display: none;
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--white);
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            color: var(--gray-900);
        }

        .mobile-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-hamburger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .mobile-hamburger:hover {
            background-color: var(--gray-100);
        }

        .mobile-hamburger svg {
            stroke: var(--gray-700);
            width: 24px;
            height: 24px;
        }

        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .mobile-logo img {
            height: 28px;
        }

        .mobile-logo .sidebar-brand {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-red);
        }

        .mobile-header .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .mobile-header .notification-btn {
            padding: 0.5rem;
        }

        .mobile-header .notification-icon {
            width: 18px;
            height: 18px;
        }
</style>
