<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DigiLearn - {{ config('app.name', 'ShoutOutGh') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vimeo Player API for hover-to-play functionality -->
    <script src="https://player.vimeo.com/api/player.js"></script>

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
            overflow: hidden;
        }

        /* Main Layout Container */
        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* YouTube-style Sidebar */
        .youtube-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width-expanded) !important;
            height: 100vh !important; /* Does not grow with content */
            max-height: 100vh !important; /* Enforce maximum height */
            background-color: var(--white);
            border-right: 1px solid var(--gray-200);
            z-index: 2000; /* Much higher than overlay */
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow-y: scroll;
            display: flex;
            flex-direction: column;
        }

        .youtube-sidebar.collapsed {
            width: var(--sidebar-width-collapsed) !important;
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
            flex: 1; /* takes all available space */
            overflow-y: auto !important; /* force scrollbar */
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin; /* For Firefox */
            scrollbar-color: var(--gray-400) transparent; /* For Firefox */
            scroll-behavior: smooth;
            max-height: calc(100vh - 64px) !important;
        }

        /* Custom scrollbar for sidebar content */
        .sidebar-content::-webkit-scrollbar {
            width: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-content:hover::-webkit-scrollbar {
            opacity: 1;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: transparent;
            margin: 8px 0;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 4px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        /* Ensure sidebar sections distribute space properly */
        .sidebar-section {
            margin-bottom: 0.75rem;
            flex-shrink: 0; /* Prevents sections from shrinking */
        }

        /* Account section should stick to the bottom */
        .sidebar-section:last-of-type {
            position: relative;
            margin-top: 0; /* Pushes account section to the bottom */
            margin-bottom: 1rem; /* Add some bottom spacing */
            padding-top: 0.75rem; /* Add subtle separation */
            border-top: 1px solid var(--gray-100); /* Visual Separator */
        }

        .sidebar-section:nth-last-of-type(2)::after {
            content: '';
            display: block;
            flex-grow: 1;
            min-height: 1rem; /* Minimum spacing */
            max-height: 3rem; /* Maximim spacing to prevent excessive gap */
        }

        .sidebar-section:last-child {
            padding-bottom: 0.75rem;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.2;
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

        .youtube-sidebar.collapsed .sidebar-section {
            margin-bottom: 0.5rem;
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
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
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
            width: calc(100vw - var(--sidebar-width-expanded)) !important;
            max-width: calc(100vw - var(--sidebar-width-expanded)) !important;
            margin-left: var(--sidebar-width-expanded) !important;
            margin-top: 201.4px !important; /*Account for both headers: 60px header + 56px filter bar */
            /* padding-top: 1rem !important; Internal padding */
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            overflow-x: hidden;
            overflow-y: auto;
            height: calc(100vh - 201.4px) !important; /* Full height minus headers */
        }

        .youtube-sidebar.collapsed ~ .main-content {
            width: calc(100vw - var(--sidebar-width-collapsed)) !important;
            max-width: calc(100vw - var(--sidebar-width-collapsed)) !important;
            margin-left: var(--sidebar-width-collapsed) !important;
        }

        .youtube-sidebar,
        .main-content {
            will-change: transform, width, margin-left;
        }

        /* Top Header */
        /* Updated top header to span full width and have enhanced glassmorphism */
        .top-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important; /* Changed from sidebar-width to 0 for full width */
            width: 100vw !important; /* Changed to full viewport width */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            padding-right: 1rem;
            padding-left: var(--sidebar-width-expanded);
            background-color: rgba(255, 255, 255, 0.8); /* More transparent for glassmorphism */
            backdrop-filter: blur(12px) saturate(180%); /* Enhanced blur effect */
            -webkit-backdrop-filter: blur(12px) saturate(180%); /* Safari support */
            border-bottom: 1px solid rgba(229, 231, 235, 0.6);
            z-index: 999 !important;
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 60px;
        }

        .youtube-sidebar.collapsed ~ .top-header {
            padding-left: var(--sidebar-width-collapsed) !important;
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

        .notification-dropdown {
            position: relative;
        }

        .notification-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            width: 380px;
            max-width: 90vw;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .notification-dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .notification-dropdown-menu .dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .notification-dropdown-menu .dropdown-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: var(--primary-red);
            font-size: 0.875rem;
            cursor: pointer;
            font-weight: 500;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            transition: background-color 0.2s ease;
        }

        .notification-item:hover {
            background: var(--gray-50);
        }

        .notification-item.unread {
            background: rgba(59, 130, 246, 0.05);
        }

        .notification-item.unread:hover {
            background: rgba(59, 130, 246, 0.08);
        }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            margin: 0 0 0.25rem 0;
            font-size: 0.875rem;
            color: var(--gray-800);
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .notification-icon {
            width: 20px;
            height: 20px;
            stroke-width: 2;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--gray-600);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: var(--primary-red);
            color: var(--white);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--white);
        }

        .dropdown-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }

        .view-all {
            color: var(--primary-red);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .view-all:hover {
            text-decoration: underline;
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
        /* Updated filter bar to span full width with glassmorphism and proper positioning */
        .filter-bar {
            position: fixed !important;
            top: 60px !important; /* Directly below the header */
            left: 0; /* Start from left edge for full width */
            width: 100vw; /* Full viewport width */
            padding-left: calc(var(--sidebar-width-expanded) + 0.75rem); /* Account for sidebar */
            padding-right: 0.75rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            z-index: 998 !important;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: rgba(255, 255, 255, 0.75); /* Transparent for glassmorphism */
            backdrop-filter: blur(10px) saturate(160%); /* Glassmorphism effect */
            -webkit-backdrop-filter: blur(10px) saturate(160%); /* Safari support */
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
            overflow-x: hidden;
            overflow-y: hidden;
            max-width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none; /* Allow clicks to pass through glassmorphism layer */
        }

        .filter-bar * {
            pointer-events: auto; /* Restore pointer events for interactive elements */
        }

        .youtube-sidebar.collapsed ~ .filter-bar {
            padding-left: calc(var(--sidebar-width-collapsed) + 0.75rem);
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
            display: flex;
            transition: all 0.3s ease;
        }

        .search-box.searching,
        #mobileSearchBox.searching {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 2px rgba(225, 30, 45, 0.2);
        }

        .search-box.searching::after,
        #mobileSearchBox.searching::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-red), var(--secondary-blue));
            animation: searchProgress 1.5s ease-in-out infinite;
        }

        @keyframes searchProgress {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(0%); }
            100% { transform: translateX(100%); }
        }

        .search-close {
            display: flex;
            position: absolute;
            right: 45px; /* Position to the left of search button */
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--gray-500);
        }

        .search-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
            padding-right: 3.5rem;
            background: var(--white);
            color: var(--gray-900);
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
            width: calc(2.5rem - 1px);
            background-color: var(--secondary-blue);
            border: none;
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 20; /* Ensure it's above other elements */
            pointer-events: auto; /* Ensure it receives click events */
        }

        .search-button:hover {
            background-color: var(--secondary-blue-hover);
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
            color: var(--grey-600);
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
            color: var(--grey-600);
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
            max-height: 60vh; /* Limit height for long dropdowns */
            overflow-y: auto;
        }

        /* Horizontal Subjects Filter */
        .subjects-filter-container {
            position: fixed !important;
            left: 0 !important;
            top: 116px !important;
            width: 100vw !important;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding-left: var(--sidebar-width-expanded);
            padding-right: 1rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            overflow: hidden; /* Hide overflow on container */
            box-sizing: border-box; /* Ensure padding is included in width */
            z-index: 997 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .youtube-sidebar.collapsed ~ .subjects-filter-container {
            padding-left: calc(var(--sidebar-width-collapsed) + 0.75rem);
        }

        .subjects-filter {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 0.5rem 0;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
            scroll-behavior: smooth;
            width: 100%;
        }

        .subjects-filter::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        .subject-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .subject-chip:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(38, 119, 184, 0.05);
            color: var(--secondary-blue);
        }

        .subject-chip.active {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
            color: var(--white);
        }

        .subject-chip.active:hover {
            background-color: var(--secondary-blue-hover);
            border-color: var(--secondary-blue-hover);
        }

        .custom-dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            z-index: 1100; /* Ensure dropdown is above other content */
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
        }

        .filter-button.question {
            border: 2px solid var(--secondary-blue);
            background-color: var(--white);
            color: var(--grey-600);
        }

        .filter-button.question:hover {
            background-color: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
        }

        .filter-button.quiz {
            border: 2px solid var(--primary-red);
            color: var(--grey-600);
            text-decoration: none;
        }

        .filter-button.quiz:hover {
            background-color: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
        }

        .filter-button:hover {
            opacity: 0.9;
        }

        .level-container {
            /* Left on desktop */
        }

        .search-container {
            flex: 1;
            min-width: 200px;
        }

        .quiz-container {
            /* Right on desktop */
        }

        .level-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: var(--secondary-blue);
            color: var(--white);
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            white-space: nowrap;
        }

        .level-indicator:hover {
            background-color: var(--secondary-blue-hover);
            transform: scale(1.05);
        }

        .level-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        #level-modal-toggle:checked ~ .level-modal {
            opacity: 1;
            visibility: visible;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .level-modal .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
        }

        .modal-content {
            position: relative;
            background: var(--white);
            border-radius: 0.5rem;
            padding: 1.5rem;
            max-width: 300px;
            width: 90%;
            box-shadow: var(--shadow-xl);
        }

        .level-modal .modal-content {
            pointer-events: auto;
        }

        .modal-content h3 {
            margin: 0 0 1rem 0;
            font-size: 1.125rem;
            color: var(--gray-900);
        }

        .level-option {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease;
        }

        .level-option:hover {
            background-color: var(--gray-50);
        }

        .level-option + .level-option {
            margin-top: 0.5rem;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 300px;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .hero-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            flex: 1;
            min-height: calc(100vh - 172px - 300px); /* viewport - fixed elements - hero */
            padding: 2rem 1rem 3rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            min-height: 400px;
            padding-bottom: 2rem;
        }

        .lesson-card {
            display: flex;
            flex-direction: column;
            background-color: var(--white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .lesson-thumbnail {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            flex: 1;
        }

        .lesson-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .lesson-iframe {
            width: 100%;
            height: 100%;
            border: none;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            pointer-events: none;
        }

        .lesson-fallback-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .video-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .lesson-duration {
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

        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.3);
            opacity: 1;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .lesson-card.playing .play-overlay {
            opacity: 0;
        }

        .lesson-card:not(.playing):hover .play-overlay {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .play-button {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-red);
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .lesson-card:hover .play-button {
            background-color: var(--primary-red);
            color: var(--white);
            transform: scale(1.1);
        }

        /* Update the lesson card hover effect to show level badge more prominently */
        .lesson-card:hover .lesson-level-badge {
            background-color: var(--primary-red);
            transform: scale(1.05);
            transition: all 0.2s ease;
        }

        .lesson-level-badge {
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

        .video-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .lesson-card.video-error .lesson-video {
            display: none;
        }

        .lesson-card.video-error .lesson-fallback-image {
            display: block;
        }

        .lesson-info {
            flex: 1;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
        }

        .lesson-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lesson-meta {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: baseline;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .lesson-subject {
            color: var(--secondary-blue);
            font-weight: 500;
        }

        /* Course-specific styles */
        .course-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0.5rem 0;
            line-height: 1.4;
        }

        .course-lessons-count {
            font-size: 0.75rem;
            color: var(--secondary-blue);
            font-weight: 500;
            margin: 0.25rem 0;
        }

        .lesson-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .lesson-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
            justify-content: center;
        }

        .lesson-action-btn.primary {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .lesson-action-btn.primary:hover {
            background-color: var(--secondary-blue-hover);
        }

        .lesson-action-btn.secondary {
            background-color: var(--white);
            color: var(--gray-900);
            border: 1px solid var(--primary-red);
        }

        .lesson-action-btn.secondary:hover {
            background-color: #fceaed;
            color: var(--primary-red);
        }

        .mobile-search-toggle {
            display: flex; /* Shown by default, hidden on desktop */
            background: var(--secondary-blue);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--white);
        }

        .mobile-search-toggle:hover {
            background-color: var(--secondary-blue-hover);
        }

        /* Mobile Layout Reset - Fix left gap issue */
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
            .main-content {
                width: 100vw !important;
                max-width: 100vw !important;
                margin-left: 0 !important;
            }

            .filter-bar {
                left: 0 !important;
                width: 100vw !important;
            }

            .subjects-filter-container {
                left: 0 !important;
                width: 100vw !important;
                padding: 16px 0;
            }

            .youtube-sidebar {
                width: 280px; /* Full width on mobile */
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            /* Larger scrollbar on mobile for better touch */
            .sidebar-content::-webkit-scrollbar {
                width: 6px;
            }

            /* Ensure mobile sidebar does not overflow viewport */
            .sidebar-content {
                padding-bottom: env(safe-area-inset-bottom, 1rem); /* Account for mobile notches */
            }
        }

        @media (max-height: 600px) {
            .sidebar-content {
                padding: 0.5rem 0;
            }

            .sidebar-menu-item {
                padding: 0.5rem 1.25rem;
                min-height: 40px;
            }

            .youtube-sidebar.collapsed .sidebar-menu-item {
                padding: 0.625rem;
            }

            .sidebar-section {
                margin-bottom: 0.5rem;
            }

            .sidebar-section-title {
                padding: 0.375rem 1.5rem;
                margin-bottom: 0.25rem;
                font-size: 0.6875rem;
            }

            .sidebar-menu-icon {
                width: 18px;
                height: 18px;
            }

            /* Minimal spacing on short screens */
            .sidebar-section:nth-last-of-type(2)::after {
                min-height: 0.5rem;
                max-height: 1.5rem;
            }

            .sidebar-section:last-of-type {
                padding-top: 0.5rem;
            }
        }

        /* Ultra-small screens (foldable phones, small heights) */
        @media (max-height: 500px) {
            .sidebar-menu-item {
                padding: 0.375rem 1rem;
                min-height: 36px; /* Minimum touch target */
            }

            .sidebar-menu-icon {
                width: 18px;
                height: 18px;
            }

            .sidebar-menu-text {
                font-size: 0.8125rem;
            }

            .sidebar-section-title {
                font-size: 0.6875rem;
                padding: 0.25rem 1.25rem;
            }

            /* Remove spacer on ultra-short screens */
            .sidebar-section:nth-last-of-type(2)::after {
                display: none;
            }

            .sidebar-section:last-of-type {
                border-top: none;
                padding-top: 0.25rem;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-bar {
                justify-content: space-between;
            }

            .level-container {
                display: flex;
                order: 1;
            }

            .mobile-search-toggle {
                display: flex;
                order: 2;
            }

            .quiz-container {
                order: 3;
            }

            .search-container {
                display: none;
            }

            #filterBar.search-active .search-container {
                display: flex;
                flex: 1;
                min-height: 48px;
                background: var(--white);
                padding: 0.5rem;
                box-shadow: var(--shadow-md);
            }

            #filterBar.search-active .search-box {
                background: var(--gray-100);
                border-radius: 0.375rem;
                padding: 0.25rem;
                width: 100%;
            }

            #filterBar.search-active .level-container,
            #filterBar.search-active .mobile-search-toggle,
            #filterBar.search-active .quiz-container {
                display: none;
            }

            .search-close {
                display: flex;
            }
            .main-container {
                flex-direction: column;
                overflow-y: auto;
                overflow-x: hidden;
                width: 100vw;
            }
            .youtube-sidebar {
                height: 100vh;
                height: 100dvh; /* Dynamic viewport height for mobile browsers */
                transform: translateX(-100%);
                transition: transform 0.3s ease, width 0.3s ease;
                width: 280px; /* Adjusted width for mobile */
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100vw;
                max-width: 100vw;
            }

            .youtube-sidebar.collapsed ~ .main-content {
                margin-left: 0;
                width: 100vw;
                max-width: 100vw;
            }

            /* Show mobile header */
            .mobile-header {
                display: flex;
                padding: 10px 15px;
            }

            /* Filter bar layout */
            .filter-bar {
                gap: 12px;
                padding: 16px;
                position: relative;
                overflow: hidden;
            }

            /* Dropdowns row */
            .dropdowns-row {
                display: flex;
                gap: 12px;
                width: 100%;
                flex-wrap: wrap;
            }

            .dropdowns-row .custom-dropdown {
                flex: 1;
                min-width: auto;
            }

            /* Hide sidebar toggle in sidebar on mobile */
            .sidebar-toggle-btn {
                display: flex;
            }
            .sidebar-logo {
                justify-content: center;
            }

            /* Search box initially hidden on mobile */
            
            .content-section {
                min-height: calc(100vh - 60px -120px - 200px);
                padding: 1rem 0.75rem 2rem;
            }

            /* Adjust hero section for mobile */
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

            .content-section {
                min-height: calc(100vh - 172px - 200px);
                padding: 1rem 0.75rem 2rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding-bottom: 1.5rem;
            }

            /* Mobile overlay - Now outside main-container */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.6);
                z-index: 1999; /* BELOW sidebar (2000) but ABOVE content */
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                pointer-events: auto;
            }

            /* Ensure sidebar sections are well-spaced on mobile */
            .sidebar-section {
                margin-bottom: 0.75rem;
            }

            .sidebar-section:nth-last-of-type(2)::after {
                max-height: 2.5rem;
            }

            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            /* Prevent body scrolling when sidebar is open */
            .body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-logo img {
                height: 28px;
            }

            .custom-dropdown {
                min-width: calc(50% - 0.25rem);
            }

            /* Buttons row */
            .filter-buttons {
                display: flex;
                gap: 12px;
                width: 100%;
            }

            .filter-button {
                min-width: calc(50% - 6px); /* Two columns with gap */
                text-align: center;
                padding: 0.75rem;
                font-size: 0.8rem;
            }

            /* Make all filter elements flexible */
            .search-box,
            .custom-dropdown,
            .filter-button {
                flex: 1 1 auto;
                min-width: calc(50% - 6px); /* Two columns with gap */
            }

            .hero-overlay {
                padding: 0 20px;
                flex-direction: column;
                justify-content: center;
                text-align: center;
            }

            .lesson-card {
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
        }

        /* CRITICAL FIX: On mobile, overlay should NOT cover sidebar area */
        @media (max-width: 768px) {
            .sidebar-overlay {
                left: var(--sidebar-width-expanded); /* 280px - don't block sidebar */
            }
        }

        /* Hide overlay on desktop */
        @media (min-width: 1024px) {
            .sidebar-overlay {
                display: none !important;
            }
        }

        @media (min-width: 640px) {
            /* Two column layout for small tablets */
            .content-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dropdowns-row .custom-dropdown {
                flex: 1 1 calc(33.333% - 0.5rem);
            }
        }

        @media (min-width: 768px) {
            /* Show desktop layout on tablets and above */
            .filter-bar {
                gap: 0.75rem;
                /* padding: 0.75rem; */
                overflow-x: visible;
            }

            .mobile-search-toggle {
                display: none;
            }

            .level-container {
                display: flex;
            }

            .search-container {
                display: flex;
            }

            .search-box {
                position: relative;
                visibility: visible;
                transform: none;
                padding: 0;
                box-shadow: none;
            }

            .dropdowns-row {
                flex-wrap: nowrap;
            }

            .dropdowns-row .custom-dropdown {
                flex: 0 0 auto;
                min-width: 120px;
            }

            .content-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .notification-dropdown-menu {
                right: 0;
                width: 380px;
            }
        }

        @media (min-width: 1024px) {
            .content-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }

        @media (min-width: 1280px) {
            /* Four column layout for large screens */
            .content-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 1400px) and (min-height: 800px) {
            .sidebar-content {
                padding: 1.5rem 0;
            }

            .sidebar-menu-item {
                padding: 0.875rem 2rem;
            }

            .sidebar-menu-text {
                font-size: 0.9375rem;
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
            color: var(--gray-900); /* Ensure text color contrast */
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

        .mobile-header-content {
            gap: 10px;
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

        /* Mobile hamburger styling */
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
            height: 24px;
        }

        .mobile-logo .sidebar-brand {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-red);
        }

        /* Adjust user avatar for mobile */
        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        /* Notification icon sizing for mobile */
        .notification-btn {
            padding: 0.5rem;
        }

        /* Better tap targets for mobile */
        .sidebar-menu-item, .dropdown-option, .filter-button {
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        @media (min-height: 700px) and (max-height: 900px) {
            .sidebar-content {
                padding: 0.75rem 0;
            }

            .sidebar-section {
                margin-bottom: 0.5rem;
            }

            .sidebar-section-title {
                padding: 0.375rem 1.5rem;
                margin-bottom: 0.375rem;
                font-size: 0.7rem;
            }

            .sidebar-menu-item {
                padding: 0.625rem 1.5rem;
            }

            /* Reduce max spacing between sections */
            .sidebar-section:nth-last-of-type(2)::after {
                max-height: 2rem;
            }
        }

        /* Tall screens - allow more breathing room */
        @media (min-height: 900px) {
            .sidebar-section {
                margin-bottom: 1rem;
            }
            
            .sidebar-section-title {
                padding: 0.5rem 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .sidebar-menu-item {
                padding: 0.875rem 1.5rem;
            }
            
            /* Allow slightly more space on tall screens */
            .sidebar-section:nth-last-of-type(2)::after {
                max-height: 4rem;
            }
        }

        /* Fix for Safari mobile to prevent overscroll */
        .sidebar-content {
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile - Moved outside main-container -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-container">
        @include('components.dashboard-sidebar')
        @include('components.dashboard-header')
            
        <!-- Search/Filter Bar -->
        <div class="filter-bar" id="filterBar">
            <div class="level-container">
                <label for="level-modal-toggle" class="level-indicator">Grade: {{ $selectedLevelGroup ? ([
                    'primary-lower' => 'Grade 1-3',
                    'primary-upper' => 'Grade 4-6',
                    'jhs' => 'Grade 7-9',
                    'shs' => 'Grade 10-12',
                    'university' => 'University'
                ][$selectedLevelGroup] ?? ucwords(str_replace('-', ' ', $selectedLevelGroup))) : 'Grade 1-3' }}</label>
            </div>

            <!-- Mobile Search Toggle Button -->
            <button class="mobile-search-toggle" id="mobileSearchToggle">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.5 17.5L12.5001 12.5M14.1667 8.33333C14.1667 11.555 11.555 14.1667 8.33333 14.1667C5.11167 14.1667 2.5 11.555 2.5 8.33333C2.5 5.11167 5.11167 2.5 8.33333 2.5C11.555 2.5 14.1667 5.11167 14.1667 8.33333Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="search-container">
                <div class="search-box" id="mobileSearchBox">
                    <input type="text" class="search-input" placeholder="Search">
                    <button type="button" class="search-button">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    <!-- Close button for mobile search -->
                    <button class="search-close" id="searchClose">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="quiz-container">
                <a href="{{ route('quiz.index') }}" class="filter-button quiz">Quiz</a>
            </div>
        </div>

        <!-- Sepaarate Horizontal Subjects Filter -->
        <div class="subjects-filter-container">
            <div class="subjects-filter">
                <div class="subject-chip active" data-subject="all">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    All Subjects
                </div>

                <div class="subject-chip" data-subject="mathematics">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>

                    Mathematics
                </div>
                <div class="subject-chip" data-subject="science">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Science
                </div>
                <div class="subject-chip" data-subject="english">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    English
                </div>
                <div class="subject-chip" data-subject="social-studies">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Social Studies
                </div>
                <div class="subject-chip" data-subject="history">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    History
                </div>
                <div class="subject-chip" data-subject="chemistry">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Chemistry
                </div>
                <div class="subject-chip" data-subject="physics">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Physics
                </div>
                <div class="subject-chip" data-subject="biology">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Biology
                </div>
                <div class="subject-chip" data-subject="geography">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Geography
                </div>
                <div class="subject-chip" data-subject="art">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4 4 4 0 004-4V5z"/>
                    </svg>
                    Art
                </div>
                <div class="subject-chip" data-subject="music">
                    <svg class="subject-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    Music
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Hero Section with Video Background -->
            <div class="hero-section">
                <div class="hero-background">
                    <video autoplay muted loop playsinline>
                        <source src="{{ secure_asset('videos/hero-video.mp4') }}" type="video/mp4">
                    </video>
                </div>
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Explore & Learn</h1>
                        <p>at your own pace.</p>
                    </div>
                    <button class="hero-view-button">View</button>
                </div>
            </div>
            
            <!-- Content Section with Lessons/Courses Grid -->
            <div class="content-section">
                <div class="content-grid">
                    @if(isset($universityCourses))
                        {{-- Display University Courses --}}
                        @forelse($universityCourses as $course)
                        <div class="lesson-card hover-video-card" data-lesson-id="{{ \App\Services\UrlObfuscator::encode($course['id']) }}" data-video-id="{{ $course['id'] }}" data-subject="{{ $course['subject'] }}" data-title="{{ $course['title'] }}" data-video-source="{{ $course['video_source'] ?? 'local' }}" data-vimeo-id="{{ $course['vimeo_id'] ?? '' }}" data-external-video-id="{{ $course['external_video_id'] ?? '' }}" data-mux-playback-id="{{ $course['mux_playback_id'] ?? '' }}" data-loaded="false">
                            <div class="lesson-thumbnail">
                                <img
                                    src="{{ secure_asset($course['thumbnail']) }}"
                                    alt="{{ $course['title'] }}"
                                    class="video-thumb"
                                    loading="lazy"
                                    onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Course+Video'"
                                />

                                <div class="video-preview"></div>

                                <div class="lesson-duration">{{ $course['duration'] }}</div>

                                <!-- Level badge -->
                                <div class="lesson-level-badge">{{ $course['level_display'] ?? 'Course' }}</div>

                                <!-- Play overlay that appears on hover -->
                                <div class="play-overlay">
                                    <div class="play-button">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="lesson-info">
                                <h3 class="lesson-title">{{ $course['title'] }}</h3>
                                <div class="lesson-meta">
                                    <span class="lesson-subject">({{ $course['subject'] }})</span>
                                    <span>{{ $course['instructor'] }} | {{ $course['year'] }}</span>
                                </div>
                                @if(isset($course['description']))
                                <p class="course-description" style="font-size: 0.875rem; color: var(--gray-600); margin: 0.5rem 0;">
                                    {{ $course['description'] }}
                                </p>
                                @endif
                                @if(isset($course['lessons_count']))
                                <p class="course-lessons-count" style="font-size: 0.75rem; color: var(--secondary-blue); font-weight: 500;">
                                    {{ $course['lessons_count'] }} lessons • {{ $course['credit_hours'] ?? 3 }} credit hours
                                </p>
                                @endif
                                <div class="lesson-actions">
                                    <a href="{{ route('dashboard.lesson.view', ['lessonId' => App\Services\UrlObfuscator::encode($course['id']), 'course_id' => App\Services\UrlObfuscator::encode($course['id'])]) }}" class="lesson-action-btn primary">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        Start Course
                                    </a>
                                    <a href="{{ $lesson['quiz_id'] ? route('quiz.instructions', ['quizId' => $lesson['encoded_quiz_id']]) : route('quiz.index') }}" class="lesson-action-btn secondary">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Quiz
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No courses available</h3>
                            <p style="color: var(--gray-500);">University courses are coming soon!</p>
                        </div>
                        @endforelse
                    @else
                        {{-- Display Regular Lessons --}}
                        @forelse($lessons ?? [] as $lesson)
                    <div class="lesson-card hover-video-card" data-lesson-id="{{ \App\Services\UrlObfuscator::encode($lesson['id']) }}" data-video-id="{{ $lesson['id'] }}" data-subject="{{ $lesson['subject'] }}" data-title="{{ $lesson['title'] }}" data-video-source="{{ $lesson['video_source'] ?? 'local' }}" data-vimeo-id="{{ $lesson['vimeo_id'] ?? '' }}" data-external-video-id="{{ $lesson['external_video_id'] ?? '' }}" data-mux-playback-id="{{ $lesson['mux_playback_id'] ?? '' }}" data-loaded="false">
                        <div class="lesson-thumbnail">
                            <img
                                src="{{ secure_asset($lesson['thumbnail']) }}"
                                alt="{{ $lesson['title'] }}"
                                class="video-thumb"
                                loading="lazy"
                                onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Video+Lesson'"
                            />

                            <div class="video-preview"></div>

                            <div class="lesson-duration">{{ $lesson['duration'] }}</div>

                            <!-- Level badge -->
                            <div class="lesson-level-badge">{{ $lesson['level_display'] ?? 'Level' }}</div>

                            <!-- Play overlay that appears on hover -->
                            <div class="play-overlay">
                                <div class="play-button">
                                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="lesson-info">
                            <h3 class="lesson-title">{{ $lesson['title'] }}</h3>
                            <div class="lesson-meta">
                                <span class="lesson-subject">({{ $lesson['subject'] }})</span>
                                <span>{{ $lesson['instructor'] }} | {{ $lesson['year'] }}</span>
                            </div>
                            <div class="lesson-actions">
                                <a href="{{ route('dashboard.lesson.view', ['lessonId' => App\Services\UrlObfuscator::encode($lesson['id'])]) }}" class="lesson-action-btn primary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Watch
                                </a>
                                <a href="{{ $lesson['quiz_id'] ? route('quiz.instructions', ['quizId' => $lesson['encoded_quiz_id']]) : route('quiz.index') }}" class="lesson-action-btn secondary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Quiz
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No lessons available</h3>
                        <p style="color: var(--gray-500);">Lessons for {{ ucwords(str_replace('-', ' ', $selectedLevelGroup)) }} are coming soon!</p>
                    </div>
                    @endforelse
                    @endif
                </div>
            </div>
            <input type="checkbox" id="level-modal-toggle" style="display: none;">

            <div class="level-modal">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <h3>Select Grade</h3>
                    <a href="{{ route('dashboard.level-group', ['groupId' => 'primary-lower']) }}" class="level-option">Grade 1-3</a>
                    <a href="{{ route('dashboard.level-group', ['groupId' => 'primary-upper']) }}" class="level-option">Grade 4-6</a>
                    <a href="{{ route('dashboard.level-group', ['groupId' => 'jhs']) }}" class="level-option">Grade 7-9</a>
                    <a href="{{ route('dashboard.level-group', ['groupId' => 'shs']) }}" class="level-option">Grade 10-12</a>
                    <a href="{{ route('dashboard.level-group', ['groupId' => 'university']) }}" class="level-option">University</a>
                </div>
            </div>
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        let isSearching = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all functionality
            initializeMobileUI();
            initializeSidebar();
            console.log("hellow")
            initializeDropdowns();
            initializeSubjectFilter();
            initializeVideoCards(); // Re-enabled hover-to-play functionality with fixes
            initializeSearch();
        });

        // Add this new function to prevent scrolling on body when sidebar is open
        function preventBodyScroll() {
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Disable body scroll when mobile sidebar is open
            if (youtubeSidebar && sidebarOverlay) {
                youtubeSidebar.addEventListener('mouseenter', function() {
                if (window.innerWidth <= 768 && this.classList.contains('mobile-open')) {
                    document.body.style.overflow = 'hidden';
                }
                });
                
                youtubeSidebar.addEventListener('mouseleave', function() {
                document.body.style.overflow = '';
                });
                
                sidebarOverlay.addEventListener('click', function() {
                document.body.style.overflow = '';
                });
            }
        }
        
        // Call the new function
        preventBodyScroll();

        // Level modal close handler
        const levelModalToggle = document.getElementById('level-modal-toggle');
        const modalOverlay = document.querySelector('.modal-overlay');
        if (modalOverlay && levelModalToggle) {
            modalOverlay.addEventListener('click', () => {
                levelModalToggle.checked = false;
            });
        }

        // Close modal when selecting a level option
        document.querySelectorAll('.level-option').forEach(option => {
            option.addEventListener('click', () => {
                if (levelModalToggle) {
                    levelModalToggle.checked = false;
                }
            });
        });

        // Enhanced Mobile UI with search functionality
        function initializeMobileUI() {
            const mobileSearchToggle = document.getElementById('mobileSearchToggle');
            const mobileSearchBox = document.getElementById('mobileSearchBox');
            const searchInput = mobileSearchBox ? mobileSearchBox.querySelector('.search-input') : null;
            const searchClose = document.getElementById('searchClose');
            const filterBar = document.getElementById('filterBar');

            if (mobileSearchToggle && filterBar && searchInput) {
                mobileSearchToggle.addEventListener('click', function() {
                    filterBar.classList.add('search-active');
                    setTimeout(() => {
                        searchInput.focus();
                    }, 100);
                });
            }

            if (searchClose && searchInput && filterBar) {
                searchClose.addEventListener('click', function() {
                    if (searchInput.value) {
                        // Clear input if it has content
                        searchInput.value = '';
                        searchInput.focus();
                    } else {
                        // Close search if input is empty
                        filterBar.classList.remove('search-active');
                    }
                });
            }

            // Optional: Enter key to close search
            if (searchInput && filterBar) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        // Close search on Enter
                        filterBar.classList.remove('search-active');
                    }
                });
            }

            // Close search when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && filterBar.classList.contains('search-active')) {
                    if (searchInput && searchInput.value) {
                        searchInput.value = '';
                        searchInput.focus();
                    } else {
                        filterBar.classList.remove('search-active');
                    }
                }
            });
        }

        // YouTube-style sidebar functionality
        function initializeSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const youtubeSidebar = document.getElementById('youtubeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;
            const sidebarContent = document.querySelector('.sidebar-content');


            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    // Mobile behavior - overlay only, no layout changes
                    youtubeSidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('active');
                    body.classList.toggle('sidebar-open');
    
                    // Reset scroll position when opening sidebar
                    if (youtubeSidebar.classList.contains('mobile-open') && sidebarContent) {
                        sidebarContent.scrollTop = 0;
                    }
                } else {
                    // Desktop behavior - layout changes
                    youtubeSidebar.classList.toggle('collapsed');
    
                    // Update header position
                    updateHeaderPosition();
    
                    // Reset scroll position when collapsing/expanding
                    if (sidebarContent) {
                        sidebarContent.scrollTop = 0;
                    }
                }
            }
    
            // function updateHeaderPosition() {
            //     const topHeader = document.querySelector('.top-header');
            //     const filterBar = document.querySelector('.filter-bar');
            //     const subjectsFilter = document.querySelector('.subjects-filter-container');
            //     if (window.innerWidth > 768) {
            //         if (youtubeSidebar.classList.contains('collapsed')) {
            //             // Header padding-left handled by CSS sibling selector
            //             if (filterBar) {
            //                 filterBar.style.left = 'var(--sidebar-width-collapsed)';
            //                 filterBar.style.width = 'calc(100vw - var(--sidebar-width-collapsed))';
            //             }
            //             if (subjectsFilter) {
            //                 subjectsFilter.style.left = 'var(--sidebar-width-collapsed)';
            //                 subjectsFilter.style.width = 'calc(100vw - var(--sidebar-width-collapsed))';
            //             }
            //         } else {
            //             // Header padding-left handled by CSS
            //             if (filterBar) {
            //                 filterBar.style.left = 'var(--sidebar-width-expanded)';
            //                 filterBar.style.width = 'calc(100vw - var(--sidebar-width-expanded))';
            //             }
            //             if (subjectsFilter) {
            //                 subjectsFilter.style.left = 'var(--sidebar-width-expanded)';
            //                 subjectsFilter.style.width = 'calc(100vw - var(--sidebar-width-expanded))';
            //             }
            //         }
            //     } else {
            //         // Header padding-left handled by CSS
            //         if (filterBar) {
            //             filterBar.style.paddingLeft= '0';
            //             filterBar.style.width = '100vw';
            //         }
            //         if (subjectsFilter) {
            //             subjectsFilter.style.paddingLeft = '0';
            //             subjectsFilter.style.width = '100vw';
            //         }
            //     }
            // }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            // Close sidebar when clicking overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function(e) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                });
            }

            // Prevent scrolling when sidebar is open on mobile
            if (youtubeSidebar) {
                youtubeSidebar.addEventListener('touchmove', function(e) {
                    if (window.innerWidth <= 768 && this.classList.contains('mobile-open')) {
                        e.preventDefault();
                    }
                }, {passive: false });
            }


            // Handle window resize - ensure proper state transitions
            function handleResize() {
                if (window.innerWidth > 768) {
                    // Switching to desktop
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');

                    // Reset scroll position
                    if (sidebarContent) {
                        sidebarContent.scrollTop = 0;
                    }
                } else {
                    // Switching to Mobile
                    youtubeSidebar.classList.remove('collapsed');
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }

                // Adjust sidebar content height
                if (sidebarContent && window.innerWidth <= 768) {
                    const viewportHeight = window.innerHeight;
                    sidebarContent.style.maxHeight = `${viewportHeight - 64}px`; // Subtract header if exists
                }
            }

            window.addEventListener('resize', handleResize);

            // Initialize adjustment
            handleResize();

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Prevent body scroll when sidebar is open on mobile
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('touchmove', function(e) {
                    if (this.classList.contains('active')) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
        }


        // Custom dropdown functionality
        function initializeDropdowns() {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.closest('.custom-dropdown');
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
                    
                    console.log('Selected option:', text);
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

        // Subject filter functionality
        function initializeSubjectFilter() {
            const subjectChips = document.querySelectorAll('.subject-chip');
            const lessonCards = document.querySelectorAll('.lesson-card');

            subjectChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    const selectedSubject = this.getAttribute('data-subject');
                    
                    // Update active state
                    subjectChips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter lessons
                    lessonCards.forEach(card => {
                        const cardSubject = card.getAttribute('data-subject');
                        
                        if (selectedSubject === 'all' || cardSubject === selectedSubject) {
                            card.style.display = 'block';
                            // Add fade-in animation
                            card.style.opacity = '0';
                            setTimeout(() => {
                                card.style.opacity = '1';
                            }, 100);
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    console.log('Filtered by subject:', selectedSubject);
                });
            });
        }

        // Optimized hover-to-play functionality following YouTube/Netflix pattern
        function initializeVideoCards() {
            let hoverTimer = null;
            let activePlayer = null;
            let hasPreconnected = false;

            // Mobile detection
            const isMobile = 'ontouchstart' in window;

            document.querySelectorAll('.hover-video-card').forEach(card => {
                const videoPreview = card.querySelector('.video-preview');

                if (isMobile) {
                    // Mobile: use click instead of hover
                    card.addEventListener('click', function(e) {
                        // Don't trigger if clicking on action buttons
                        if (e.target.closest('.lesson-action-btn')) return;

                        e.preventDefault();
                        if (card.dataset.loaded === 'false') {
                            activatePreview(card);
                        }
                    });
                } else {
                    // Desktop: hover with debounce
                    card.addEventListener('mouseenter', () => {
                        hoverTimer = setTimeout(() => {
                            activatePreview(card);
                        }, 250); // prevents accidental hover
                    });

                    card.addEventListener('mouseleave', () => {
                        clearTimeout(hoverTimer);
                        deactivatePreview(card);
                    });
                }
            });

            function activatePreview(card) {
                if (card.dataset.loaded === 'true') return;

                // Kill previous preview
                if (activePlayer) {
                    activePlayer.pause();
                    activePlayer = null;
                    document.querySelectorAll('.video-preview').forEach(p => p.innerHTML = '');
                    document.querySelectorAll('.hover-video-card').forEach(c => c.dataset.loaded = 'false');
                }

                const videoId = card.dataset.vimeoId || card.dataset.externalVideoId;
                const videoSource = card.dataset.videoSource;
                const preview = card.querySelector('.video-preview');

                if (!videoId) return; // No video to preview

                // Preconnect to Vimeo on first hover
                if (!hasPreconnected && (videoSource === 'vimeo' || videoSource === 'youtube')) {
                    const link = document.createElement('link');
                    link.rel = 'preconnect';
                    link.href = videoSource === 'vimeo' ? 'https://player.vimeo.com' : 'https://www.youtube.com';
                    document.head.appendChild(link);
                    hasPreconnected = true;
                }

                if (videoSource === 'vimeo') {
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1&background=1`;
                    iframe.allow = 'autoplay';
                    iframe.loading = 'lazy';
                    iframe.frameBorder = '0';
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.position = 'absolute';
                    iframe.style.top = '0';
                    iframe.style.left = '0';

                    preview.appendChild(iframe);

                    activePlayer = new Vimeo.Player(iframe);
                } else if (videoSource === 'youtube') {
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&controls=0&modestbranding=1&rel=0&showinfo=0&loop=1&playlist=${videoId}`;
                    iframe.allow = 'autoplay';
                    iframe.loading = 'lazy';
                    iframe.frameBorder = '0';
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.position = 'absolute';
                    iframe.style.top = '0';
                    iframe.style.left = '0';

                    preview.appendChild(iframe);

                    // For YouTube, we can't easily get a player instance, so we'll handle cleanup differently
                    activePlayer = { pause: () => {}, element: iframe };
                }

                card.dataset.loaded = 'true';
            }

            function deactivatePreview(card) {
                card.dataset.loaded = 'false';

                const preview = card.querySelector('.video-preview');
                preview.innerHTML = ''; // destroy iframe

                if (activePlayer) {
                    activePlayer.pause();
                    activePlayer = null;
                }
            }

            // Handle lesson link clicks
            document.addEventListener('click', function(e) {
                if (e.target.closest('.lesson-link')) {
                    e.preventDefault();
                    const link = e.target.closest('.lesson-link');
                    const lessonId = link.getAttribute('data-lesson-id');
                    if (lessonId) {
                        window.location.href = `/dashboard/lesson/${lessonId}`;
                    }
                }
            });

            // Set periodic ping to keep session alive
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    fetch('/ping', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                }
            }, 300000); // 5 minutes
        }

        // Enhanced search functionality
        function initializeSearch() {
            // Check search container first
            console.log('Initializing search...');
            const searchContainer = document.querySelector('.search-container');
            console.log('Search container found:', searchContainer);
            if (searchContainer) {
                console.log('Search container display:', window.getComputedStyle(searchContainer).display);
                console.log('Search container visibility:', window.getComputedStyle(searchContainer).visibility);
            }

            // Try desktop search first, then mobile
            let searchBox = document.querySelector('.search-container .search-box');
            let searchInput = document.querySelector('.search-container .search-input');
            let searchButton = document.querySelector('.search-container .search-button');

            // If desktop search not found, try mobile search
            if (!searchInput) {
                searchBox = document.querySelector('#mobileSearchBox');
                searchInput = document.querySelector('#mobileSearchBox .search-input');
                searchButton = document.querySelector('#mobileSearchBox .search-button');
                console.log('Using mobile search');
            } else {
                console.log('Using desktop search');
            }

            console.log('Search box found:', searchBox);
            console.log('Search input found:', searchInput);
            console.log('Search button found:', searchButton);

            if (searchButton) {
                const rect = searchButton.getBoundingClientRect();
                console.log('Search button position:', {
                    top: rect.top,
                    left: rect.left,
                    width: rect.width,
                    height: rect.height,
                    visible: rect.width > 0 && rect.height > 0
                });
                console.log('Search button computed style:', window.getComputedStyle(searchButton));
            }

            // Add global click listener for debugging
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('search-button')) {
                    console.log('Search button clicked globally');
                }
            });

            // Screen size debugging removed - search now works on both desktop and mobile
            let searchTimeout;
            let originalLessonsHTML = ''; // Store original grid content
            let isSearching = false;

            // Store original content on page load
            document.addEventListener('DOMContentLoaded', function() {
                const grid = document.querySelector('.content-grid');
                if (grid) {
                    originalLessonsHTML = grid.innerHTML;
                }
            });

            // Handle input changes (debounced search)
            if (searchInput) {
                console.log('Adding input event listener');
                searchInput.addEventListener('input', function() {
                    console.log('Input event triggered, value:', this.value);
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length === 0) {
                        // Restore original content
                        restoreOriginalLessons();
                        return;
                    }

                    if (query.length < 2) {
                        return; // Don't search for very short queries
                    }

                    searchTimeout = setTimeout(() => {
                        performSearch(query);
                    }, 300); // Debounce 300ms
                });

                // Handle Enter key
                searchInput.addEventListener('keydown', function(e) {
                    console.log('Keydown event:', e.key);
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimeout);
                        const query = this.value.trim();
                        console.log('Enter pressed with query:', query);
                        if (query.length === 0) {
                            restoreOriginalLessons();
                        } else if (query.length >= 2) {
                            performSearch(query);
                        }
                    }
                });
            }

            // Handle search button click
            if (searchButton) {
                console.log('Adding search button click listener');
                searchButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Search button clicked');
                    clearTimeout(searchTimeout);
                    const query = searchInput ? searchInput.value.trim() : '';
                    console.log('Search query:', query);
                    if (query.length === 0) {
                        restoreOriginalLessons();
                    } else {
                        // Allow any length for button click (for testing)
                        performSearch(query);
                    }
                });
            } else {
                console.log('Search button not found, cannot attach listener');
            }
        }

        async function performSearch(query) {
            if (isSearching) return; // Prevent multiple simultaneous searches

            const levelGroup = '{{ $selectedLevelGroup }}';
            const grid = document.querySelector('.content-grid');

            // Find the active search box (desktop or mobile)
            let searchBox = document.querySelector('.search-container .search-box');
            if (!searchBox) {
                searchBox = document.querySelector('#mobileSearchBox');
            }

            if (!grid) return;

            isSearching = true;
            console.log('Starting search for:', query);

            // Add searching indicator
            if (searchBox) {
                searchBox.classList.add('searching');
            }

            // Show loading state
            grid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid rgba(225, 30, 45, 0.3); border-top: 3px solid #E11E2D; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="color: var(--gray-600); margin-top: 1rem;">Searching lessons...</p>
                </div>
            `;

            try {
                console.log('Making API request...');
                const response = await fetch(`/api/dashboard/search-lessons?q=${encodeURIComponent(query)}&level_group=${levelGroup}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                console.log('API response received:', response.status);
                const data = await response.json();
                console.log('API data:', data);

                if (data.success) {
                    updateLessonGrid(data.lessons, query);
                } else {
                    showSearchError(data.message || 'Search failed. Please try again.');
                }
            } catch (error) {
                console.error('Search error:', error);
                showSearchError('Search failed. Please try again.');
            } finally {
                isSearching = false;
                // Remove searching indicator
                const activeSearchBox = document.querySelector('.search-container .search-box') || document.querySelector('#mobileSearchBox');
                if (activeSearchBox) {
                    activeSearchBox.classList.remove('searching');
                }
                console.log('Search completed');
            }
        }

        function updateLessonGrid(lessons, query) {
            const grid = document.querySelector('.content-grid');

            if (!grid) return;

            if (!lessons || lessons.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--gray-400); margin-bottom: 1rem;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                            <line x1="13" x2="9" y1="9" y2="13"></line>
                            <line x1="9" x2="13" y1="9" y2="13"></line>
                        </svg>
                        <h3 style="color: var(--gray-600); margin-bottom: 1rem;">No lessons found</h3>
                        <p style="color: var(--gray-500);">No lessons match "<strong>${query}</strong>" in your current level.</p>
                        <p style="color: var(--gray-500); margin-top: 1rem;">Try different keywords or check your level selection.</p>
                    </div>
                `;
                return;
            }

            // Render lessons
            let html = '';
            lessons.forEach(lesson => {
                const documentsHtml = lesson.documents_count > 0 ? `
                    <div class="lesson-documents" style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--gray-500);">
                        📄 ${lesson.documents_count} document${lesson.documents_count !== 1 ? 's' : ''}
                    </div>
                ` : '';

                html += `
                    <div class="lesson-card hover-video-card" data-lesson-id="${lesson.encoded_id}" data-video-id="${lesson.id}" data-subject="${lesson.subject}" data-title="${lesson.title}" data-video-source="${lesson.video_source || 'local'}" data-vimeo-id="${lesson.vimeo_id || ''}" data-external-video-id="${lesson.external_video_id || ''}" data-mux-playback-id="${lesson.mux_playback_id || ''}" data-loaded="false">
                        <div class="lesson-thumbnail">
                            <img
                                src="${lesson.thumbnail}"
                                alt="${lesson.title}"
                                class="video-thumb"
                                loading="lazy"
                            />

                            <div class="video-preview"></div>

                            <div class="lesson-duration">${lesson.duration}</div>

                            <div class="lesson-level-badge">${lesson.level_display}</div>

                            <div class="play-overlay">
                                <div class="play-button">
                                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="lesson-info">
                            <h3 class="lesson-title">${lesson.title}</h3>
                            <div class="lesson-meta">
                                <span class="lesson-subject">(${lesson.subject})</span>
                                <span>${lesson.instructor} | ${lesson.year}</span>
                            </div>
                            ${documentsHtml}
                            <div class="lesson-actions">
                                <a href="#" data-lesson-id="${lesson.encoded_id}" class="lesson-action-btn primary lesson-link">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Watch
                                </a>
                                <a href="${lesson.encoded_quiz_id ? `/quiz/${lesson.encoded_quiz_id}/instructions` : '/quiz'}" class="lesson-action-btn secondary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Quiz
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.innerHTML = html;

            // Re-initialize video cards for the new content
            initializeVideoCards();
        }

        function restoreOriginalLessons() {
            const grid = document.querySelector('.content-grid');
            if (grid && originalLessonsHTML) {
                grid.innerHTML = originalLessonsHTML;
                // Re-initialize video cards for restored content
                initializeVideoCards();
            }
        }

        function showSearchError(message) {
            const grid = document.querySelector('.content-grid');
            if (grid) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--gray-400); margin-bottom: 1rem;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="m15 9-6 6"></path>
                            <path d="m9 9 6 6"></path>
                        </svg>
                        <h3 style="color: var(--gray-600); margin-bottom: 1rem;">Search Error</h3>
                        <p style="color: var(--gray-500);">${message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
