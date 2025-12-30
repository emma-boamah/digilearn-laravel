<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // First page - Document preview/selection
    public function viewDocument($lessonId, $type)
    {
        // Check if this is an AJAX request to check document existence
        if (request()->ajax() || request()->wantsJson()) {
            // For AJAX requests, check level group selection first
            if (!session('selected_level_group')) {
                return response()->json([
                    'exists' => false,
                    'error' => 'level_required',
                    'message' => 'Please select your grade level first.'
                ]);
            }

            $document = $this->getDocumentForLesson($lessonId, $type);
            return response()->json([
                'exists' => $document !== null && !empty($document),
                'document' => $document
            ]);
        }

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevelGroup = session('selected_level_group');

        // Get lesson data from database (similar to DashboardController approach)
        $gradeLevels = $this->getGradeLevelForLevelGroup($selectedLevelGroup);
        $videos = \App\Models\Video::approved()->whereIn('grade_level', $gradeLevels)->with('documents')->get();
        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return view('dashboard.document-viewer', [
                'lesson' => null,
                'document' => null,
                'selectedLevelGroup' => $selectedLevelGroup,
                'type' => $type,
                'error' => 'Lesson not found.'
            ]);
        }

        // Get basic document info for preview
        $document = $this->getDocumentForLesson($lessonId, $type);

        // Ensure document is always an array for consistent view handling
        if (!$document) {
            $document = []; // Empty array instead of null
        } elseif (!is_array($document)) {
            $document = [$document]; // Wrap single document in array
        }

        // Return the simple preview page
        return view('dashboard.document-viewer', compact('lesson', 'document', 'selectedLevelGroup', 'type'));
    }

    // Second page - Document content viewer
    public function viewDocumentContent($lessonId, $type)
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevelGroup = session('selected_level_group');

        // Get lesson data from database (similar to DashboardController approach)
        $gradeLevels = $this->getGradeLevelForLevelGroup($selectedLevelGroup);
        $videos = \App\Models\Video::approved()->whereIn('grade_level', $gradeLevels)->with('documents')->get();
        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Get full document data with content
        $document = $this->getDocumentContentForLesson($lessonId, $type);

        if (!$document) {
            return redirect()->route('dashboard.lesson.document', [$lessonId, $type])
                ->with('error', 'Document content not found.');
        }

        // Record document view engagement for recommendation system
        if (auth()->check()) {
            \App\Models\UserEngagement::record(
                auth()->id(),
                'document',
                $lessonId,
                'view',
                0, // duration tracked separately
                [
                    'title' => $document['title'] ?? 'Document',
                    'subject' => $lesson['subject'] ?? 'General',
                    'type' => $type,
                    'lesson_id' => $lessonId,
                ]
            );
        }

        // Return the full content viewer page
        return view('dashboard.document-content-viewer', compact('lesson', 'document', 'selectedLevelGroup', 'type'));
    }

    private function getGradeLevelForLevelGroup($levelGroup)
    {
        // Map level groups to all possible grade levels they contain
        $gradeLevels = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => ['University']
        ];

        return $gradeLevels[$levelGroup] ?? ['SHS 1'];
    }

    // Basic document info for preview page
    private function getDocumentForLesson($lessonId, $type)
    {
        // Query database for documents related to this video (lesson)
        $document = \App\Models\Document::where('video_id', $lessonId)->first();

        if (!$document) {
            return null;
        }

        // Determine file type based on file extension
        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $documentType = $fileExtension === 'pdf' ? 'pdf' : 'ppt';

        // Only return document if it matches the requested type
        if ($documentType !== $type) {
            return null;
        }

        // Return basic document info for preview
        if ($type === 'pdf') {
            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_size' => $this->formatFileSize($document->file_path),
                'pages' => 1 // Placeholder, could be calculated from actual file
            ];
        }

        // For PPT documents
        if ($type === 'ppt') {
            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_size' => $this->formatFileSize($document->file_path),
                'slides' => 1 // Placeholder, could be calculated from actual file
            ];
        }

        return null;
    }

    // Full document content for content viewer page
    private function getDocumentContentForLesson($lessonId, $type)
    {
        // Check if this is a user-created PPT
        if ($type === 'ppt' && request()->has('ppt_id')) {
            $pptId = request()->get('ppt_id');
            $presentations = session('user_presentations', []);

            if (isset($presentations[$lessonId][$pptId])) {
                return $presentations[$lessonId][$pptId];
            }
        }

        // Query database for documents related to this video (lesson)
        $document = \App\Models\Document::where('video_id', $lessonId)->first();

        if (!$document) {
            return [];
        }

        // Determine file type based on file extension
        $fileExtension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $documentType = $fileExtension === 'pdf' ? 'pdf' : 'ppt';

        // Only return document if it matches the requested type
        if ($documentType !== $type) {
            return [];
        }

        // For PDF documents, return structured content
        if ($type === 'pdf') {
            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_size' => $this->formatFileSize($document->file_path),
                'pages' => [
                    [
                        'number' => 1,
                        'title' => 'Document Content',
                        'content' => $document->description ?: 'Document content not available for preview.'
                    ]
                ]
            ];
        }

        // For PPT documents, return slide structure
        if ($type === 'ppt') {
            return [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'file_size' => $this->formatFileSize($document->file_path),
                'subject' => $document->video->subject ?? 'General',
                'slides' => [
                    [
                        'number' => 1,
                        'title' => $document->title,
                        'subtitle' => $document->video->subject ?? 'General',
                        'type' => 'title'
                    ],
                    [
                        'number' => 2,
                        'title' => 'Document Overview',
                        'content' => $document->description ?: 'Document slides not available for preview.',
                        'type' => 'definition'
                    ]
                ]
            ];
        }

        return [];
    }

    // Create new PPT
    public function createPpt($lessonId)
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevelGroup = session('selected_level_group');

        // Get lesson data from database (similar to DashboardController approach)
        $videos = \App\Models\Video::approved()->where('grade_level', $this->getGradeLevelForLevelGroup($selectedLevelGroup))->with('documents')->get();
        $lesson = $videos->firstWhere('id', (int)$lessonId);

        // Convert to array format expected by the view
        if ($lesson) {
            $lesson = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject->name ?? 'Subject',
                'duration' => $lesson->duration ?? 'Unknown',
                'instructor' => $lesson->instructor ?? 'Unknown',
                'year' => $lesson->year ?? '2025',
                'video_url' => $lesson->getVideoUrl(),
                'thumbnail' => $lesson->thumbnail ?? null,
            ];
        }

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Create a new empty PPT structure
        $newPpt = [
            'id' => uniqid(),
            'title' => 'New Presentation',
            'subject' => $lesson['subject'],
            'slides' => [
                [
                    'number' => 1,
                    'type' => 'title',
                    'title' => 'New Presentation',
                    'subtitle' => $lesson['subject'],
                    'content' => ''
                ]
            ]
        ];

        return view('dashboard.ppt-creator', compact('lesson', 'newPpt', 'selectedLevelGroup'));
    }

    // Store new PPT
    public function storePpt(Request $request, $lessonId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slides' => 'required|array|min:1',
            'slides.*.type' => 'required|in:title,definition,list',
            'slides.*.title' => 'required|string',
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        // In a real application, you would save this to your database
        // For now, we'll store it in session
        $presentations = session('user_presentations', []);
        $pptId = uniqid();
        
        $presentations[$lessonId][$pptId] = [
            'id' => $pptId,
            'title' => $request->title,
            'slides' => $request->slides,
            'created_at' => now(),
            'updated_at' => now()
        ];

        session(['user_presentations' => $presentations]);

        return response()->json([
            'success' => true,
            'message' => 'Presentation created successfully!',
            'ppt_id' => $pptId,
            'redirect_url' => route('dashboard.lesson.document.content', [$lessonId, 'ppt']) . '?ppt_id=' . $pptId
        ]);
    }

    // Update existing PPT
    public function updatePpt(Request $request, $lessonId, $pptId)
    {
        $request->validate([
            'slides' => 'required|array'
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $presentations = session('user_presentations', []);
        
        if (isset($presentations[$lessonId][$pptId])) {
            $presentations[$lessonId][$pptId]['slides'] = $request->slides;
            $presentations[$lessonId][$pptId]['updated_at'] = now();
            
            session(['user_presentations' => $presentations]);

            return response()->json([
                'success' => true,
                'message' => 'Presentation updated successfully!'
            ]);
        }

        return response()->json(['error' => 'Presentation not found'], 404);
    }

    public function saveDocumentChanges(Request $request, $lessonId, $type)
    {
        $request->validate([
            'changes' => 'required|array'
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        // In a real application, you would save these changes to your database
        // For now, we'll just store them in the session
        $documentChanges = session('document_changes', []);
        $documentChanges[$lessonId][$type] = $request->changes;
        session(['document_changes' => $documentChanges]);

        return response()->json([
            'success' => true,
            'message' => 'Document changes saved successfully'
        ]);
    }

    private function formatFileSize($filePath)
    {
        // Check if file exists
        if (!file_exists(storage_path('app/public/' . $filePath))) {
            return 'Unknown';
        }

        $bytes = filesize(storage_path('app/public/' . $filePath));

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}