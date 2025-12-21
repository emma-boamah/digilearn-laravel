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
            overflow-x: hidden;
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
            z-index: 2000; /* Much higher than overlay */
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
            width: calc(100vw - var(--sidebar-width-expanded));
            max-width: calc(100vw - var(--sidebar-width-expanded));
            margin-left: var(--sidebar-width-expanded);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .youtube-sidebar.collapsed ~ .main-content {
            width: calc(100vw - var(--sidebar-width-collapsed));
            max-width: calc(100vw - var(--sidebar-width-collapsed));
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
        .filter-bar {
            display: flex;
            align-items: stretch;
            gap: 0.75rem; /* Reduced gap for mobile */
            padding: 0.75rem; /* Reduced padding */
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
            overflow-x: hidden; /* Hide overflow for horizontal scrolling */
            overflow-y: hidden; /* Hide vertical overflow */
            width: 100%;
            max-width: 100%; /* Ensure it doesn't exceed viewport width */
            box-sizing: border-box; /* Ensure padding is included in width */
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
            display: flex;
            transition: all 0.3s ease;
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
        }

        .search-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
        }

        .search-button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 2.5rem;
            background-color: var(--secondary-blue);
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
            width: 100%;
            max-width: 100%;
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0.75rem 1rem;
            overflow: hidden; /* Hide overflow on container */
            box-sizing: border-box; /* Ensure padding is included in width */
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
            padding: 2rem 1rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .lesson-card {
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
        }

        .lesson-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
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
            padding: 1.25rem;
        }

        .lesson-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .lesson-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-color: var(--primary-red);
            color: var(--white);
        }

        .lesson-action-btn.primary:hover {
            background-color: var(--primary-red-hover);
        }

        .lesson-action-btn.secondary {
            background-color: var(--white);
            color: var(--secondary-blue);
            border: 1px solid var(--secondary-blue);
        }

        .lesson-action-btn.secondary:hover {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .mobile-search-toggle {
            display: none; /* Hidden by default */
            background: var(--secondary-blue);
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
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
                z-index: 2000; /* Increased to be above overlay (1999) */
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .youtube-sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .filter-bar.search-active {
                flex-wrap: wrap;
                padding: 0 1rem;
            }

            .filter-bar.search-active > *:not(.search-box) {
                display: none;
            }

            .filter-bar.search-active .search-box {
                display: flex;
                flex: 1 0 100%;
                order: -1;
                position: static;
                visibility: visible;
                transform: none;
                min-width: 100%;
                padding: 0;
            }

            .filter-bar.search-active .search-close {
                display: flex;
            }
            .mobile-search-toggle {
                display: flex; /* Show on mobile */
            }
            .main-container {
                flex-direction: column;
                overflow: hidden;
                width: 100vw;
            }
            .youtube-sidebar {
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

            /* Hide desktop header on mobile */
            .top-header {
                position: sticky;
                top: 0;
                z-index: 1000;
                padding: 0.5rem 1rem;
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
            .search-box {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                z-index: 100;
                min-width: 100%;
                visibility: hidden;
                transform: translateY(-10px);
                background: var(--white);
                padding: 0.75rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .search-box.active {
                visibility: visible;
                transform: translateY(0);
                padding: 0;
            }

            .search-box.mobile-visible {
                display: flex;
            }
            
            .content-section {
                padding: 1rem 0.5rem;
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
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
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

            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
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
                padding: 0.75rem;
                overflow-x: visible;
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
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile - Moved outside main-container -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-container">
        @include('components.dashboard-sidebar')

        <!-- Main Content -->
        <main class="main-content">
            @include('components.dashboard-header')
            
            <!-- Search/Filter Bar -->
            <div class="filter-bar">
                <!-- Mobile Search Toggle Button (visible only on mobile) -->
                <button class="mobile-search-toggle" id="mobileSearchToggle">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <div class="search-box" id="mobileSearchBox">
                    <input type="text" class="search-input" placeholder="Search">
                    <button class="search-button">
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
                
                <!-- Custom levels dropdown -->
                <div class="custom-dropdown">
                    <button class="dropdown-toggle">
                        <span>{{ ucwords(str_replace('-', ' ', $selectedLevelGroup ?? 'Grade 1-3')) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-option">Grade 1-3</div>
                        <div class="dropdown-option">Grade 4-6</div>
                        <div class="dropdown-option">Grade 7-9</div>
                        <div class="dropdown-option">High School</div>
                    </div>
                </div>
                
                <button class="filter-button question">Question</button>
                <a href="{{ route('quiz.index') }}" class="filter-button quiz">Quiz</a>
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
                        <div class="lesson-card hover-video-card" data-lesson-id="{{ $course['id'] }}" data-subject="{{ $course['subject'] }}" data-title="{{ $course['title'] }}">
                            <div class="lesson-thumbnail">
                                <!-- Video element for hover-to-play functionality -->
                                <video 
                                    id="lesson-video-{{ $course['id'] }}" 
                                    class="lesson-video" 
                                    muted 
                                    loop 
                                    preload="metadata"
                                    poster="{{ secure_asset($course['thumbnail']) }}"
                                >
                                    <source src="{{ secure_asset($course['video_url']) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                
                                <!-- Fallback image if video fails to load -->
                                <img 
                                    src="{{ secure_asset($course['thumbnail']) }}" 
                                    alt="{{ $course['title'] }}" 
                                    class="lesson-fallback-image"
                                    style="display: none;"
                                    onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Course+Video'"
                                >
                                
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
                                    <a href="{{ route('dashboard.lesson.view', ['lessonId' => $course['id'], 'course_id' => $course['id']]) }}" class="lesson-action-btn primary">
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                        Start Course
                                    </a>
                                    <a href="{{ route('quiz.index') }}" class="lesson-action-btn secondary">
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
                    <div class="lesson-card hover-video-card" data-lesson-id="{{ $lesson['id'] }}" data-subject="{{ $lesson['subject'] }}" data-title="{{ $lesson['title'] }}">
                        <div class="lesson-thumbnail">
                            <!-- Video element for hover-to-play functionality -->
                            <video 
                                id="lesson-video-{{ $lesson['id'] }}" 
                                class="lesson-video" 
                                muted 
                                loop 
                                preload="metadata"
                                poster="{{ secure_asset($lesson['thumbnail']) }}"
                            >
                                <source src="{{ secure_asset($lesson['video_url']) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            
                            <!-- Fallback image if video fails to load -->
                            <img 
                                src="{{ secure_asset($lesson['thumbnail']) }}" 
                                alt="{{ $lesson['title'] }}" 
                                class="lesson-fallback-image"
                                style="display: none;"
                                onerror="this.src='https://via.placeholder.com/400x225/E11E2D/ffffff?text=Video+Lesson'"
                            >
                            
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
                                <a href="{{ route('dashboard.lesson.view', ['lessonId' => $lesson['id']]) }}" class="lesson-action-btn primary">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Watch
                                </a>
                                <a href="{{ route('quiz.index') }}" class="lesson-action-btn secondary">
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
        </main>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all functionality
            initializeMobileUI();
            initializeSidebar();
            initializeDropdowns();
            initializeSubjectFilter();
            initializeVideoCards();
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

        // Enhanced Mobile UI with search functionality
        function initializeMobileUI() {
            const mobileSearchToggle = document.getElementById('mobileSearchToggle');
            const mobileSearchBox = document.getElementById('mobileSearchBox');
            const searchInput = mobileSearchBox.querySelector('.search-input');
            const searchClose = document.getElementById('searchClose');
            const filterBar = document.querySelector('.filter-bar');
            
            if (mobileSearchToggle && mobileSearchBox) {
                mobileSearchToggle.addEventListener('click', function() {
                    filterBar.classList.toggle('search-active');
                    setTimeout(() => {
                        searchInput.focus();
                    }, 100);
                });
            }

            if (searchClose) {
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

            // Close search when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && filterBar.classList.contains('search-active')) {
                    if (searchInput.value) {
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


            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    // Mobile behavior - overlay only, no layout changes
                    youtubeSidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('active');
                    body.classList.toggle('sidebar-open');
                } else {
                    // Desktop behavior - layout changes
                    youtubeSidebar.classList.toggle('collapsed');
                }
            }

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


            // Handle window resize - ensure proper state transitions
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Switching to desktop - remove mobile overlay state
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                } else {
                    // Switching to mobile - ensure sidebar is collapsed (overlay state)
                    youtubeSidebar.classList.remove('collapsed');
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    body.classList.remove('sidebar-open');
                }
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && youtubeSidebar.classList.contains('mobile-open')) {
                    youtubeSidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        // Notification dropdown functionality
        const notificationButton = document.getElementById('notificationButton');
        const notificationDropdown = document.getElementById('notificationDropdown');

        if (notificationButton && notificationDropdown) {
            notificationButton.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
                
                // Close user dropdown if open
                const userDropdown = document.getElementById('userDropdownMenu');
                if (userDropdown) {
                    userDropdown.classList.remove('active');
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('active');
                }
            });

            // Mark all as read functionality
            const markAllReadBtn = notificationDropdown.querySelector('.mark-all-read');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function() {
                    const unreadNotifications = notificationDropdown.querySelectorAll('.notification-item.unread');
                    unreadNotifications.forEach(notification => {
                        notification.classList.remove('unread');
                    });
                    
                    // Update badge count
                    const badge = notificationButton.querySelector('.notification-badge');
                    if (badge) {
                        badge.style.display = 'none';
                    }
                    
                    showNotification('All notifications marked as read', 'success');
                });
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

        // Updated the playLesson function and video card click handling
        function initializeVideoCards() {
            const videoCards = document.querySelectorAll('.hover-video-card');
            let currentlyPlaying = null;
            
            videoCards.forEach(card => {
                const videoId = card.getAttribute('data-video-id') || `lesson-video-${card.getAttribute('data-lesson-id')}`;
                const video = document.getElementById(videoId);
                const loadingIndicator = card.querySelector('.video-loading');
                
                if (!video) return;
                
                // Handle video loading
                video.addEventListener('loadstart', function() {
                    if (loadingIndicator) loadingIndicator.style.display = 'flex';
                });
                
                video.addEventListener('canplay', function() {
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
                
                // Handle video errors
                video.addEventListener('error', function() {
                    console.log('Video failed to load:', videoId);
                    card.classList.add('video-error');
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
                
                // Play video on hover
                card.addEventListener('mouseenter', function() {
                    if (currentlyPlaying && currentlyPlaying !== video) {
                        currentlyPlaying.pause();
                        currentlyPlaying.currentTime = 0;
                        currentlyPlaying.parentElement.parentElement.classList.remove('playing');
                    }
                    
                    if (video.paused && !card.classList.contains('video-error')) {
                        if (video.currentTime === video.duration) {
                            video.currentTime = 0;
                        }
                        
                        const playPromise = video.play();
                        
                        if (playPromise !== undefined) {
                            playPromise
                                .then(() => {
                                    card.classList.add('playing');
                                    currentlyPlaying = video;
                                })
                                .catch(error => {
                                    console.log('Autoplay prevented:', error);
                                    card.classList.add('video-error');
                                });
                        }
                    }
                });
                
                // Pause video when mouse leaves
                card.addEventListener('mouseleave', function() {
                    if (!video.paused) {
                        video.pause();
                        video.currentTime = 0;
                        card.classList.remove('playing');
                        if (currentlyPlaying === video) {
                            currentlyPlaying = null;
                        }
                    }
                });
                
                // Handle video end
                video.addEventListener('ended', function() {
                    this.currentTime = 0;
                    card.classList.remove('playing');
                    if (currentlyPlaying === video) {
                        currentlyPlaying = null;
                    }
                });
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
    </script>
</body>
</html>
