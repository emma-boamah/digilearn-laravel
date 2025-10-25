<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile Settings - {{ config('app.name', 'ShoutOutGh') }}</title>
    
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
            --success-green: #10b981;
            --warning-yellow: #f59e0b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: var(--gray-600);
            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: opacity 0.3s ease;
        }

        .sidebar-logo img {
            height: 32px;
            width: auto;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            color: var(--gray-600);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }

        /* Add notification badge */
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

        .notification-btn:hover {
            background: var(--gray-100);
            color: var(---gray-900);
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

        .user-avatar-header {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.875rem;
            font-weight: 600;
            overflow: hidden;
        }

        .user-avatar-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Main Layout */
        .main-container {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            padding: 2rem 0;
            position: sticky;
            top: 64px;
            height: calc(100vh - 64px);
            overflow-y: auto;
        }

        .profile-summary {
            text-align: center;
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 80px;
            height: 148px;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .profile-email {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .profile-location {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .flag-icon {
            width: 16px;
            height: 12px;
            border-radius: 2px;
        }

        /* Navigation Menu */
        .nav-menu {
            padding: 0 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 0.875rem;
        }

        .nav-item:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .nav-item.active {
            background: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
            font-weight: 600;
        }

        .nav-item.danger {
            color: var(--primary-red);
        }

        .nav-item.danger:hover {
            background: rgba(225, 30, 45, 0.1);
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--gray-50);
        }

        .content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-red);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-red-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Profile Form */
        .profile-form {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        /* Avatar Upload */
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-red), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            font-weight: 700;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .edit-avatar-btn {
            background: var(--primary-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .edit-avatar-btn:hover {
            background: var(--primary-red-hover);
            transform: translateY(-1px);
        }

        .avatar-upload-input {
            display: none;
        }

        /* Form Fields */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 30, 45, 0.1);
        }

        .form-input:disabled {
            background: var(--gray-50);
            color: var(--gray-500);
            cursor: not-allowed;
        }

        /* Select with Flag */
        .select-with-flag {
            position: relative;
        }

        .select-with-flag select {
            padding-left: 3rem;
        }

        .flag-display {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            pointer-events: none;
            z-index: 1;
        }

        /* Success Message */
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-green);
            color: var(--success-green);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-right: none;
                border-bottom: 1px solid var(--gray-200);
            }

            .profile-summary {
                display: none;
            }

            .nav-menu {
                display: flex;
                overflow-x: auto;
                padding: 1rem;
                gap: 0.5rem;
            }

            .nav-item {
                white-space: nowrap;
                margin-bottom: 0;
                flex-shrink: 0;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0.75rem 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .profile-form {
                padding: 1.5rem;
            }

            .header-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                font-size: 0.8125rem;
                padding: 0.5rem 1rem;
            }
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-200);
            min-width: 280px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .user-info .user-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .user-info .user-email {
            font-size: 0.875rem;
            color: var(--gray-600);
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

        .dropdown-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 0.5rem 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .dropdown-item.logout-item {
            color: var(--primary-red);
        }

        .dropdown-item.logout-item:hover {
            background: rgba(225, 30, 45, 0.1);
        }

        .dropdown-form {
            margin: 0;
        }

        /* Phone Input Styles */
        .phone-input-container {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .country-code-selector {
            position: relative;
            flex-shrink: 0;
        }

        .country-code-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: var(--white);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            min-width: 120px;
        }

        .country-code-btn:hover {
            border-color: var(--primary-red);
        }

        .country-flag {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        .country-code {
            font-weight: 500;
            color: var(--gray-700);
        }

        .country-code-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            max-height: 300px;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .country-code-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .country-search {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .country-search-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .country-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .country-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.875rem;
        }

        .country-option:hover {
            background: var(--gray-50);
        }

        .country-name {
            flex: 1;
            color: var(--gray-700);
        }

        .phone-number-input {
            flex: 1;
        }

        .phone-verification {
            margin-top: 0.5rem;
        }

        .verify-phone-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--success-green);
            color: var(--white);
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .verify-phone-btn:hover {
            background: #059669;
        }

        .verified-badge {
            color: var(--success-green);
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .unverified-badge {
            color: var(--warning-yellow);
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-outline {
            background: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        .privacy-notice {
            background-color: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .privacy-notice-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .privacy-notice-text {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .optional-indicator {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-style: italic;
            margin-left: 0.25rem;
        }

        /* Subject Preferences Styles */
        .subject-preferences {
            margin-top: 0.5rem;
        }

        .preference-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .preference-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .preference-option:hover {
            border-color: var(--primary-red);
            background: rgba(225, 30, 45, 0.05);
        }

        .preference-option input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid var(--gray-300);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .preference-option input[type="checkbox"]:checked + .checkmark {
            background: var(--primary-red);
            border-color: var(--primary-red);
        }

        .preference-option input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            color: var(--white);
            font-size: 12px;
            font-weight: bold;
        }

        .preference-option span:last-child {
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        /* Modal Base Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            padding: 1rem;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-container {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            transform: scale(0.95) translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-container {
            transform: scale(1) translateY(0);
        }

        .modal-container.modal-sm {
            max-width: 400px;
        }

        .modal-container.modal-lg {
            max-width: 800px;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .modal-header.danger-header {
            background: #fef2f2;
            border-bottom-color: #fecaca;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: var(--gray-500);
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .modal-body {
            padding: 2rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        /* Security Notice */
        .security-notice {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .security-notice .notice-icon {
            color: #3b82f6;
            flex-shrink: 0;
        }

        .security-notice p {
            margin: 0;
            font-size: 0.875rem;
            color: #1e40af;
        }

        /* Verification Info */
        .verification-info {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .verification-icon {
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .verification-info h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0 0 0.5rem 0;
        }

        .verification-info p {
            color: var(--gray-600);
            margin: 0;
        }

        .verification-code-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5rem;
            font-family: "Courier New", monospace;
        }

        .processing {
            display: none !important;
        }

        .btn-loading {
            display: none !important;
        }

        /* Show processing state only when button has loading or processing class */
        .btn.loading .btn-loading,
        .btn.processing .btn-loading {
            display: inline-flex !important;
        }

        .btn.loading .btn-text,
        .btn.processing .btn-text {
            display: none !important;
        }

        .resend-section {
            text-align: center;
            margin-top: 1rem;
        }

        .resend-section p {
            color: var(--gray-600);
            font-size: 0.875rem;
            margin: 0;
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--primary-red);
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline;
        }

        .resend-btn:hover {
            color: var(--primary-red-hover);
        }

        /* Danger Warning */
        .danger-warning {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .warning-icon {
            color: var(--primary-red);
            margin-bottom: 1rem;
        }

        .danger-warning h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-red);
            margin: 0 0 0.75rem 0;
        }

        .danger-warning p {
            color: var(--gray-600);
            margin: 0 0 1rem 0;
        }

        .deletion-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .deletion-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .deletion-list li::before {
            content: "•";
            color: var(--primary-red);
            font-weight: bold;
        }

        .btn-danger {
            background: var(--primary-red);
            color: var(--white);
        }

        .btn-danger:hover:not(:disabled) {
            background: var(--primary-red-hover);
        }

        .btn-danger:disabled {
            background: var(--gray-300);
            color: var(--gray-500);
            cursor: not-allowed;
        }

        /* Keep disabled state styling but don't show spinner */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn:disabled .btn-text {
            display: inline !important;
        }

        .btn:disabled .btn-loading {
            display: none !important;
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Notification Toast */
        .notification-toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1100;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            min-width: 300px;
            max-width: 400px;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .toast-icon {
            flex-shrink: 0;
        }

        .toast-message {
            font-size: 0.875rem;
            color: var(--gray-700);
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            color: var(--gray-400);
            transition: all 0.2s ease;
        }

        .toast-close:hover {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .toast-success .toast-icon {
            color: var(--success-green);
        }

        .toast-error .toast-icon {
            color: var(--primary-red);
        }

        .toast-info .toast-icon {
            color: var(--secondary-blue);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-container {
                margin: 1rem;
                max-height: calc(100vh - 2rem);
            }

            .modal-header,
            .modal-footer {
                padding: 1rem 1.5rem;
            }

            .modal-body {
                padding: 1.5rem;
            }

            .modal-footer {
                flex-direction: column-reverse;
            }

            .modal-footer .btn {
                width: 100%;
            }

            .notification-toast {
                left: 1rem;
                right: 1rem;
                min-width: auto;
            }
        }

        /* Subscription Modals Specific Styles */
        .plan-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .plan-card-modal {
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .plan-card-modal:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .plan-card-modal.selected {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 2px var(--primary-red);
        }

        .plan-card-modal .plan-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .plan-card-modal .plan-price {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--primary-red);
            margin-bottom: 0.5rem;
        }

        .plan-card-modal .plan-period {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .plan-card-modal .plan-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 1rem;
            min-height: 40px; /* Ensure consistent height */
        }

        .plan-card-modal .plan-features-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            text-align: left;
        }

        .plan-card-modal .plan-features-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .plan-card-modal .plan-features-list li svg {
            color: var(--success-green);
            flex-shrink: 0;
        }

        .plan-card-modal .selected-indicator {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            color: var(--primary-red);
            font-size: 1.25rem;
            display: none;
        }

        .plan-card-modal.selected .selected-indicator {
            display: block;
        }

        .current-plan-details {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .current-plan-details h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }

        .current-plan-details p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .current-plan-details .status-badge {
            margin-top: 0.5rem;
        }

        .cancel-subscription-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .cancel-subscription-section h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-red);
            margin-bottom: 0.75rem;
        }

        .cancel-subscription-section p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .billing-history-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .billing-history-section h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        .billing-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .billing-table th, .billing-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
            text-align: left;
        }

        .billing-table th {
            background: var(--gray-100);
            color: var(--gray-700);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .billing-table td {
            color: var(--gray-800);
        }

        .billing-table tr:last-child td {
            border-bottom: none;
        }

        .billing-table .text-right {
            text-align: right;
        }

        .billing-table .text-green {
            color: var(--success-green);
            font-weight: 600;
        }

        .billing-table .text-red {
            color: var(--primary-red);
            font-weight: 600;
        }

        .no-history-message {
            text-align: center;
            color: var(--gray-500);
            padding: 2rem;
        }

        /* Avatar image styles */
        .avatar-image {
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }

        .avatar-image.header {
            width: 80px;
            height: 55px;
        }

        .avatar-image.large {
            width: 120px;
            height: 120px;
        }

        /* Phone actions layout */
        .phone-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Subscription button margins */
        .subscription-btn-margin {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="back-button" id="backButton">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="sidebar-logo">
                <img src="{{ secure_asset('images/shoutoutgh-logo.png') }}" alt="ShoutOutGh">
            </div>
        </div>
        
        <div class="header-right">
            <button class="notification-btn" id="notificationButton">
                <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
            </button>
            
            <div class="user-dropdown">
                <button class="user-avatar-header" id="userDropdownToggle">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="avatar-image header">
                    @else
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    @endif
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="user-email">{{ auth()->user()->email ?? 'user@example.com' }}</div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile Settings
                    </a>
                    <a href="{{ route('dashboard.digilearn') }}" class="dropdown-item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Back to Lessons
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="dropdown-form">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Log Out
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notification dropdown in the header -->
            <div class="notification-dropdown">
                <div class="notification-dropdown-menu" id="notificationDropdown">
                    <div class="dropdown-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read">Mark all as read</button>
                    </div>
                    <div class="notification-list">
                        <!-- Notifications will be populated here -->
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="notification-content">
                                <p>New follower: John Doe started following you</p>
                                <span class="notification-time">2 hours ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="notification-content">
                                <p>Congratulations! You completed the Math challenge</p>
                                <span class="notification-time">1 day ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-footer">
                        <a href="{{ route('dashboard.notifications') }}" class="view-all">View all notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-summary">
                <div class="profile-avatar">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="avatar-image sidebar">
                    @else
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    @endif
                </div>
                <div class="profile-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="profile-email">{{ auth()->user()->email ?? 'Please Sign up' }}</div>
                <div class="profile-location">
                    <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="flag-icon">
                    <span>Ghana | 6+ JHS 2024</span>
                </div>
            </div>
            
            <nav class="nav-menu">
                <a href="#" class="nav-item active" data-section="account">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Account
                </a>
                
                <a href="{{ route('dashboard.notifications') }}" class="nav-item" data-section="notifications">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notification
                </a>
                
                <a href="#" class="nav-item" data-section="language">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    Language
                </a>

                <button class="nav-item" id="changePasswordBtn">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2v5a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2h6zM12 14a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                    Change Password
                </button>
                
                <a href="#" class="nav-item" data-section="questions">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Question
                </a>
                
                <a href="#" class="nav-item" data-section="help">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 0 0012 2.25z"/>
                    </svg>
                    Help
                </a>
                
                <button class="nav-item danger" id="deleteAccountBtn">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Account
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Account</h1>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                Profile updated successfully!
            </div>

            <form class="profile-form" id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Avatar Upload Section -->
                <div class="avatar-upload">
                    <div class="avatar-large" id="avatarPreview">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="Profile" class="avatar-image large" id="avatarImage">
                        @else
                            {{ substr(auth()->user()->name ?? 'AS', 0, 2) }}
                        @endif
                    </div>
                    <button type="button" class="edit-avatar-btn" id="editAvatarBtn">
                        <i class="fas fa-camera"></i>
                        Edit
                    </button>
                    <input type="file" id="avatarInput" name="avatar" class="avatar-upload-input" accept="image/*">
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="{{ explode(' ', auth()->user()->name ?? 'Aboagye Samuel')[0] }}" required autocomplete="">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="{{ explode(' ', auth()->user()->name ?? 'Aboagye Samuel')[1] ?? '' }}" required autocomplete>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ auth()->user()->email ?? 'samuel.aboagye@gmail.com' }}" required autocomplete>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                Phone Number 
                                <span class="optional-indicator">(Optional - for account security)</span>
                                @if(auth()->user()->phone_verified_at)
                                    <span class="verified-badge">✓ Verified</span>
                                @elseif(auth()->user()->phone)
                                    <span class="unverified-badge">⚠ Unverified</span>
                                @endif
                            </label>
                            
                            @if(!auth()->user()->phone)
                                <!-- Privacy Notice for new phone number -->
                                <div class="privacy-notice">
                                    <div class="privacy-notice-header">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Why add your phone number?
                                    </div>
                                    <div class="privacy-notice-text">
                                        Adding your phone number enhances your account security with two-factor authentication and helps with account recovery. 
                                        We never share your number with third parties or use it for marketing.
                                    </div>
                                </div>
                            @endif

                            <div class="phone-input-container">
                                <div class="country-code-selector">
                                    <button type="button" class="country-code-btn" id="countryCodeBtn">
                                        <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="country-flag" id="selectedFlag">
                                        <span class="country-code" id="selectedCode">+233</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div class="country-code-dropdown" id="countryCodeDropdown">
                                        <div class="country-search">
                                            <input type="text" placeholder="Search countries..." class="country-search-input" id="countrySearch">
                                        </div>
                                        <div class="country-list" id="countryList">
                                            <!-- Countries will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    class="form-input phone-number-input" 
                                    value="{{ auth()->user()->phone ? ltrim(auth()->user()->phone, auth()->user()->phone ? substr(auth()->user()->phone, 0, strpos(auth()->user()->phone, ' ') + 1) : '') : '' }}" 
                                    placeholder="24 123 4567"
                                >
                                <input type="hidden" id="country_code" name="country_code" value="+233" autocomplete>
                            </div>
                            
                            <div class="phone-actions">
                                @if(auth()->user()->phone)
                                    @if(!auth()->user()->phone_verified_at)
                                        <button type="button" class="btn btn-primary btn-sm" id="verifyCurrentNumberBtn">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Verify Current Number
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-secondary btn-sm" id="updateNumberBtn">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Update Number
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" id="removePhoneBtn">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Remove
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm" id="addPhoneBtn">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Add Phone Number
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="{{ auth()->user()->date_of_birth ?? '2008-01-15' }}" autocomplete>
                        </div>
                    </div>
                </div>

                <!-- Location & Education -->
                <div class="form-section">
                    <h2 class="section-title">Location & Education</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="country" class="form-label">Country</label>
                            <div class="select-with-flag">
                                <div class="flag-display">
                                    <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="flag-icon">
                                </div>
                                <select id="country" name="country" class="form-input">
                                    <option value="Ghana" data-code="gh" selected>Ghana</option>
                                    <option value="Nigeria" data-code="ng">Nigeria</option>
                                    <option value="Kenya" data-code="ke">Kenya</option>
                                    <option value="South Africa" data-code="za">South Africa</option>
                                    <option value="United States" data-code="us">United States</option>
                                    <option value="United Kingdom" data-code="uk">United Kingdom</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-input" value="{{ auth()->user()->city ?? 'Accra' }}" autocomplete>
                        </div>
                        
                        <div class="form-group">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select id="education_level" name="education_level" class="form-input">
                                <option value="primary">Primary School</option>
                                <option value="jhs" selected>Junior High School (JHS)</option>
                                <option value="shs">Senior High School (SHS)</option>
                                <option value="university">University</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="grade" class="form-label">Grade/Year</label>
                            <select id="grade" name="grade" class="form-input">
                                <option value="1">Grade 1</option>
                                <option value="2">Grade 2</option>
                                <option value="3">Grade 3</option>
                                <option value="4">Grade 4</option>
                                <option value="5">Grade 5</option>
                                <option value="6">Grade 6</option>
                                <option value="7" selected>Grade 7 (JHS 1)</option>
                                <option value="8">Grade 8 (JHS 2)</option>
                                <option value="9">Grade 9 (JHS 3)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="form-section">
                    <h2 class="section-title">Learning Preferences</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="preferred_language" class="form-label">Preferred Language</label>
                            <select id="preferred_language" name="preferred_language" class="form-input">
                                <option value="en" selected>English</option>
                                <option value="tw">Twi</option>
                                <option value="ga">Ga</option>
                                <option value="ee">Ewe</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="learning_style" class="form-label">Learning Style</label>
                            <select id="learning_style" name="learning_style" class="form-input">
                                <option value="visual">Visual Learner</option>
                                <option value="auditory" selected>Auditory Learner</option>
                                <option value="kinesthetic">Kinesthetic Learner</option>
                                <option value="mixed">Mixed Learning</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="bio" class="form-label">Bio (Optional)</label>
                            <textarea id="bio" name="bio" class="form-input" rows="3" placeholder="Tell us a bit about yourself...">{{ auth()->user()->bio ?? 'Passionate student from Ghana, currently in JHS 2. I love learning new things and exploring different subjects!' }}</textarea>
                        </div>
                        <div class="header-actions">
                            <button type="button" class="btn btn-secondary" id="cancelChangesBtn">Cancel</button>
                            <button type="submit" form="profileForm" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan Section -->
                <div class="form-section">
                    <h2 class="section-title">Subscription Plan</h2>
                    @if($user->currentSubscription)
                        <div class="current-plan-details">
                            <h4>Current Plan: <span id="currentPlanNameDisplay">{{ $user->currentSubscription->pricingPlan->name }}</span></h4>
                            <p>Price: <span id="currentPlanPriceDisplay">{{ $user->currentSubscription->pricingPlan->formatted_price }}/{{ $user->currentSubscription->pricingPlan->period }}</span></p>
                            <p>Status: 
                                <span id="currentPlanStatusDisplay" class="status-badge status-{{ strtolower($user->currentSubscription->status) }}">
                                    {{ ucfirst($user->currentSubscription->status) }}
                                </span>
                            </p>
                            @if($user->currentSubscription->is_in_trial)
                                <p id="currentPlanTrialInfoDisplay">Your trial expires in {{ now()->diffInDays($user->currentSubscription->trial_ends_at) }} days on {{ $user->currentSubscription->trial_ends_at->format('M d, Y') }}</p>
                            @elseif($user->currentSubscription->expires_at)
                                <p id="currentPlanExpiryDisplay">Next Billing Date: {{ $user->currentSubscription->expires_at->format('M d, Y') }} ({{ now()->diffInDays($user->currentSubscription->expires_at) }} days)</p>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm subscription-btn-margin" id="manageSubscriptionBtn">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                Manage Subscription
                            </button>
                        </div>
                    @else
                        <div class="current-plan-details">
                            <h4>You are currently on the Free Plan.</h4>
                            <p>Upgrade to unlock premium features and content.</p>
                            <button type="button" class="btn btn-primary subscription-btn-margin" id="subscribeNowBtn">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Subscribe Now
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </main>
    </div>

    <!-- Phone Number Update Modal -->
    <div id="phoneUpdateModal" class="modal-overlay">
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3 class="modal-title">Update Phone Number</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="security-notice">
                    <div class="notice-icon">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p>We'll send a verification code to your new number to confirm the change.</p>
                </div>
                
                <form id="phoneUpdateForm">
                    <div class="form-group">
                        <label for="modal_phone" class="form-label">New Phone Number</label>
                        <div class="phone-input-container">
                            <div class="country-code-selector">
                                <button type="button" class="country-code-btn" id="modalCountryCodeBtn">
                                    <img src="https://flagcdn.com/w20/gh.png" alt="Ghana" class="country-flag" id="modalSelectedFlag">
                                    <span class="country-code" id="modalSelectedCode">+233</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div class="country-code-dropdown" id="modalCountryCodeDropdown">
                                    <div class="country-search">
                                        <input type="text" placeholder="Search countries..." class="country-search-input" id="modalCountrySearch">
                                    </div>
                                    <div class="country-list" id="modalCountryList">
                                        <!-- Countries will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <input type="tel" id="modal_phone" name="phone" class="form-input phone-number-input" 
                                   placeholder="24 123 4567" required>
                            <input type="hidden" id="modal_country_code" name="country_code" value="+233">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal_current_password" class="form-label">Current Password *</label>
                        <input type="password" id="modal_current_password" name="current_password" class="form-input" 
                               placeholder="Enter your current password" required>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelPhoneUpdateBtn">Cancel</button>
                <button class="btn btn-primary" id="submitPhoneUpdateBtn">
                    <span class="btn-text">Update Phone</span>
                    <span class="btn-loading">
                        <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
                        </svg>
                        Updating...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Phone Verification Modal -->
    <div id="phoneVerificationModal" class="modal-overlay">
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3 class="modal-title">Verify Phone Number</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="verification-info">
                    <div class="verification-icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h4>Verification Code Sent</h4>
                    <p>We've sent a 6-digit verification code to <span id="verificationPhoneNumber"></span></p>
                </div>
                
                <form id="phoneVerificationForm">
                    <div class="form-group">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <input type="text" id="verification_code" name="verification_code" class="form-input verification-code-input" 
                               placeholder="Enter 6-digit code" maxlength="6" required>
                    </div>
                    
                    <div class="resend-section">
                        <p>Didn't receive the code? <button type="button" class="resend-btn" id="resendVerificationBtn">Resend</button></p>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelPhoneVerifyBtn">Cancel</button>
                <button class="btn btn-primary" id="submitPhoneVerifyBtn">
                    <span class="btn-text">Verify</span>
                    <span class="btn-loading">
                        <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
                        </svg>
                        Verifying...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordChangeModal" class="modal-overlay">
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3 class="modal-title">Change Password</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="passwordChangeForm">
                    <div class="form-group">
                        <label for="current_password_change" class="form-label">Current Password *</label>
                        <input type="password" id="current_password_change" name="current_password" class="form-input" 
                               placeholder="Enter your current password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" id="new_password" name="password" class="form-input" 
                               placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password *</label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation" class="form-input" 
                               placeholder="Confirm new password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelPasswordChangeBtn">Cancel</button>
                <button class="btn btn-primary" id="submitPasswordChangeBtn">
                    <span class="btn-text">Change Password</span>
                    <span class="btn-loading">
                        <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
                        </svg>
                        Changing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteAccountModal" class="modal-overlay">
        <div class="modal-container modal-sm">
            <div class="modal-header danger-header">
                <h3 class="modal-title">Delete Account</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="danger-warning">
                    <div class="warning-icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h4>This action cannot be undone</h4>
                    <p>Deleting your account will permanently remove all your data, including:</p>
                    <ul class="deletion-list">
                        <li>Your profile and personal information</li>
                        <li>Learning progress and achievements</li>
                        <li>All saved content and preferences</li>
                        <li>Access to your account and lessons</li>
                    </ul>
                </div>
                
                <form id="deleteAccountForm">
                    <div class="form-group">
                        <label for="delete_confirmation" class="form-label">
                            Type "DELETE" to confirm:
                        </label>
                        <input type="text" id="delete_confirmation" class="form-input" 
                               placeholder="Type DELETE here" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="delete_password" class="form-label">Current Password *</label>
                        <input type="password" id="delete_password" name="current_password" class="form-input" 
                               placeholder="Enter your current password" required>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelDeleteAccountBtn">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="btn-text">Delete Account</span>
                    <span class="btn-loading">
                        <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
                        </svg>
                        Deleting...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Subscribe/Upgrade Plan Modal -->
    <div id="subscribeUpgradeModal" class="modal-overlay">
        <div class="modal-container modal-lg">
            <div class="modal-header">
                <h3 class="modal-title" id="subscribeUpgradeModalTitle">Choose Your Plan</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-gray-600 mb-6" id="subscribeUpgradeModalDescription">
                    Unlock premium features and content with a subscription plan.
                </p>
                <div class="plan-selection-grid" id="planSelectionGrid">
                    <!-- Plans will be dynamically loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelSubscribeUpgradeBtn">Cancel</button>
                <button class="btn btn-primary" id="confirmPlanSelectionBtn">
                    <span class="btn-text">Confirm Plan</span>
                    <span class="btn-loading">
                        <svg class="spinner" width="16" height="16" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Manage Subscription Modal -->
    <div id="manageSubscriptionModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Manage Your Subscription</h3>
                <button class="modal-close">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="current-plan-details">
                    <h4>Current Plan: <span id="currentPlanName"></span></h4>
                    <p>Price: <span id="currentPlanPrice"></span></p>
                    <p>Status: <span id="currentPlanStatus" class="status-badge"></span></p>
                    <p id="currentPlanExpiry"></p>
                    <p id="currentPlanTrialInfo"></p>
                </div>

                <div class="billing-history-section">
                    <h4>Billing History</h4>
                    <div id="billingHistoryContent">
                        <!-- Billing history will be dynamically loaded here -->
                    </div>
                </div>

                <div class="cancel-subscription-section">
                    <h4>Cancel Subscription</h4>
                    <p>If you cancel, your subscription will remain active until the end of your current billing period.</p>
                    <button class="btn btn-danger" id="confirmCancelSubscriptionBtn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cancel Subscription
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeManageSubscriptionBtn">Close</button>
                <button class="btn btn-primary" id="changePlanBtn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Change Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Success/Error Notification -->
    <div id="notificationToast" class="notification-toast">
        <div class="toast-content">
            <div class="toast-icon" id="toastIcon">
                <!-- Icon will be populated by JavaScript -->
            </div>
            <div class="toast-message" id="toastMessage">
                <!-- Message will be populated by JavaScript -->
            </div>
        </div>
        <button class="toast-close" id="toastCloseBtn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- External JavaScript Files -->
    <script src="/js/utils.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="/js/country-selector.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="/js/modals.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="/js/modal-phone-input.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="/js/profile.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    <script src="/js/main.js" nonce="{{ request()->attributes->get('csp_nonce') }}"></script>
    
    <!-- Inline script for avatar preview functionality -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Profile Page Initialized');

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
            
            // Avatar preview functionality
            const avatarInput = document.getElementById('avatarInput');
            const editAvatarBtn = document.getElementById('editAvatarBtn');

            if (avatarInput && editAvatarBtn) {
                editAvatarBtn.addEventListener('click', function() {
                    avatarInput.click();
                });

                avatarInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        const preview = document.getElementById('avatarPreview');
                        let avatarImage = document.getElementById('avatarImage');

                        reader.onload = function(e) {
                            if (!avatarImage) {
                                avatarImage = document.createElement('img');
                                avatarImage.id = 'avatarImage';
                                avatarImage.className = 'avatar-image';
                                preview.innerHTML = '';
                                preview.appendChild(avatarImage);
                            }
                            avatarImage.src = e.target.result;

                            if (typeof showNotification === 'function') {
                                showNotification('click "Update" to save your profile picture', 'info');
                            }
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Country flag update
            function updateFlag(select) {
                const flagDisplay = select.parentElement.querySelector('.flag-display img');
                const selectedOption = select.options[select.selectedIndex];
                const countryCode = selectedOption.getAttribute('data-code');
                if (countryCode) {
                
                    flagDisplay.src = `https://flagcdn.com/w20/${countryCode.toLowerCase()}.png`;
                    flagDisplay.alt = selectedOption.text;
                }
            }

            // Form submission
            document.getElementById('profileForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;
                
                try {
                    const formData = new FormData(this);
                    
                    // Log form data for debugging
                    console.log('Form data being submitted:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}:`, value);
                    }
                    
                    const response = await fetch('{{ route("profile.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        // Handle validation errors
                        if (response.status === 422 && data.errors) {
                            let errorMessage = 'Please correct the following issues:';
                            for (const [field, errors] of Object.entries(data.errors)) {
                                // Format field name to be more readable
                                const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                errorMessage += `\n- ${fieldName}: ${errors[0]}`;
                            }
                            showNotification(errorMessage, 'error');
                            return;
                        }
                        throw new Error(data.message || 'Failed to update profile');
                    }
                    
                    // Success case
                    if (data.avatar_url) {
                        const timestamp = new Date().getTime();
                        const avatarUrl = data.avatar_url + '?v=' + timestamp;

                        // Update main profile form avatar
                        const avatarImage = document.getElementById('avatarImage');
                        if (avatarImage) {
                            avatarImage.src = avatarUrl;
                        } else {
                            const preview = document.getElementById('avatarPreview');
                            if (preview) {
                                preview.innerHTML = `<img src="${avatarUrl}" alt="Profile" class="avatar-image large" id="avatarImage">`;
                            }
                        }

                        // Update sidebar avatar
                        const sidebarAvatar = document.querySelector('.profile-avatar img');
                        if (sidebarAvatar) {
                            sidebarAvatar.src = avatarUrl;
                        } else {
                            const sidebarContainer = document.querySelector('.profile-avatar');
                            if (sidebarContainer) {
                                sidebarContainer.innerHTML = `<img src="${avatarUrl}" alt="Profile" class="avatar-image sidebar">`;
                            }
                        }

                        // Update header avatar
                        const headerAvatar = document.querySelector('#user-avatar-header img');
                        if (headerAvatar) {
                            headerAvatar.src = avatarUrl;
                        } else {
                            const headerContainer = document.querySelector('.user-avatar-header');
                            if (headerContainer && headerContainer.textContent.trim().length <= 2) {
                                headerContainer.innerHTML = `<img src="${avatarUrl}" alt="Profile" class="avatar-image header">`;
                            }
                        }

                        // Trigger avatar update event for other components
                        if (window.avatarUpdater) {
                            window.avatarUpdater.updateAllAvatars(avatarUrl, data.name || '{{ auth()->user()->name }}');
                        }
                    }
                    
                    showNotification('Profile updated successfully!', 'success');
                    
                } catch (error) {
                    console.error('Error updating profile:', error);
                    showNotification(error.message || 'An error occurred while updating your profile', 'error');
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            // Cancel changes button
            const cancelChangesBtn = document.getElementById('cancelChangesBtn');
            if (cancelChangesBtn) {
                cancelChangesBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                        window.location.reload();
                    }
                });
            }

            // Auto-save functionality (optional)
            let autoSaveTimeout;
            document.querySelectorAll('.form-input').forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(autoSaveTimeout);
                    autoSaveTimeout = setTimeout(() => {
                        // Auto-save logic would go here
                        console.log('Auto-saving changes...');
                    }, 2000);
                });
            });

            // User dropdown functionality
            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            if (userDropdownToggle && userDropdownMenu) {
                userDropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userDropdownToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                        userDropdownMenu.classList.remove('active');
                    }
                });
            }

            // Phone number functionality
            const countries = [
                { name: 'Ghana', code: '+233', flag: 'gh' },
                { name: 'Nigeria', code: '+234', flag: 'ng' },
                { name: 'Kenya', code: '+254', flag: 'ke' },
                { name: 'South Africa', code: '+27', flag: 'za' },
                { name: 'United States', code: '+1', flag: 'us' },
                { name: 'United Kingdom', code: '+44', flag: 'gb' },
                { name: 'Canada', code: '+1', flag: 'ca' },
                { name: 'Australia', code: '+61', flag: 'au' },
                { name: 'Germany', code: '+49', flag: 'de' },
                { name: 'France', code: '+33', flag: 'fr' },
                { name: 'India', code: '+91', flag: 'in' },
                { name: 'China', code: '+86', flag: 'cn' },
                { name: 'Japan', code: '+81', flag: 'jp' },
                { name: 'Brazil', code: '+55', flag: 'br' },
                { name: 'Mexico', code: '+52', flag: 'mx' }
            ];

            function initializePhoneInput() {
                const countryCodeBtn = document.getElementById('countryCodeBtn');
                const countryCodeDropdown = document.getElementById('countryCodeDropdown');
                const countryList = document.getElementById('countryList');
                const countrySearch = document.getElementById('countrySearch');
                const selectedFlag = document.getElementById('selectedFlag');
                const selectedCode = document.getElementById('selectedCode');
                const countryCodeInput = document.getElementById('country_code');
                const phoneInput = document.getElementById('phone');

                if (!countryCodeBtn || !countryList) return;

                // Populate country list
                function populateCountries(filteredCountries = countries) {
                    countryList.innerHTML = '';
                    filteredCountries.forEach(country => {
                        const option = document.createElement('div');
                        option.className = 'country-option';
                        option.innerHTML = `
                            <img src="https://flagcdn.com/w20/${country.flag}.png" alt="${country.name}" class="country-flag">
                            <span class="country-name">${country.name}</span>
                            <span class="country-code">${country.code}</span>
                        `;
                        option.addEventListener('click', () => selectCountry(country));
                        countryList.appendChild(option);
                    });
                }

                // Select country
                function selectCountry(country) {
                    selectedFlag.src = `https://flagcdn.com/w20/${country.flag}.png`;
                    selectedFlag.alt = country.name;
                    selectedCode.textContent = country.code;
                    countryCodeInput.value = country.code;
                    countryCodeDropdown.classList.remove('active');
                }

                // Toggle dropdown
                countryCodeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    countryCodeDropdown.classList.toggle('active');
                    if (countryCodeDropdown.classList.contains('active')) {
                        countrySearch.focus();
                    }
                });

                // Search functionality
                if (countrySearch) {
                    countrySearch.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const filtered = countries.filter(country => 
                            country.name.toLowerCase().includes(searchTerm) ||
                            country.code.includes(searchTerm)
                        );
                        populateCountries(filtered);
                    });
                }

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!countryCodeBtn.contains(e.target) && !countryCodeDropdown.contains(e.target)) {
                        countryCodeDropdown.classList.remove('active');
                    }
                });

                // Auto-detect country based on user's IP using ipapi.co
                fetch('https://ipapi.co/json/')
                    .then(response => response.json())
                    .then(data => {
                        if (data.country_code) {
                            // Convert country code to lowercase for matching with flag codes
                            const countryCode = data.country_code.toLowerCase();
                            const detectedCountry = countries.find(c => c.flag === countryCode);
                            if (detectedCountry) {
                                selectCountry(detectedCountry);
                            } else {
                                // Fallback to Ghana if country not found
                                const defaultCountry = countries.find(c => c.flag === 'gh');
                                if (defaultCountry) {
                                    selectCountry(defaultCountry);
                                }
                            }
                        } else {
                            // Fallback to Ghana if no country code in response
                            const defaultCountry = countries.find(c => c.flag === 'gh');
                            if (defaultCountry) {
                                selectCountry(defaultCountry);
                            }
                        }
                    })
                    .catch(error => {
                        console.log('IP geolocation failed, using default country:', error);
                        // Fallback to Ghana on error
                        const defaultCountry = countries.find(c => c.flag === 'gh');
                        if (defaultCountry) {
                            selectCountry(defaultCountry);
                        }
                    });

                // Initialize with default countries
                populateCountries();
            }

            // Initialize phone input
            initializePhoneInput();

            window.removePhoneNumber = function() {
                if (!confirm('Are you sure you want to remove your phone number? This will disable phone-based security features.')) {
                    return;
                }

                const password = prompt('Please enter your current password to confirm this change:');
                if (!password) return;

                fetch('{{ route("profile.phone.remove") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    body: JSON.stringify({
                        current_password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                });
            }

            // Modal Management Functions
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';

                    // Reset modal forms
                    if (modalId === 'phoneUpdateModal') {
                        document.getElementById('phoneUpdateForm').reset();
                    } else if (modalId === 'phoneVerificationModal') {
                        document.getElementById('phoneVerificationForm').reset();
                    } else if (modalId === 'deleteAccountModal') {
                        document.getElementById('deleteAccountForm').reset();
                        document.getElementById('confirmDeleteBtn').disabled = true;
                    } else if (modalId === 'passwordChangeModal') { // Reset password change form
                        document.getElementById('passwordChangeForm').reset();
                    } else if (modalId === 'subscribeUpgradeModal') {
                        // Reset selected plan
                        const selectedPlanCard = document.querySelector('.plan-card-modal.selected');
                        if (selectedPlanCard) {
                            selectedPlanCard.classList.remove('selected');
                        }
                        document.getElementById('confirmPlanSelectionBtn').disabled = true;
                        selectedPlanId = null; // Reset global selected plan ID
                    }
                }
            }

            // Close modal when clicking outside
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal-overlay')) {
                    const modalId = e.target.id;
                    closeModal(modalId);
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const activeModal = document.querySelector('.modal-overlay.active');
                    if (activeModal) {
                        closeModal(activeModal.id);
                    }
                }
            });

            // Initialize modal phone input
            function initializeModalPhoneInput() {
                const modalCountryCodeBtn = document.getElementById('modalCountryCodeBtn');
                const modalCountryCodeDropdown = document.getElementById('modalCountryCodeDropdown');
                const modalCountryList = document.getElementById('modalCountryList');
                const modalCountrySearch = document.getElementById('modalCountrySearch');
                const modalSelectedFlag = document.getElementById('modalSelectedFlag');
                const modalSelectedCode = document.getElementById('modalSelectedCode');
                const modalCountryCodeInput = document.getElementById('modal_country_code');

                if (!modalCountryCodeBtn || !modalCountryList) return;

                // Populate country list for modal
                function populateModalCountries(filteredCountries = countries) {
                    modalCountryList.innerHTML = '';
                    filteredCountries.forEach(country => {
                        const option = document.createElement('div');
                        option.className = 'country-option';
                        option.innerHTML = `
                            <img src="https://flagcdn.com/w20/${country.flag}.png" alt="${country.name}" class="country-flag">
                            <span class="country-name">${country.name}</span>
                            <span class="country-code">${country.code}</span>
                        `;
                        option.addEventListener('click', () => selectModalCountry(country));
                        modalCountryList.appendChild(option);
                    });
                }

                // Select country for modal
                function selectModalCountry(country) {
                    modalSelectedFlag.src = `https://flagcdn.com/w20/${country.flag}.png`;
                    modalSelectedFlag.alt = country.name;
                    modalSelectedCode.textContent = country.code;
                    modalCountryCodeInput.value = country.code;
                    modalCountryCodeDropdown.classList.remove('active');
                }

                // Toggle dropdown for modal
                modalCountryCodeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    modalCountryCodeDropdown.classList.toggle('active');
                    if (modalCountryCodeDropdown.classList.contains('active')) {
                        modalCountrySearch.focus();
                    }
                });

                // Search functionality for modal
                if (modalCountrySearch) {
                    modalCountrySearch.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const filtered = countries.filter(country => 
                            country.name.toLowerCase().includes(searchTerm) || 
                            country.code.includes(searchTerm)
                        );
                        populateModalCountries(filtered);
                    });
                }

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!modalCountryCodeBtn.contains(e.target) && !modalCountryCodeDropdown.contains(e.target)) {
                        modalCountryCodeDropdown.classList.remove('active');
                    }
                });

                // Initialize with default countries
                populateModalCountries();
            }

            // Show notification
            function showNotification(message, type = 'info') {
                const toast = document.getElementById('notificationToast');
                const icon = document.getElementById('toastIcon');
                const messageEl = document.getElementById('toastMessage');

                // Set message
                messageEl.textContent = message;

                // Set icon based on type
                let iconSvg = '';
                toast.className = `notification-toast toast-${type}`;

                switch (type) {
                    case 'success':
                        iconSvg = `<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>`;
                        break;
                    case 'error':
                        iconSvg = `<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>`;
                        break;
                    default:
                        iconSvg = `<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>`;
                }

                icon.innerHTML = iconSvg;

                // Show toast
                toast.classList.add('show');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideNotification();
                }, 5000);
            }

            function hideNotification() {
                const toast = document.getElementById('notificationToast');
                toast.classList.remove('show');
            }

            // Phone Update Modal Functions
            window.showPhoneUpdateModal = function() {
                openModal('phoneUpdateModal');
                initializeModalPhoneInput();
            }

            window.showPhoneVerificationModal = function() {
                openModal('phoneVerificationModal');
            }

            async function submitPhoneUpdate(event) {
                event.preventDefault();
                const submitBtn = event.target;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                const phone = document.getElementById('modal_phone').value.trim();
                const countryCode = document.getElementById('modal_country_code').value;
                const password = document.getElementById('modal_current_password').value;

                if (!phone || !password) {
                    showNotification('Please fill in all required fields', 'error');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';

                try {
                    const response = await fetch('{{ route("profile.phone.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            phone: phone,
                            country_code: countryCode,
                            current_password: password
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        closeModal('phoneUpdateModal');
                        showNotification('Phone number updated successfully!', 'success');

                        // Show verification modal
                        document.getElementById('verificationPhoneNumber').textContent = countryCode + phone;
                        openModal('phoneVerificationModal');
                    } else {
                        showNotification(data.message || 'Failed to update phone number', 'error');
                    }
                } catch (error) {
                    showNotification('An error occurred. Please try again.', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }

            // Phone Verification Modal Functions
            async function submitPhoneVerification(event) {
                event.preventDefault();
                const submitBtn = event.target;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                const code = document.getElementById('verification_code').value.trim();

                if (!code || code.length !== 6) {
                    showNotification('Please enter a valid 6-digit verification code', 'error');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';

                try {
                    const response = await fetch('{{ route("profile.phone.verify") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            verification_code: code
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        closeModal('phoneVerificationModal');
                        showNotification('Phone number verified successfully!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message || 'Invalid verification code', 'error');
                    }
                } catch (error) {
                    showNotification('An error occurred. Please try again.', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }

            window.resendVerificationCode = async function() {
                try {
                    const response = await fetch('{{ route("profile.phone.resend-verification") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        showNotification('Verification code resent successfully!', 'success');
                    } else {
                        showNotification(data.message || 'Failed to resend verification code', 'error');
                    }
                } catch (error) {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            }

            // Password Change Modal Functions
            window.showPasswordChangeModal = function() {
                openModal('passwordChangeModal');
            }

            window.submitPasswordChange = async function(event) {
                event.preventDefault();
                const submitBtn = event.target;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                const currentPassword = document.getElementById('current_password_change').value;
                const newPassword = document.getElementById('new_password').value;
                const newPasswordConfirmation = document.getElementById('new_password_confirmation').value;

                if (!currentPassword || !newPassword || !newPasswordConfirmation) {
                    showNotification('Please fill in all password fields.', 'error');
                    return;
                }

                if (newPassword !== newPasswordConfirmation) {
                    showNotification('New password and confirmation do not match.', 'error');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';

                try {
                    const response = await fetch('{{ route("profile.password.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            current_password: currentPassword,
                            password: newPassword,
                            password_confirmation: newPasswordConfirmation
                        })
                    });
                    const data = await response.json();

                    if (data.success) {
                        closeModal('passwordChangeModal');
                        showNotification(data.message, 'success');
                        document.getElementById('passwordChangeForm').reset(); // Clear the form
                    } else {
                        showNotification(data.message || 'Failed to change password.', 'error');
                    }
                } catch (error) {
                    console.error('Error during password change:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }

            // Delete Account Modal Functions
            window.showDeleteAccountModal = function() {
                openModal('deleteAccountModal');
            }

            // Enable delete button when "DELETE" is typed correctly
            document.addEventListener('DOMContentLoaded', () => {
                const deleteConfirmation = document.getElementById('delete_confirmation');
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

                if (deleteConfirmation && confirmDeleteBtn) {
                    deleteConfirmation.addEventListener('input', function() {
                        confirmDeleteBtn.disabled = this.value !== 'DELETE';
                    });
                }
            });

            window.submitDeleteAccount = async function(event) {
                event.preventDefault();
                const submitBtn = event.target;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                const password = document.getElementById('delete_password').value;
                const confirmation = document.getElementById('delete_confirmation').value;

                if (confirmation !== 'DELETE') {
                    showNotification('Please type "DELETE" to confirm', 'error');
                    return;
                }

                if (!password) {
                    showNotification('Please enter your current password', 'error');
                    return;
                }


                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';

                try {
                    const response = await fetch('{{ route("profile.destroy") }}', { // Corrected route name
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            password: password // Changed to 'password' as per destroy method validation
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        closeModal('deleteAccountModal');
                        showNotification('Account deleted successfully. Redirecting...', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect || '/'; // Redirect to home or login page
                        }, 2000);
                    } else {
                        showNotification(data.message || 'Failed to delete account', 'error');
                    }
                } catch (error) {
                    showNotification('An error occurred. Please try again.', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }

            // Global variable to store selected plan ID for subscription/upgrade
            let selectedPlanId = null;

            // Subscription/Upgrade Plan Modal Functions
            window.showSubscriptionPlans = async function() {
                // Fetch available plans from the server
                try {
                    const response = await fetch('{{ route("api.pricing-plans") }}'); // API route for plans
                    const plans = await response.json();

                    const planSelectionGrid = document.getElementById('planSelectionGrid');
                    planSelectionGrid.innerHTML = ''; // Clear previous plans

                    if (plans.success && plans.plans.length > 0) {
                        plans.plans.forEach(plan => {
                            const planCard = document.createElement('div');
                            planCard.className = 'plan-card-modal';
                            planCard.dataset.planId = plan.id;
                            planCard.innerHTML = `
                                <div class="plan-name">${plan.name}</div>
                                <div class="plan-price">${plan.formatted_price}</div>
                                <div class="plan-period">/${plan.period}</div>
                                <p class="plan-description">${plan.description}</p>
                                <ul class="plan-features-list">
                                    ${plan.features.map(feature => `
                                        <li>
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            ${feature}
                                        </li>
                                    `).join('')}
                                </ul>
                                <div class="selected-indicator">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            `;
                            planCard.addEventListener('click', () => selectPlan(plan.id, planCard));
                            planSelectionGrid.appendChild(planCard);
                        });
                    } else {
                        planSelectionGrid.innerHTML = '<p class="text-center text-gray-500">No pricing plans available.</p>';
                    }

                    document.getElementById('subscribeUpgradeModalTitle').textContent = 'Choose Your Plan';
                    document.getElementById('subscribeUpgradeModalDescription').textContent = 'Unlock premium features and content with a subscription plan.';
                    openModal('subscribeUpgradeModal');
                } catch (error) {
                    console.error('Error fetching plans:', error);
                    showNotification('Failed to load pricing plans. Please try again.', 'error');
                }
            }

            window.showUpgradeModal = function() {
                // This function is called when a user on a trial wants to upgrade.
                // It should behave similarly to showSubscriptionPlans but might highlight upgrade options.
                // For simplicity, we'll reuse showSubscriptionPlans for now.
                showSubscriptionPlans();
                document.getElementById('subscribeUpgradeModalTitle').textContent = 'Upgrade Your Plan';
                document.getElementById('subscribeUpgradeModalDescription').textContent = 'Select a new plan to upgrade your subscription.';
            }

            window.showChangePlanModal = function() {
                // This function is called when a user with an active subscription wants to change plans.
                // It should filter out the current plan and allow selection of others.
                showSubscriptionPlans(); // Reusing for now, but could be more specific
                document.getElementById('subscribeUpgradeModalTitle').textContent = 'Change Your Plan';
                document.getElementById('subscribeUpgradeModalDescription').textContent = 'Switch to a different plan that better suits your needs.';
            }

            function selectPlan(planId, planCard) {
                const currentSelected = document.querySelector('.plan-card-modal.selected');
                if (currentSelected) {
                    currentSelected.classList.remove('selected');
                }
                planCard.classList.add('selected');
                selectedPlanId = planId;
                document.getElementById('confirmPlanSelectionBtn').disabled = false;
            }

            window.confirmPlanSelection = async function(event) {
                if (!selectedPlanId) {
                    showNotification('Please select a plan first.', 'error');
                    return;
                }

                const submitBtn = event.target;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';

                try {
                    const response = await fetch('{{ route("api.subscribe") }}', { // API route for subscription
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ plan_id: selectedPlanId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        closeModal('subscribeUpgradeModal');
                        showNotification(data.message || 'Subscription successful!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message || 'Failed to process subscription.', 'error');
                    }
                } catch (error) {
                    console.error('Error during plan selection:', error);
                    showNotification('An error occurred during subscription. Please try again.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            }

            // Manage Subscription Modal Functions
            window.showManageSubscriptionModal = async function() {
                try {
                    const response = await fetch('{{ route("api.current-subscription") }}'); // API to get current subscription details
                    const subscriptionData = await response.json();

                    if (subscriptionData.success && subscriptionData.subscription) {
                        const sub = subscriptionData.subscription;
                        const plan = sub.pricing_plan;

                        document.getElementById('currentPlanName').textContent = plan.name;
                        document.getElementById('currentPlanPrice').textContent = `${plan.formatted_price}/${plan.period}`;
                        
                        const statusBadge = document.getElementById('currentPlanStatus');
                        statusBadge.textContent = sub.status.charAt(0).toUpperCase() + sub.status.slice(1);
                        statusBadge.className = `status-badge status-${sub.status}`;

                        if (sub.is_in_trial) {
                            document.getElementById('currentPlanTrialInfo').textContent = `Your trial expires in ${sub.trial_days_remaining} days on ${sub.trial_ends_at_formatted}`;
                            document.getElementById('currentPlanExpiry').textContent = ''; // Clear expiry if trial
                        } else if (sub.expires_at_formatted) {
                            document.getElementById('currentPlanExpiry').textContent = `Next Billing Date: ${sub.expires_at_formatted} (${sub.days_remaining} days)`;
                            document.getElementById('currentPlanTrialInfo').textContent = ''; // Clear trial if not trial
                        } else {
                            document.getElementById('currentPlanExpiry').textContent = '';
                            document.getElementById('currentPlanTrialInfo').textContent = '';
                        }

                        // Populate billing history
                        const billingHistoryContent = document.getElementById('billingHistoryContent');
                        if (sub.billing_history && sub.billing_history.length > 0) {
                            let tableHtml = `
                                <table class="billing-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th class="text-right">Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                            sub.billing_history.forEach(item => {
                                tableHtml += `
                                    <tr>
                                        <td>${item.date}</td>
                                        <td>${item.description}</td>
                                        <td class="text-right">${item.currency} ${item.amount}</td>
                                        <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
                                    </tr>
                                `;
                            });
                            tableHtml += `</tbody></table>`;
                            billingHistoryContent.innerHTML = tableHtml;
                        } else {
                            billingHistoryContent.innerHTML = '<p class="no-history-message">No billing history available.</p>';
                        }

                        openModal('manageSubscriptionModal');
                    } else {
                        showNotification(subscriptionData.message || 'Failed to load subscription details.', 'error');
                    }
                } catch (error) {
                    console.error('Error fetching subscription details:', error);
                    showNotification('An error occurred while loading subscription details. Please try again.', 'error');
                }
            }

            window.confirmCancelSubscription = async function() {
                if (!confirm('Are you sure you want to cancel your subscription? Your access will continue until the end of the current billing period.')) {
                    return;
                }

                try {
                    const response = await fetch('{{ route("api.cancel-subscription") }}', { // API to cancel subscription
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({}) // No specific data needed for cancellation
                    });
                    const data = await response.json();

                    if (data.success) {
                        closeModal('manageSubscriptionModal');
                        showNotification(data.message || 'Subscription cancelled successfully!', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message || 'Failed to cancel subscription.', 'error');
                    }
                } catch (error) {
                    console.error('Error during subscription cancellation:', error);
                    showNotification('An error occurred during cancellation. Please try again.', 'error');
                }
            }

            // Initialize modal functionality when page loads
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize the phone input in the modal
                initializeModalPhoneInput();
            });

            // Bind events without inline handlers to comply with CSP
            const $ = (sel, ctx = document) => ctx.querySelector(sel);
            const on = (el, ev, fn) => { if (el) el.addEventListener(ev, fn, false); };

            // Avatar edit -> open file picker (REMOVED: duplicate event listener already exists in first script block)

            // Country flag update
            const countrySelect = $('#country');
            if (countrySelect) {
                on(countrySelect, 'change', function() {
                    if (typeof window.updateFlag === 'function') {
                        window.updateFlag(this);
                    } else {
                        const flagDisplay = this.parentElement.querySelector('.flag-display img');
                        const opt = this.options[this.selectedIndex];
                        const code = opt && opt.getAttribute('data-code');
                        if (code && flagDisplay) flagDisplay.src = `https://flagcdn.com/w20/${code.toLowerCase()}.png`;
                    }
                });
            }

            // Top nav actions
            on($('#changePasswordBtn'), 'click', () => (window.showPasswordChangeModal ? showPasswordChangeModal() : (window.showModal && showModal('passwordChangeModal'))));
            on($('#deleteAccountBtn'), 'click', () => (window.showDeleteAccountModal ? showDeleteAccountModal() : (window.showModal && showModal('deleteAccountModal'))));

            // Helper to bind by id to a global function name
            const bindBtn = (id, fnName) => {
                const el = $('#' + id);
                if (!el) return;
                on(el, 'click', (e) => {
                    if (fnName.startsWith('submit')) e.preventDefault();
                    if (typeof window[fnName] === 'function') {
                        window[fnName](e);
                    } else {
                        switch (fnName) {
                            case 'showPhoneVerificationModal':
                                return window.showModal && showModal('phoneVerificationModal');
                            case 'showPhoneUpdateModal':
                                return window.showModal && showModal('phoneUpdateModal');
                            case 'confirmCancelSubscription':
                            case 'confirmPlanSelection':
                            case 'submitPhoneUpdate':
                            case 'submitPhoneVerification':
                            case 'submitPasswordChange':
                            case 'submitDeleteAccount':
                                console.warn(fnName + ' is not implemented');
                                break;
                            default:
                                break;
                        }
                    }
                });
            };

            // Phone actions
            bindBtn('verifyCurrentNumberBtn', 'showPhoneVerificationModal');
            bindBtn('updateNumberBtn', 'showPhoneUpdateModal');
            bindBtn('addPhoneBtn', 'showPhoneUpdateModal');
            bindBtn('removePhoneBtn', 'removePhoneNumber');

            // Phone update modal
            bindBtn('submitPhoneUpdateBtn', 'submitPhoneUpdate');
            on($('#cancelPhoneUpdateBtn'), 'click', () => window.closeModal && closeModal('phoneUpdateModal'));

            // Phone verification modal
            bindBtn('submitPhoneVerifyBtn', 'submitPhoneVerification');
            on($('#cancelPhoneVerifyBtn'), 'click', () => window.closeModal && closeModal('phoneVerificationModal'));
            on($('#resendVerificationBtn'), 'click', () => window.resendVerificationCode && resendVerificationCode());

            // Password change modal
            bindBtn('submitPasswordChangeBtn', 'submitPasswordChange');
            on($('#cancelPasswordChangeBtn'), 'click', () => window.closeModal && closeModal('passwordChangeModal'));

            // Delete account modal
            bindBtn('confirmDeleteBtn', 'submitDeleteAccount');
            on($('#cancelDeleteAccountBtn'), 'click', () => window.closeModal && closeModal('deleteAccountModal'));

            // Subscription actions
            on($('#manageSubscriptionBtn'), 'click', () => window.showManageSubscriptionModal ? showManageSubscriptionModal() : (window.showModal && showModal('manageSubscriptionModal')));
            on($('#subscribeNowBtn'), 'click', () => window.showSubscriptionPlans ? showSubscriptionPlans() : (window.showModal && showModal('subscribeUpgradeModal')));
            on($('#cancelSubscribeUpgradeBtn'), 'click', () => window.closeModal && closeModal('subscribeUpgradeModal'));
            bindBtn('confirmPlanSelectionBtn', 'confirmPlanSelection');
            on($('#closeManageSubscriptionBtn'), 'click', () => window.closeModal && closeModal('manageSubscriptionModal'));
            on($('#changePlanBtn'), 'click', () => window.showChangePlanModal ? showChangePlanModal() : (window.showModal && showModal('subscribeUpgradeModal')));
            on($('#confirmCancelSubscriptionBtn'), 'click', () => window.confirmCancelSubscription && confirmCancelSubscription());

            // Toast close
            on($('#toastCloseBtn'), 'click', hideNotification);

            // Cancel changes
            on($('#cancelChangesBtn'), 'click', function() {
                if (typeof window.cancelChanges === 'function') return cancelChanges();
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    const form = $('#profileForm');
                    if (form) form.reset();
                }
            });

            // User dropdown toggle
            const userToggle = $('#userDropdownToggle');
            const userMenu = $('#userDropdownMenu');
            on(userToggle, 'click', () => userMenu && userMenu.classList.toggle('active'));
            document.addEventListener('click', (e) => {
                if (!userMenu || !userToggle) return;
                if (!userMenu.contains(e.target) && !userToggle.contains(e.target)) userMenu.classList.remove('active');
            });
        });
        
        function hideNotification() {
            const toast = document.getElementById('notificationToast');
            if (toast) toast.classList.remove('show');
        }
    </script>
</body>
</html>
