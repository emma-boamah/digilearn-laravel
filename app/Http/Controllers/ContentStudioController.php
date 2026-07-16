<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class ContentStudioController extends Controller
{
    /**
     * Display the Content Studio dashboard.
     */
    /**
     * Get the active school and authorize access.
     */
    protected function getSchool()
    {
        $user = Auth::user();
        $school = app()->has('tenant') ? app('tenant') : $user->school;

        if (!$school) {
            abort(403, 'You are not associated with a school.');
        }

        // Allow superusers to bypass the school_id restriction
        if ($user->school_id !== $school->id && !$user->is_superuser) {
            abort(403, 'Unauthorized access to this school.');
        }

        return $school;
    }

    public function index()
    {
        $user = Auth::user();
        $school = $this->getSchool();

        // Fetch videos belonging to this school
        $videos = Video::where('school_id', $school->id)->latest()->paginate(10, ['*'], 'videos_page');
        
        // Fetch quizzes belonging to this school
        $quizzes = Quiz::where('school_id', $school->id)->latest()->paginate(10, ['*'], 'quizzes_page');

        return view('schools.studio.index', compact('school', 'videos', 'quizzes'));
    }

    /**
     * Show form to create a new private video.
     */
    public function createVideo()
    {
        $school = $this->getSchool();
        
        // For subjects dropdown
        $subjects = \App\Models\Subject::orderBy('name')->get();

        return view('schools.studio.create-video', compact('school', 'subjects'));
    }

    /**
     * Store a new private video.
     */
    public function storeVideo(Request $request)
    {
        $user = Auth::user();
        $school = $this->getSchool();

        $request->validate([
            'title' => 'required|string|max:255',
            'external_video_url' => 'required|url',
            'grade_level' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
        ]);

        Video::create([
            'school_id' => $school->id,
            'title' => $request->title,
            'external_video_url' => $request->external_video_url,
            'video_source' => 'youtube', // Defaulting to youtube for simplicity, can be expanded
            'grade_level' => $request->grade_level,
            'subject_id' => $request->subject_id,
            'description' => $request->description,
            'uploaded_by' => $user->id,
            'status' => 'published', // Auto-publish for private school content
            'is_agent_generated' => false,
        ]);

        return redirect()->route('school.studio.index')->with('success', 'Private video added successfully.');
    }

    /**
     * Show form to create a new private quiz.
     */
    public function createQuiz()
    {
        $school = $this->getSchool();
        $subjects = \App\Models\Subject::orderBy('name')->get();

        return view('schools.studio.create-quiz', compact('school', 'subjects'));
    }

    /**
     * Store a new private quiz.
     */
    public function storeQuiz(Request $request)
    {
        $user = Auth::user();
        $school = $this->getSchool();

        $request->validate([
            'title' => 'required|string|max:255',
            'grade_level' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'time_limit_minutes' => 'required|integer|min:1',
            'difficulty_level' => 'required|in:easy,medium,hard',
        ]);

        $quiz = Quiz::create([
            'school_id' => $school->id,
            'title' => $request->title,
            'grade_level' => $request->grade_level,
            'subject_id' => $request->subject_id,
            'time_limit_minutes' => $request->time_limit_minutes,
            'difficulty_level' => $request->difficulty_level,
            'status' => 'published',
            'uploaded_by' => $user->id,
            'quiz_data' => json_encode(['questions' => []]), // Empty JSON structure to start
        ]);

        // In a real scenario, you'd redirect to a question builder.
        // For MVP, we'll just go back to the studio.
        return redirect()->route('school.studio.index')->with('success', 'Private quiz created. You can now add questions to it.');
    }

    /**
     * Request a private quiz to be shared globally.
     */
    public function requestShare($id)
    {
        $school = $this->getSchool();

        $quiz = Quiz::where('school_id', $school->id)->findOrFail($id);
        
        $quiz->share_requested = true;
        $quiz->save();

        return back()->with('success', 'Request sent to Super Admin to share this quiz globally.');
    }
}
