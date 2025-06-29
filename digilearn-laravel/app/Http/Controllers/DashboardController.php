<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $level = $user->level;
        $lessons = $this->getLessonsForLevel($level);

        return view('dashboard', compact('lessons', 'level'));
    }
    /**
     * Show level selection page
     */
    public function levelSelection()
    {
        // Log access to level selection
        Log::channel('security')->info('level_selection_accessed', [
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Get available levels
        $levels = $this->getAvailableLevels();

        return view('dashboard.level-selection', compact('levels'));
    }

    /**
     * Handle level selection
     */
    public function selectLevel(Request $request, $levelId)
    {
        $validLevels = [
            'primary-1', 'primary-2', 'primary-3', 'primary-4', 'primary-5', 'primary-6',
            'jhs-1', 'jhs-2', 'jhs-3',
            'shs-1', 'shs-2', 'shs-3'
        ];

        if (!in_array($levelId, $validLevels)) {
            return redirect()->route('dashboard.level-selection')
                ->withErrors(['level' => 'Invalid level selected.']);
        }

        // Store selected level in session
        session(['selected_level' => $levelId]);

        // Log level selection
        Log::channel('security')->info('level_selected', [
            'user_id' => Auth::id(),
            'level' => $levelId,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return redirect()->route('dashboard.main');
    }

    /**
     * Show main dashboard
     */
    public function main()
    {
        // Check if user has selected a level
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevel = session('selected_level');

        // Log dashboard access
        Log::channel('security')->info('dashboard_main_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.main', compact('selectedLevel'));
    }

    /**
     * Show DigiLearn page
     */
    public function digilearn()
    {
        // Check if user has selected a level
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevel = session('selected_level');

        // Log DigiLearn access
        Log::channel('security')->info('digilearn_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Sample lessons data based on level
        $lessons = $this->getLessonsForLevel($selectedLevel);

        return view('dashboard.digilearn', compact('selectedLevel', 'lessons'));
    }

    /**
     * Show personalized learning page
     */
    public function personalized()
    {
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevel = session('selected_level');

        Log::channel('security')->info('personalized_learning_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.personalized', compact('selectedLevel'));
    }

    /**
     * Show shop page
     */
    public function shop()
    {
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevel = session('selected_level');

        Log::channel('security')->info('shop_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.shop', compact('selectedLevel'));
    }

    /**
     * Show change level selection page
     */
    public function changeLevelSelection()
    {
        Log::channel('security')->info('change_level_accessed', [
            'user_id' => Auth::id(),
            'current_level' => session('selected_level'),
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Get available levels
        $levels = $this->getAvailableLevels();

        return view('dashboard.level-selection', compact('levels'))->with('isChanging', true);
    }

    /**
     * View specific lesson
     */
    public function viewLesson($lessonId)
    {
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevel = session('selected_level');
        $lesson = $this->getLessonById($lessonId, $selectedLevel);

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->withErrors(['lesson' => 'Lesson not found.']);
        }

        // Get related lessons (exclude current lesson)
        $allLessons = $this->getLessonsForLevel($selectedLevel);
        $relatedLessons = array_filter($allLessons, function($l) use ($lessonId) {
            return $l['id'] != $lessonId;
        });
        $relatedLessons = array_slice($relatedLessons, 0, 8);

        // Sample comments for the lesson
        $comments = $this->getCommentsForLesson($lessonId);

        Log::channel('security')->info('lesson_viewed', [
            'user_id' => Auth::id(),
            'lesson_id' => $lessonId,
            'selected_level' => $selectedLevel,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.lesson-view', compact('lesson', 'selectedLevel'));
    }

    /**
     * Save lesson notes
     */
    public function saveNotes(Request $request, $lessonId)
    {
        $request->validate([
            'notes' => 'required|string|max:5000'
        ]);

        // Here you would save to database
        // For now, we'll just log it
        Log::info('lesson_notes_saved', [
            'user_id' => Auth::id(),
            'lesson_id' => $lessonId,
            'notes_length' => strlen($request->notes),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return response()->json(['success' => true, 'message' => 'Notes saved successfully']);
    }

    /**
     * Post lesson comment
     */
    public function postComment(Request $request, $lessonId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Here you would save to database
        Log::info('lesson_comment_posted', [
            'user_id' => Auth::id(),
            'lesson_id' => $lessonId,
            'comment_length' => strlen($request->comment),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return response()->json(['success' => true, 'message' => 'Comment posted successfully']);
    }

    /**
     * Get available education levels (grouped)
     */
    private function getAvailableLevels()
    {
        return [
            [
                'id' => 'primary-lower',
                'title' => 'Grade/Primary 1-3',
                'description' => 'Lower primary or Elementary school',
                'has_illustration' => false,
                'levels' => ['primary-1', 'primary-2', 'primary-3']
            ],
            [
                'id' => 'primary-upper', 
                'title' => 'Grade/Primary 4-6',
                'description' => 'Upper primary or elementary school',
                'has_illustration' => false,
                'levels' => ['primary-4', 'primary-5', 'primary-6']
            ],
            [
                'id' => 'jhs',
                'title' => 'Grade/JHS 7-9', 
                'description' => 'Junior High School or Middle school',
                'has_illustration' => true,
                'levels' => ['jhs-1', 'jhs-2', 'jhs-3']
            ],
            [
                'id' => 'shs',
                'title' => 'Grade/SHS 1-3',
                'description' => 'High school or Senior High School', 
                'has_illustration' => false,
                'levels' => ['shs-1', 'shs-2', 'shs-3']
            ]
        ];
    }

    /**
     * Get lessons for a specific level
     */
    private function getLessonsForLevel($level)
    {
        // Sample lessons data - in production, this would come from database
        $allLessons = [
            'primary-1' => [
                ['id' => 1, 'title' => 'Basic Mathematics', 'subject' => 'Mathematics', 'duration' => '15 min', 'thumbnail' => 'images/lessons/math-basic.jpg', 'video_url' => 'videos/lessons/math-basic.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 2, 'title' => 'English Alphabet', 'subject' => 'English', 'duration' => '20 min', 'thumbnail' => 'images/lessons/english-alphabet.jpg', 'video_url' => 'videos/lessons/english-alphabet.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
                ['id' => 3, 'title' => 'Colors and Shapes', 'subject' => 'Art', 'duration' => '12 min', 'thumbnail' => 'images/lessons/colors-shapes.jpg', 'video_url' => 'videos/lessons/colors-shapes.mp4', 'instructor' => 'Ms. Adjei', 'year' => '2024'],
                ['id' => 4, 'title' => 'Our Body Parts', 'subject' => 'Science', 'duration' => '18 min', 'thumbnail' => 'images/lessons/body-parts.jpg', 'video_url' => 'videos/lessons/body-parts.mp4', 'instructor' => 'Dr. Mensah', 'year' => '2024'],
            ],
            'primary-2' => [
                ['id' => 5, 'title' => 'Addition and Subtraction', 'subject' => 'Mathematics', 'duration' => '18 min', 'thumbnail' => 'images/lessons/math-addition.jpg', 'video_url' => 'videos/lessons/math-addition.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 6, 'title' => 'Reading Comprehension', 'subject' => 'English', 'duration' => '25 min', 'thumbnail' => 'images/lessons/reading-comp.jpg', 'video_url' => 'videos/lessons/reading-comp.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
                ['id' => 7, 'title' => 'Our Environment', 'subject' => 'Science', 'duration' => '22 min', 'thumbnail' => 'images/lessons/environment.jpg', 'video_url' => 'videos/lessons/environment.mp4', 'instructor' => 'Dr. Mensah', 'year' => '2024'],
                ['id' => 8, 'title' => 'Ghanaian Culture', 'subject' => 'Social Studies', 'duration' => '20 min', 'thumbnail' => 'images/lessons/culture.jpg', 'video_url' => 'videos/lessons/culture.mp4', 'instructor' => 'Prof. Boateng', 'year' => '2024'],
            ],
            'primary-3' => [
                ['id' => 9, 'title' => 'Multiplication Tables', 'subject' => 'Mathematics', 'duration' => '25 min', 'thumbnail' => 'images/lessons/multiplication.jpg', 'video_url' => 'videos/lessons/multiplication.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 10, 'title' => 'Creative Writing', 'subject' => 'English', 'duration' => '30 min', 'thumbnail' => 'images/lessons/creative-writing.jpg', 'video_url' => 'videos/lessons/creative-writing.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
                ['id' => 11, 'title' => 'Plants and Animals', 'subject' => 'Science', 'duration' => '28 min', 'thumbnail' => 'images/lessons/plants-animals.jpg', 'video_url' => 'videos/lessons/plants-animals.mp4', 'instructor' => 'Dr. Mensah', 'year' => '2024'],
                ['id' => 12, 'title' => 'Map Reading', 'subject' => 'Social Studies', 'duration' => '22 min', 'thumbnail' => 'images/lessons/map-reading.jpg', 'video_url' => 'videos/lessons/map-reading.mp4', 'instructor' => 'Prof. Boateng', 'year' => '2024'],
            ],
            'primary-4' => [
                ['id' => 25, 'title' => 'Fractions and Decimals', 'subject' => 'Mathematics', 'duration' => '28 min', 'thumbnail' => 'images/lessons/fractions.jpg', 'video_url' => 'videos/lessons/fractions.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 26, 'title' => 'Story Writing', 'subject' => 'English', 'duration' => '32 min', 'thumbnail' => 'images/lessons/story-writing.jpg', 'video_url' => 'videos/lessons/story-writing.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
            ],
            'primary-5' => [
                ['id' => 27, 'title' => 'Advanced Mathematics', 'subject' => 'Mathematics', 'duration' => '35 min', 'thumbnail' => 'images/lessons/advanced-math-p5.jpg', 'video_url' => 'videos/lessons/advanced-math-p5.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 28, 'title' => 'Comprehension Skills', 'subject' => 'English', 'duration' => '30 min', 'thumbnail' => 'images/lessons/comprehension.jpg', 'video_url' => 'videos/lessons/comprehension.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
            ],
            'primary-6' => [
                ['id' => 29, 'title' => 'BECE Preparation Math', 'subject' => 'Mathematics', 'duration' => '40 min', 'thumbnail' => 'images/lessons/bece-math.jpg', 'video_url' => 'videos/lessons/bece-math.mp4', 'instructor' => 'Mrs. Asante', 'year' => '2024'],
                ['id' => 30, 'title' => 'BECE English Prep', 'subject' => 'English', 'duration' => '38 min', 'thumbnail' => 'images/lessons/bece-english.jpg', 'video_url' => 'videos/lessons/bece-english.mp4', 'instructor' => 'Mr. Osei', 'year' => '2024'],
            ],
            'jhs-1' => [
                ['id' => 13, 'title' => 'Algebra Basics', 'subject' => 'Mathematics', 'duration' => '30 min', 'thumbnail' => 'images/lessons/algebra-basics.jpg', 'video_url' => 'videos/lessons/algebra-basics.mp4', 'instructor' => 'Mr. Kwame', 'year' => '2024'],
                ['id' => 14, 'title' => 'Grammar Rules', 'subject' => 'English', 'duration' => '28 min', 'thumbnail' => 'images/lessons/grammar.jpg', 'video_url' => 'videos/lessons/grammar.mp4', 'instructor' => 'Mrs. Akosua', 'year' => '2024'],
                ['id' => 15, 'title' => 'Introduction to Chemistry', 'subject' => 'Science', 'duration' => '35 min', 'thumbnail' => 'images/lessons/chemistry-intro.jpg', 'video_url' => 'videos/lessons/chemistry-intro.mp4', 'instructor' => 'Dr. Appiah', 'year' => '2024'],
                ['id' => 16, 'title' => 'Ghana History', 'subject' => 'Social Studies', 'duration' => '32 min', 'thumbnail' => 'images/lessons/ghana-history.jpg', 'video_url' => 'videos/lessons/ghana-history.mp4', 'instructor' => 'Prof. Nkrumah', 'year' => '2024'],
            ],
            'jhs-2' => [
                ['id' => 17, 'title' => 'Geometry Fundamentals', 'subject' => 'Mathematics', 'duration' => '35 min', 'thumbnail' => 'images/lessons/geometry.jpg', 'video_url' => 'videos/lessons/geometry.mp4', 'instructor' => 'Mr. Kwame', 'year' => '2024'],
                ['id' => 18, 'title' => 'Literature Analysis', 'subject' => 'English', 'duration' => '40 min', 'thumbnail' => 'images/lessons/literature.jpg', 'video_url' => 'videos/lessons/literature.mp4', 'instructor' => 'Mrs. Akosua', 'year' => '2024'],
                ['id' => 19, 'title' => 'Physics Principles', 'subject' => 'Science', 'duration' => '38 min', 'thumbnail' => 'images/lessons/physics.jpg', 'video_url' => 'videos/lessons/physics.mp4', 'instructor' => 'Dr. Appiah', 'year' => '2024'],
                ['id' => 20, 'title' => 'Economics Basics', 'subject' => 'Social Studies', 'duration' => '30 min', 'thumbnail' => 'images/lessons/economics.jpg', 'video_url' => 'videos/lessons/economics.mp4', 'instructor' => 'Prof. Nkrumah', 'year' => '2024'],
            ],
            'jhs-3' => [
                ['id' => 31, 'title' => 'BECE Mathematics', 'subject' => 'Mathematics', 'duration' => '45 min', 'thumbnail' => 'images/lessons/bece-final-math.jpg', 'video_url' => 'videos/lessons/bece-final-math.mp4', 'instructor' => 'Mr. Kwame', 'year' => '2024'],
                ['id' => 32, 'title' => 'BECE Science', 'subject' => 'Science', 'duration' => '42 min', 'thumbnail' => 'images/lessons/bece-science.jpg', 'video_url' => 'videos/lessons/bece-science.mp4', 'instructor' => 'Dr. Appiah', 'year' => '2024'],
            ],
            'shs-1' => [
                ['id' => 21, 'title' => 'Advanced Algebra', 'subject' => 'Mathematics', 'duration' => '45 min', 'thumbnail' => 'images/lessons/advanced-algebra.jpg', 'video_url' => 'videos/lessons/advanced-algebra.mp4', 'instructor' => 'Dr. Frimpong', 'year' => '2024'],
                ['id' => 22, 'title' => 'Essay Writing', 'subject' => 'English', 'duration' => '50 min', 'thumbnail' => 'images/lessons/essay-writing.jpg', 'video_url' => 'videos/lessons/essay-writing.mp4', 'instructor' => 'Prof. Agyeman', 'year' => '2024'],
                ['id' => 23, 'title' => 'Organic Chemistry', 'subject' => 'Chemistry', 'duration' => '55 min', 'thumbnail' => 'images/lessons/organic-chemistry.jpg', 'video_url' => 'videos/lessons/organic-chemistry.mp4', 'instructor' => 'Dr. Owusu', 'year' => '2024'],
                ['id' => 24, 'title' => 'World History', 'subject' => 'History', 'duration' => '40 min', 'thumbnail' => 'images/lessons/world-history.jpg', 'video_url' => 'videos/lessons/world-history.mp4', 'instructor' => 'Prof. Danso', 'year' => '2024'],
            ],
            'shs-2' => [
                ['id' => 33, 'title' => 'Calculus Introduction', 'subject' => 'Mathematics', 'duration' => '50 min', 'thumbnail' => 'images/lessons/calculus.jpg', 'video_url' => 'videos/lessons/calculus.mp4', 'instructor' => 'Dr. Frimpong', 'year' => '2024'],
                ['id' => 34, 'title' => 'Advanced Literature', 'subject' => 'English', 'duration' => '48 min', 'thumbnail' => 'images/lessons/advanced-lit.jpg', 'video_url' => 'videos/lessons/advanced-lit.mp4', 'instructor' => 'Prof. Agyeman', 'year' => '2024'],
            ],
            'shs-3' => [
                ['id' => 35, 'title' => 'WASSCE Mathematics', 'subject' => 'Mathematics', 'duration' => '60 min', 'thumbnail' => 'images/lessons/wassce-math.jpg', 'video_url' => 'videos/lessons/wassce-math.mp4', 'instructor' => 'Dr. Frimpong', 'year' => '2024'],
                ['id' => 36, 'title' => 'WASSCE Preparation', 'subject' => 'General', 'duration' => '55 min', 'thumbnail' => 'images/lessons/wassce-prep.jpg', 'video_url' => 'videos/lessons/wassce-prep.mp4', 'instructor' => 'Prof. Agyeman', 'year' => '2024'],
            ],
        ];

        // Return the lessons for the specific level, or empty array if level not found
        return $allLessons[$level] ?? [];
    }

    /**
     * Get lesson by ID
     */
    private function getLessonById($lessonId, $level)
    {
        $lessons = $this->getLessonsForLevel($level);
        
        foreach ($lessons as $lesson) {
            if ($lesson['id'] == $lessonId) {
                return $lesson;
            }
        }

        return null;
    }

    /**
     * Get comments for a lesson
     */
    private function getCommentsForLesson($lessonId)
    {
        return [
            [
                'id' => 1,
                'author' => 'einana kojo',
                'avatar' => 'E',
                'time' => '3 hours ago',
                'text' => 'very interesting and helpful lesson please add more to it. this time make it more easy.',
                'likes' => 22,
                'dislikes' => 1
            ],
            [
                'id' => 2,
                'author' => 'kwame asante',
                'avatar' => 'K',
                'time' => '5 hours ago',
                'text' => 'Great explanation! This helped me understand the concept better.',
                'likes' => 15,
                'dislikes' => 0
            ],
        ];
    }
}