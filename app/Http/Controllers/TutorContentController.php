<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Video;
use App\Models\Document;
use App\Models\Quiz;
use App\Models\Subject;
use App\Services\VideoSourceService;
use App\Services\VimeoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TutorContentController extends Controller
{
    /**
     * List tutor's courses and content portfolio.
     */
    public function index()
    {
        $user = Auth::user();

        $courses = Course::with(['videos', 'documents', 'quizzes'])
            ->where(function ($q) use ($user) {
                $q->where('tutor_id', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->latest()
            ->paginate(10);

        $videosCount = Video::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->count();
        $documentsCount = Document::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->count();
        $quizzesCount = Quiz::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->count();

        return view('tutors.content-studio', compact('user', 'courses', 'videosCount', 'documentsCount', 'quizzesCount'));
    }

    /**
     * Show Course Creation Wizard form.
     */
    public function createCourse()
    {
        $subjects = Subject::orderBy('name')->get();
        $gradeLevels = ['Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6', 'JHS 1', 'JHS 2', 'JHS 3', 'SHS 1', 'SHS 2', 'SHS 3', 'University'];

        $user = Auth::user();
        $videos = Video::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->get();
        $documents = Document::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->get();
        $quizzes = Quiz::where('uploaded_by', $user->id)->orWhere('tutor_id', $user->id)->get();

        return view('tutors.course-builder', compact('subjects', 'gradeLevels', 'videos', 'documents', 'quizzes'));
    }

    /**
     * Store new tutor course.
     */
    public function storeCourse(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_level' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,published',
            'videos' => 'nullable|array',
            'videos.*' => 'exists:videos,id',
            'documents' => 'nullable|array',
            'documents.*' => 'exists:documents,id',
            'quizzes' => 'nullable|array',
            'quizzes.*' => 'exists:quizzes,id',
        ]);

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
            'status' => $request->status,
            'created_by' => $user->id,
            'tutor_id' => $user->id,
        ]);

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

        return redirect()->route('tutors.content.index')->with('success', "Course \"{$course->title}\" created successfully.");
    }

    /**
     * Store video content (supporting Vimeo, Mux, YouTube URLs).
     */
    public function storeVideo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|mimes:mp4,mov,avi,webm|max:512000', // 500MB
            'grade_level' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $videoSource = 'youtube';
        $externalVideoUrl = $request->video_url;
        $externalVideoId = null;
        $vimeoId = null;
        $vimeoEmbedUrl = null;

        if ($request->filled('video_url')) {
            $parsed = VideoSourceService::parseVideoUrl($request->video_url);
            if ($parsed) {
                $videoSource = $parsed['source'];
                $externalVideoId = $parsed['video_id'];
                if ($videoSource === 'vimeo') {
                    $vimeoId = $parsed['video_id'];
                    $vimeoEmbedUrl = $parsed['embed_url'];
                }
            }
        }

        // If file uploaded directly, attempt Vimeo TUS upload via VimeoService if configured
        if ($request->hasFile('video_file')) {
            $filePath = $request->file('video_file')->store('videos/tutors', 'public');
            try {
                $vimeoService = app(VimeoService::class);
                $vimeoResult = $vimeoService->uploadVideo($filePath, $request->title, $request->description, $user->id);
                if (isset($vimeoResult['success']) && $vimeoResult['success']) {
                    $videoSource = 'vimeo';
                    $vimeoId = $vimeoResult['video_id'];
                    $vimeoEmbedUrl = $vimeoResult['embed_url'];
                }
            } catch (\Exception $e) {
                // Keep local path fallback
                $videoSource = 'local';
            }
        }

        $video = Video::create([
            'title' => $request->title,
            'description' => $request->description,
            'external_video_url' => $externalVideoUrl,
            'external_video_id' => $externalVideoId,
            'video_source' => $videoSource,
            'vimeo_id' => $vimeoId,
            'vimeo_embed_url' => $vimeoEmbedUrl,
            'grade_level' => $request->grade_level,
            'subject_id' => $request->subject_id,
            'uploaded_by' => $user->id,
            'tutor_id' => $user->id,
            'status' => 'approved',
        ]);

        return back()->with('success', "Video \"{$video->title}\" added to content studio.");
    }

    /**
     * Store document content.
     */
    public function storeDocument(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_file' => 'required|mimes:pdf,doc,docx,ppt,pptx|max:51200', // 50MB
            'grade_level' => 'required|string',
        ]);

        $file = $request->file('document_file');
        $filePath = $file->store('documents/tutors', 'public');

        $document = Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size_bytes' => $file->getSize(),
            'grade_level' => $request->grade_level,
            'uploaded_by' => $user->id,
            'tutor_id' => $user->id,
        ]);

        return back()->with('success', "Document \"{$document->title}\" uploaded successfully.");
    }

    /**
     * Store quiz content.
     */
    public function storeQuiz(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|string',
            'time_limit_minutes' => 'required|integer|min:1',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'quiz_data' => 'required|json',
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'grade_level' => $request->grade_level,
            'time_limit_minutes' => $request->time_limit_minutes,
            'difficulty_level' => $request->difficulty_level,
            'quiz_data' => $request->quiz_data,
            'uploaded_by' => $user->id,
            'tutor_id' => $user->id,
            'status' => 'published',
        ]);

        return back()->with('success', "Quiz \"{$quiz->title}\" created successfully.");
    }

    /**
     * Delete tutor course.
     */
    public function deleteCourse($id)
    {
        $user = Auth::user();

        $course = Course::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('tutor_id', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->firstOrFail();

        $course->videos()->detach();
        $course->documents()->detach();
        $course->quizzes()->detach();

        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }

        $course->delete();

        return back()->with('success', 'Course deleted successfully.');
    }
}
