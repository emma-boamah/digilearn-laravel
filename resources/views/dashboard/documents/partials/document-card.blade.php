@php
    $formatClass = strtolower($doc->resolved_format ?? 'pdf');
    $isPpt = in_array($formatClass, ['ppt', 'pptx']);
    $actionLabel = $isPpt ? 'View Slides' : 'Open Document';
    $icon = $isPpt ? 'fa-chart-bar' : 'fa-clock';
    $coverImage = $doc->cover_image_url ?? null;
    $subjectName = $doc->resolved_subject ?? 'General Study';
    $gradeName = $doc->resolved_grade ?? '';
    $docTargetUrl = route('dashboard.library.document', $doc->seo_url);
@endphp

<div class="document-card">
    <!-- Left Column: Document Cover Thumbnail Frame -->
    <a href="{{ $docTargetUrl }}" class="document-cover-frame" aria-label="{{ $doc->title }}">
        @if(!empty($coverImage))
            <img src="{{ $coverImage }}" 
                 alt="{{ $doc->title }}" 
                 class="document-cover-img"
                 loading="lazy"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="document-cover-fallback" style="display: none;">
                <div class="cover-fallback-badge">{{ $doc->resolved_format ?? 'PDF' }}</div>
                <i class="fas {{ $isPpt ? 'fa-file-powerpoint' : 'fa-file-pdf' }} cover-fallback-icon"></i>
                <span class="cover-fallback-title">{{ Str::limit($doc->title, 35) }}</span>
            </div>
        @else
            <div class="document-cover-fallback">
                <div class="cover-fallback-badge">{{ $doc->resolved_format ?? 'PDF' }}</div>
                <i class="fas {{ $isPpt ? 'fa-file-powerpoint' : 'fa-file-pdf' }} cover-fallback-icon"></i>
                <span class="cover-fallback-title">{{ Str::limit($doc->title, 35) }}</span>
            </div>
        @endif
    </a>

    <!-- Right Column: Text Content & Actions -->
    <div class="document-card-body">
        <div>
            <!-- Top Meta Row (Format pill + File size) -->
            <div class="document-card-top">
                <span class="doc-format-pill {{ $formatClass }}">
                    {{ $doc->resolved_format ?? 'PDF' }}
                </span>
                <span class="doc-file-size">
                    {{ $doc->formatted_size ?? 'N/A' }}
                </span>
            </div>

            <!-- Document Title -->
            <h3 class="document-card-title" title="{{ $doc->title }}">
                <a href="{{ $docTargetUrl }}">
                    {{ $doc->title }}
                </a>
            </h3>

            <!-- Tags Row (Grade / Subject) -->
            <div class="document-tags-row">
                @if($gradeName)
                    <span class="doc-grade-tag">
                        {{ $gradeName }}
                    </span>
                @endif
                @if($subjectName && $subjectName !== 'General Study')
                    <span class="doc-subject-tag">
                        {{ $subjectName }}
                    </span>
                @endif
            </div>

            <!-- Meta Row (Pages / Slides) -->
            <div class="document-meta-row">
                <i class="fas {{ $icon }}"></i>
                <span>{{ $doc->meta_count }} {{ $doc->meta_count_label }}</span>
            </div>
        </div>

        <!-- Action Button -->
        <a href="{{ $docTargetUrl }}" class="doc-action-btn">
            <i class="fas fa-play text-xs"></i>
            <span>{{ $actionLabel }}</span>
        </a>
    </div>
</div>
