@php
    $formatClass = strtolower($doc->resolved_format ?? 'pdf');
    $isPpt = in_array($formatClass, ['ppt', 'pptx']);
    $icon = $isPpt ? 'fa-chart-bar' : 'fa-clock';
    $coverImage = $doc->cover_image_url ?? null;
    $subjectName = $doc->resolved_subject ?? 'General Study';
    $gradeName = $doc->resolved_grade ?? '';
    $docTargetUrl = route('dashboard.library.document', $doc->id);
@endphp

<a href="{{ $docTargetUrl }}" class="document-card" data-doc-id="{{ $doc->id }}" aria-label="{{ $doc->title }}">
    <!-- Left Column: Document Cover Thumbnail Frame -->
    <div class="document-cover-frame">
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
    </div>

    <!-- Right Column: Text Content & Metadata -->
    <div class="document-card-body">
        <div>
            <!-- Top Meta Row (Format pill + File size + Subtle arrow) -->
            <div class="document-card-top">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="doc-format-pill {{ $formatClass }}">
                        {{ $doc->resolved_format ?? 'PDF' }}
                    </span>
                    <span class="doc-file-size">
                        {{ $doc->formatted_size ?? 'N/A' }}
                    </span>
                </div>
                <div class="doc-card-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>

            <!-- Document Title -->
            <h3 class="document-card-title" title="{{ $doc->title }}">
                {{ $doc->title }}
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
        </div>

        <!-- Bottom Meta Row (Pages / Slides) -->
        <div class="document-meta-row" style="margin-bottom: 0;">
            <i class="fas {{ $icon }}"></i>
            <span>{{ $doc->meta_count }} {{ $doc->meta_count_label }}</span>
        </div>
    </div>
</a>
