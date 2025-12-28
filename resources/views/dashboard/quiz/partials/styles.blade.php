<style nonce="{{ request()->attributes->get('csp_nonce') }}">
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
      --success: #10B981;
      --warning: #F59E0B;
      --error: #EF4444;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
      --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
      --sidebar-width-expanded: 240px;
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
      overflow-x: hidden;
      width: 100%;
      max-width: 100%;
  }

  /* Main Layout Container */
  .main-container {
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
      width: 100%;
      max-width: 100%;
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
      box-shadow: var(--shadow-lg);
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
      overflow-x: hidden;
      height: calc(100vh - 64px);
      scrollbar-width: none; /* Hide scrollbar for Firefox */
  }

  .sidebar-content::-webkit-scrollbar {
      display: none; /* Hide scrollbar for WebKit browsers */
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

  /* Main Content Area - FIXED */
  .main-content {
      flex: 1;
      margin-left: var(--sidebar-width-expanded);
      width: calc(100vw - var(--sidebar-width-expanded));
      max-width: calc(100vw - var(--sidebar-width-expanded));
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow-x: hidden;
      min-height: 100vh;
  }

  .youtube-sidebar.collapsed ~ .main-content {
      margin-left: var(--sidebar-width-collapsed);
      width: calc(100vw - var(--sidebar-width-collapsed));
      max-width: calc(100vw - var(--sidebar-width-collapsed));
  }

  /* Top Header */
  .top-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem;
      padding-left: var(--sidebar-width-expanded);
      background-color: var(--white);
      border-bottom: 1px solid var(--gray-200);
      position: sticky;
      top: 0;
      z-index: 999;
      backdrop-filter: blur(8px);
      background-color: rgba(255, 255, 255, 0.95);
      width: 100%;
      max-width: 100%;
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

  /* Search/Filter Bar - FIXED */
  .filter-bar {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem;
      background-color: var(--white);
      border-bottom: 1px solid var(--gray-200);
      flex-wrap: nowrap;
      overflow: visible;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
  }

  .search-box {
      position: relative;
      flex: 1;
      min-width: 300px;
      display: flex;
      max-width: 100%;
  }

  .search-input {
      padding: 0.75rem 1rem;
      border: 1px solid var(--gray-300);
      border-radius: 0.5rem;
      width: 100%;
      font-size: 0.875rem;
      padding-right: 3.5rem;
      box-sizing: border-box;
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
      flex-shrink: 0;
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
      box-sizing: border-box;
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

  .dropdown-menu {
      position: absolute;
      top: calc(100% + 8px);
      left: 0;
      right: 0;
      background-color: var(--white);
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1100;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      max-height: 60vh;
      overflow-y: auto;
  }

  .custom-dropdown.open .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
  }

  .custom-dropdown.open .dropdown-chevron {
      transform: rotate(180deg);
  }

  .dropdown-section {
      padding: 0.5rem 0;
      border-bottom: 1px solid var(--gray-100);
  }

  .dropdown-section:last-child {
      border-bottom: none;
  }

  .section-header {
      padding: 0.5rem 1rem;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--gray-500);
      letter-spacing: 0.5px;
  }

  .dropdown-option {
      display: flex;
      align-items: center;
      color: var(--gray-700);
      padding: 0.75rem 1rem;
      cursor: pointer;
      transition: background-color 0.2s ease;
  }

  .dropdown-option:hover {
      background-color: var(--gray-50);
  }

  .subject-icon {
      width: 18px;
      height: 18px;
      margin-right: 0.75rem;
      color: var(--gray-600);
  }

  .filter-button {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      flex-shrink: 0;
      white-space: nowrap;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
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
      width: 100%;
      max-width: 100%;
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
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
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

  /* Content Section - FIXED */
  .content-section {
      padding: 2rem 1rem;
      background-color: var(--gray-25);
      overflow-x: hidden;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
  }

  .content-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 2rem;
      max-width: 100%;
      margin: 0 auto;
      width: 100%;
      box-sizing: border-box;
  }

  /* Enhanced Quiz Card Styles */
  .quiz-card {
      background-color: var(--white);
      border-radius: 1rem;
      overflow: hidden;
      box-shadow:
          0 4px 6px -1px rgba(0, 0, 0, 0.1),
          0 2px 4px -1px rgba(0, 0, 0, 0.06),
          0 0 0 1px rgba(225, 30, 45, 0.1),
          0 0 20px rgba(225, 30, 45, 0.05);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      border: 2px solid transparent;
      background: linear-gradient(var(--white), var(--white)) padding-box,
                  linear-gradient(135deg, rgba(225, 30, 45, 0.2), rgba(38, 119, 184, 0.2)) border-box;
      position: relative;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      backdrop-filter: blur(10px);
  }

  .quiz-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-red), var(--secondary-blue));
      border-radius: 1rem 1rem 0 0;
      z-index: 1;
  }

  .quiz-card:hover {
      transform: translateY(-8px);
      box-shadow:
          0 20px 25px -5px rgba(0, 0, 0, 0.1),
          0 10px 10px -5px rgba(0, 0, 0, 0.04),
          0 0 0 2px rgba(225, 30, 45, 0.3),
          0 0 20px rgba(225, 30, 45, 0.1);
      border-color: var(--primary-red);
      background: linear-gradient(var(--white), var(--white)) padding-box,
                  linear-gradient(135deg, rgba(225, 30, 45, 0.4), rgba(38, 119, 184, 0.4)) border-box;
  }

  .quiz-header {
      position: relative;
      background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
      padding: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 120px;
  }

  .quiz-icon-container {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 1rem;
      padding: 1rem;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
  }

  .quiz-main-icon {
      width: 48px;
      height: 48px;
      color: var(--white);
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  }

  .quiz-level-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background-color: rgba(255, 255, 255, 0.9);
      color: var(--secondary-blue);
      padding: 0.5rem 1rem;
      border-radius: 2rem;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  .quiz-difficulty {
      position: absolute;
      bottom: 1rem;
      left: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background-color: rgba(255, 255, 255, 0.9);
      padding: 0.5rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--gray-700);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  .difficulty-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
  }

  .quiz-content {
      padding: 1.5rem;
  }

  .quiz-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: 0.75rem;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
  }

  .quiz-description {
      font-size: 0.875rem;
      color: var(--gray-600);
      line-height: 1.5;
      margin-bottom: 1.5rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
  }

  .quiz-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.5rem;
      padding: 1rem;
      background-color: var(--gray-50);
      border-radius: 0.75rem;
      border: 1px solid var(--gray-100);
  }

  .stat-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: var(--gray-700);
      font-weight: 500;
  }

  .stat-icon {
      width: 16px;
      height: 16px;
      color: var(--secondary-blue);
      flex-shrink: 0;
  }

  .quiz-subject {
      color: var(--primary-red) !important;
      font-weight: 600;
  }

  .quiz-progress {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background-color: var(--gray-50);
      border-radius: 0.75rem;
      border: 1px solid var(--gray-100);
  }

  .progress-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--gray-700);
  }

  .progress-bar {
      width: 100%;
      height: 8px;
      background-color: var(--gray-200);
      border-radius: 4px;
      overflow: hidden;
  }

  .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--secondary-blue), var(--primary-red));
      border-radius: 4px;
      transition: width 0.3s ease;
  }

  .quiz-actions {
      display: flex;
      gap: 0.75rem;
  }

  .quiz-start-btn {
      flex: 1;
      background: linear-gradient(135deg, var(--primary-red), var(--primary-red-hover));
      color: var(--white);
      border: none;
      padding: 0.875rem 1.5rem;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: 0 2px 8px rgba(225, 30, 45, 0.3);
  }

  .quiz-start-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(225, 30, 45, 0.4);
  }

  .quiz-preview-btn {
      background-color: var(--white);
      color: var(--secondary-blue);
      border: 2px solid var(--secondary-blue);
      padding: 0.875rem 1.5rem;
      border-radius: 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
  }

  .quiz-preview-btn:hover {
      background-color: var(--secondary-blue);
      color: var(--white);
      transform: translateY(-2px);
  }

  .btn-icon {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
  }

  .quiz-footer {
      padding: 1rem 1.5rem;
      background-color: var(--gray-50);
      border-top: 1px solid var(--gray-100);
  }

  .quiz-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.75rem;
      color: var(--gray-500);
  }

  .quiz-attempts {
      font-weight: 500;
  }

  .rating-stars {
      display: flex;
      gap: 0.125rem;
  }

  .star {
      width: 12px;
      height: 12px;
      color: var(--gray-300);
      transition: color 0.2s ease;
  }

  .star.filled {
      color: var(--warning);
  }

  .no-rating {
      font-style: italic;
      color: var(--gray-400);
  }

  /* Quiz Reviews Link */
  .quiz-reviews-link {
      margin-bottom: 1.5rem;
      text-align: center;
  }

  .reviews-link {
      color: var(--secondary-blue);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
      border: 1px solid transparent;
  }

  .reviews-link:hover {
      background-color: var(--gray-50);
      color: var(--primary-red);
      border-color: var(--gray-200);
      text-decoration: none;
  }

  .reviews-link i {
      font-size: 0.75rem;
      color: var(--warning);
  }

  /* User Review Section */
  .quiz-review-section {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background-color: var(--gray-50);
      border-radius: 0.75rem;
      border: 1px solid var(--gray-100);
  }

  .user-review {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
  }

  .review-icon {
      flex-shrink: 0;
      width: 32px;
      height: 32px;
      background-color: var(--secondary-blue);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
  }

  .review-svg {
      width: 16px;
      height: 16px;
      color: var(--white);
  }

  .review-content {
      flex: 1;
  }

  .review-text {
      font-size: 0.875rem;
      color: var(--gray-700);
      line-height: 1.5;
      font-style: italic;
  }

  /* Reviews Modal */
  .reviews-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
  }

  .reviews-modal-overlay.active {
      opacity: 1;
      visibility: visible;
  }

  .reviews-modal {
      background: var(--white);
      border-radius: 1rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transform: scale(0.9) translateY(20px);
      transition: all 0.3s ease;
  }

  .reviews-modal-overlay.active .reviews-modal {
      transform: scale(1) translateY(0);
  }

  .reviews-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid var(--gray-200);
      background: linear-gradient(135deg, var(--secondary-blue), var(--primary-red));
      color: var(--white);
  }

  .reviews-modal-header h3 {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 700;
  }

  .reviews-modal-close {
      background: none;
      border: none;
      color: var(--white);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 0.5rem;
      transition: background-color 0.2s ease;
  }

  .reviews-modal-close:hover {
      background: rgba(255, 255, 255, 0.2);
  }

  .reviews-modal-content {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
      max-height: 60vh;
  }

  .reviews-loading {
      text-align: center;
      padding: 2rem;
      color: var(--gray-600);
  }

  .reviews-loading i {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--secondary-blue);
  }

  .reviews-summary {
      background: var(--gray-50);
      padding: 1.5rem;
      border-radius: 0.75rem;
      margin-bottom: 1.5rem;
      border: 1px solid var(--gray-200);
  }

  .summary-rating {
      display: flex;
      align-items: center;
      gap: 1rem;
  }

  .rating-score {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-red);
  }

  .rating-count {
      color: var(--gray-600);
      font-size: 0.875rem;
  }

  .reviews-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
  }

  .review-item {
      background: var(--gray-50);
      padding: 1.5rem;
      border-radius: 0.75rem;
      border: 1px solid var(--gray-200);
  }

  .review-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
  }

  .reviewer-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
  }

  .reviewer-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-weight: 600;
      font-size: 0.875rem;
  }

  .reviewer-details {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
  }

  .reviewer-name {
      font-weight: 600;
      color: var(--gray-900);
      font-size: 0.875rem;
  }

  .review-rating {
      display: flex;
      gap: 0.125rem;
  }

  .review-rating i {
      font-size: 0.75rem;
      color: var(--gray-300);
  }

  .review-rating i.filled {
      color: var(--warning);
  }

  .review-date {
      color: var(--gray-500);
      font-size: 0.75rem;
  }

  .review-text {
      color: var(--gray-700);
      line-height: 1.6;
      font-size: 0.875rem;
  }

  .no-reviews {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--gray-500);
  }

  .no-reviews i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: var(--gray-400);
  }

  .reviews-error {
      text-align: center;
      padding: 2rem;
      color: var(--error-red);
  }

  .reviews-error i {
      font-size: 2rem;
      margin-bottom: 1rem;
  }


  .rating-display {
      display: flex;
      align-items: center;
      gap: 0.75rem;
  }

  .rating-stars {
      display: flex;
      gap: 0.125rem;
  }

  .rating-stars .star {
      width: 16px;
      height: 16px;
      color: var(--gray-300);
      transition: color 0.2s ease;
  }

  .rating-stars .filled {
      color: var(--warning);
  }

  .rating-score {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--gray-900);
  }

  .rating-count {
      font-size: 0.75rem;
      color: var(--gray-500);
  }

  /* Sidebar Overlay for Mobile - Now outside main-container */
  .sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0; /* Full screen since outside main-container */
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 1999; /* BELOW sidebar (2000) but ABOVE everything else */
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      pointer-events: auto; /* Always allow clicks to close */
  }

  .sidebar-overlay.active {
      opacity: 1;
      visibility: visible;
  }

  /* CRITICAL FIX: On mobile, overlay should NOT cover sidebar area */
  @media (max-width: 768px) {
      .sidebar-overlay {
          left: 240px; /* Sidebar width - don't block sidebar */
      }
  }

  /* Hide overlay on desktop */
  @media (min-width: 1024px) {
      .sidebar-overlay {
          display: none !important;
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
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
  }

  .mobile-header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      max-width: 100%;
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
      -webkit-tap-highlight-color: transparent;
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

  /* Mobile Layout Reset - Fix left gap issue */
  @media (max-width: 768px) {
      .main-content {
          width: 100vw !important;
          max-width: 100vw !important;
          margin-left: 0 !important;
      }

      .youtube-sidebar {
          position: fixed;
          left: 0;
          top: 0;
          height: 100vh;
          z-index: 2000; /* Much higher than overlay */
          transform: translateX(-100%);
          transition: transform 0.3s ease;
      }

      .youtube-sidebar.mobile-open {
          transform: translateX(0);
      }
  }

  /* Mobile Responsive */
  @media (max-width: 768px) {
      * {
            -webkit-tap-highlight-color: transparent;
        }
        body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: #f9fafb;
            font-size: 16px; /* Prevents iOS zoom on input focus */
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* Enable smooth scrolling */
            scroll-behavior: smooth;
            
        }

        button, a, input {
            min-height: 44px; /* Apple's recommended touch target */
            min-width: 44px;
        }

      .youtube-sidebar {
          transform: translateX(-100%);
          transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          width: 280px;
          z-index: 2000; /* Keep consistent high z-index */
      }

      .youtube-sidebar.mobile-open {
          transform: translateX(0);
      }

      .main-content {
          margin-left: 0;
          width: 100vw;
          max-width: 100vw;
          overflow-x: hidden;
      }

      .youtube-sidebar.collapsed ~ .main-content {
          margin-left: 0;
          width: 100vw;
          max-width: 100vw;
      }

      .top-header {
          display: none;
      }

      .mobile-header {
          display: block;
          z-index: 1001;
      }

      .filter-bar {
          flex-direction: column;
          gap: 12px;
          padding: 12px;
          position: relative;
          overflow-x: hidden;
          width: 100%;
          max-width: 100%;
      }

      .search-box {
          min-width: 100%;
          width: 100%;
          max-width: 100%;
      }

      .content-section {
          padding: 1rem 0.75rem;
          overflow-x: hidden;
          width: 100%;
          max-width: 100%;
      }

      .content-grid {
          grid-template-columns: 1fr;
          gap: 1rem;
          padding: 0;
          width: 100%;
          max-width: 100%;
      }

      .hero-section {
          height: 160px;
          width: 100%;
          max-width: 100%;
      }

      .hero-overlay {
          padding: 0 1rem;
          width: 100%;
          max-width: 100%;
      }

      .hero-content h1 {
          font-size: 1.75rem;
          margin-bottom: 0.5rem;
      }
      
      .hero-content p {
          font-size: 1rem;
          margin-bottom: 1rem;
      }

      .hero-view-button {
          padding: 0.625rem 1.25rem;
          font-size: 0.875rem;
      }

      .quiz-card {
          border-radius: 0.75rem;
          width: 100%;
          max-width: 100%;
          margin: 0;
      }

      .quiz-header {
          padding: 1rem;
          min-height: 80px;
      }

      .quiz-icon-container {
          padding: 0.75rem;
      }

      .quiz-main-icon {
          width: 32px;
          height: 32px;
      }

      .quiz-level-badge {
          top: 0.75rem;
          right: 0.75rem;
          padding: 0.375rem 0.75rem;
          font-size: 0.6875rem;
      }

      .quiz-difficulty {
          bottom: 0.75rem;
          left: 0.75rem;
          padding: 0.375rem 0.625rem;
          font-size: 0.6875rem;
      }

      .quiz-content {
          padding: 1rem;
      }

      .quiz-title {
          font-size: 1.125rem;
          margin-bottom: 0.5rem;
      }

      .quiz-description {
          font-size: 0.8125rem;
          margin-bottom: 1rem;
      }

      .quiz-stats {
          flex-direction: column;
          gap: 0.5rem;
          padding: 0.75rem;
          margin-bottom: 1rem;
      }

      .stat-item {
          font-size: 0.8125rem;
      }

      .quiz-progress {
          padding: 0.75rem;
          margin-bottom: 1rem;
      }

      .quiz-actions {
          flex-direction: column;
          gap: 0.5rem;
      }

      .quiz-start-btn,
      .quiz-preview-btn {
          padding: 0.75rem 1rem;
          font-size: 0.8125rem;
      }

      .quiz-footer {
          padding: 0.75rem 1rem;
      }

      .quiz-meta {
          font-size: 0.6875rem;
          flex-direction: column;
          gap: 0.25rem;
          align-items: flex-start;
      }

      /* Ensure sidebar shows above everything on mobile */
      .sidebar-overlay {
          display: block;
      }

      /* Add header to sidebar on mobile */
      .youtube-sidebar .sidebar-header {
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 1rem;
          border-bottom: 1px solid var(--gray-200);
      }

      .youtube-sidebar .sidebar-logo {
          opacity: 1;
          pointer-events: all;
      }

      .youtube-sidebar .sidebar-toggle-btn {
          display: none;
      }
  }

  @media (min-width: 1024px) {
      .content-grid {
          grid-template-columns: repeat(3, 1fr);
      }
  }

  @media (min-width: 1400px) {
      .content-grid {
          grid-template-columns: repeat(4, 1fr);
      }
  }
</style>
