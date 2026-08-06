<?php
$path = __DIR__ . '/../resources/views/dashboard/digilearn.blade.php';
$content = file_get_contents($path);

// Replace the body content
$bodyStart = strpos($content, '<body>') + strlen('<body>');
$scriptStart = strpos($content, '<script nonce="{{ request()->attributes->get(\'csp_nonce\') }}">', $bodyStart);

$newBody = "
<!-- Sidebar Overlay for Mobile -->
<div class=\"sidebar-overlay\" id=\"sidebarOverlay\"></div>

<div class=\"main-container\">
    @yield('sidebar')
    @include('components.dashboard-header')

    <!-- Search/Filter Bar -->
    <div class=\"filter-bar\" id=\"filterBar\">
        @yield('filter-bar')
    </div>

    <div class=\"subjects-filter-container\">
        @yield('subjects-filter')
    </div>

    <!-- Main Content -->
    <main class=\"main-content\">
        @yield('content')
    </main>
</div>
";

$content = substr($content, 0, $bodyStart) . $newBody . substr($content, $scriptStart);

// Remove digilearn specific initializers
$content = str_replace('initializeInfiniteScroll();', '', $content);
$content = str_replace('initializeContextFilter();', '', $content);
$content = str_replace('initializeSearch();', '', $content);

// Remove video facade
$content = preg_replace('/if \(typeof window\.videoFacadeManager !==.*?initializeAllSaveButtons\(\);\n        }\);/s', '});', $content);

// Remove functions
$content = preg_replace('/async function initializeAllSaveButtons\(\).*?}\n        }/s', '', $content);
$content = preg_replace('/window\.addEventListener\(\'pageshow\'.*?}\);/s', '', $content);
$content = preg_replace('/function escapeHTML\(str\).*?showSearchError\(\'Search failed\. Please try again\.\'\);\n            }\n        }/s', '', $content);
$content = preg_replace('/function updateLessonGrid\(lessons, query\).*?grid\.innerHTML = html;.*?}/s', '', $content);
$content = preg_replace('/function restoreOriginalLessons\(\).*?}/s', '', $content);
$content = preg_replace('/function showSearchError\(message\).*?}/s', '', $content);
$content = preg_replace('/let isSearching = false;/s', '', $content);

// Remove includes
$content = str_replace("@include('partials._upgrade_modal')", "", $content);
$content = str_replace("@include('components.search-autocomplete')", "", $content);
$content = str_replace("<x-skeleton-loader type=\"digilearn\" />", "", $content);

// Change offset
$content = str_replace("\$mainContentTopOffset = \$isPrimaryLevel ? '205px' : '255px';", "\$mainContentTopOffset = '145px';", $content);
$content = str_replace("\$isPrimaryLevel = str_contains(strtolower(\$currentLevelGroup), 'primary') || str_contains(strtolower(\$currentLevelGroup), 'grade');", "", $content);

file_put_contents(__DIR__ . '/../resources/views/layouts/tutors-layout.blade.php', $content);
echo 'Done';
