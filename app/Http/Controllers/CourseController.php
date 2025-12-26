<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Course;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request)
    {
        $query = Course::with(['creator', 'videos', 'documents', 'quizzes']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }

        // Filter by grade level
        if ($request->filled('grade_level') && $request->grade_level != '') {
            $query->where('grade_level', $request->grade_level);
        }

        // Filter by subject
        if ($request->filled('subject') && $request->subject != '') {
            $query->where('subject', $request->subject);
        }

        // Filter by status
        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by featured
        if ($request->filled('is_featured') && $request->is_featured != '') {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get filter options
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $subjects = Course::select('subject')->whereNotNull('subject')->distinct()->pluck('subject')->toArray();

        // Course statistics
        $totalCourses = Course::count();
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();
        $featuredCourses = Course::where('is_featured', true)->count();

        return view('admin.content.courses.index', compact(
            'courses',
            'gradeLevels',
            'subjects',
            'totalCourses',
            'publishedCourses',
            'draftCourses',
            'featuredCourses'
        ));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $videos = Video::approved()->select('id', 'title')->get();
        $documents = Document::select('id', 'title')->get();
        $quizzes = Quiz::select('id', 'title')->get();

        return view('admin.content.courses.create', compact('gradeLevels', 'videos', 'documents', 'quizzes'));
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_level' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_featured' => 'boolean',
            'status' => 'required|in:draft,published,archived',
            'videos' => 'nullable|array',
            'videos.*' => 'exists:videos,id',
            'documents' => 'nullable|array',
            'documents.*' => 'exists:documents,id',
            'quizzes' => 'nullable|array',
            'quizzes.*' => 'exists:quizzes,id',
        ]);

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'grade_level' => $request->grade_level,
            'subject' => $request->subject,
            'thumbnail_path' => $thumbnailPath,
            'price' => $request->price ?? 0,
            'is_featured' => $request->has('is_featured'),
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        // Attach content with ordering
        if ($request->filled('videos')) {
            foreach ($request->videos as $index => $videoId) {
                $course->videos()->attach($videoId, ['order' => $index + 1]);
            }
        }

        if ($request->filled('documents')) {
            foreach ($request->documents as $index => $documentId) {
                $course->documents()->attach($documentId, ['order' => $index + 1]);
            }
        }

        if ($request->filled('quizzes')) {
            foreach ($request->quizzes as $index => $quizId) {
                $course->quizzes()->attach($quizId, ['order' => $index + 1]);
            }
        }

        return redirect()->route('admin.content.courses.index')->with('success', 'Course created successfully!');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load(['creator', 'videos', 'documents', 'quizzes']);
        $stats = $course->getStats();

        return view('admin.content.courses.show', compact('course', 'stats'));
    }

    /**
     * Show the form for editing the course.
     */
    public function edit(Course $course)
    {
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3'];
        $videos = Video::approved()->select('id', 'title')->get();
        $documents = Document::select('id', 'title')->get();
        $quizzes = Quiz::select('id', 'title')->get();

        // Get selected content IDs
        $selectedVideos = $course->videos->pluck('id')->toArray();
        $selectedDocuments = $course->documents->pluck('id')->toArray();
        $selectedQuizzes = $course->quizzes->pluck('id')->toArray();

        return view('admin.content.courses.edit', compact(
            'course',
            'gradeLevels',
            'videos',
            'documents',
            'quizzes',
            'selectedVideos',
            'selectedDocuments',
            'selectedQuizzes'
        ));
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_level' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_featured' => 'boolean',
            'status' => 'required|in:draft,published,archived',
            'videos' => 'nullable|array',
            'videos.*' => 'exists:videos,id',
            'documents' => 'nullable|array',
            'documents.*' => 'exists:documents,id',
            'quizzes' => 'nullable|array',
            'quizzes.*' => 'exists:quizzes,id',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $thumbnailPath = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $course->thumbnail_path = $thumbnailPath;
        }

        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'grade_level' => $request->grade_level,
            'subject' => $request->subject,
            'price' => $request->price ?? 0,
            'is_featured' => $request->has('is_featured'),
            'status' => $request->status,
        ]);

        // Sync content with ordering
        $course->videos()->detach();
        if ($request->filled('videos')) {
            foreach ($request->videos as $index => $videoId) {
                $course->videos()->attach($videoId, ['order' => $index + 1]);
            }
        }

        $course->documents()->detach();
        if ($request->filled('documents')) {
            foreach ($request->documents as $index => $documentId) {
                $course->documents()->attach($documentId, ['order' => $index + 1]);
            }
        }

        $course->quizzes()->detach();
        if ($request->filled('quizzes')) {
            foreach ($request->quizzes as $index => $quizId) {
                $course->quizzes()->attach($quizId, ['order' => $index + 1]);
            }
        }

        return redirect()->route('admin.content.courses.index')->with('success', 'Course updated successfully!');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        // Delete thumbnail
        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }

        // Detach all relationships
        $course->videos()->detach();
        $course->documents()->detach();
        $course->quizzes()->detach();

        $course->delete();

        return redirect()->route('admin.content.courses.index')->with('success', 'Course deleted successfully!');
    }

    /**
     * Toggle course featured status.
     */
    public function toggleFeature(Course $course)
    {
        $course->is_featured = !$course->is_featured;
        $course->save();

        return back()->with('success', 'Course feature status updated.');
    }

    /**
     * Change course status.
     */
    public function changeStatus(Request $request, Course $course)
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $course->update(['status' => $request->status]);

        return back()->with('success', 'Course status updated successfully.');
    }

    /**
     * Get course content for AJAX requests.
     */
    public function getContent(Course $course)
    {
        $course->load(['videos', 'documents', 'quizzes']);

        return response()->json([
            'videos' => $course->videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'duration' => $video->duration_seconds,
                    'status' => $video->status,
                ];
            }),
            'documents' => $course->documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file_size' => $document->getFormattedFileSize(),
                ];
            }),
            'quizzes' => $course->quizzes->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                ];
            }),
        ]);
    }
}