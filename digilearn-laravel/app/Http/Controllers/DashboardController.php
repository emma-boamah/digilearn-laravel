<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function levelSelection()
    {
        $levels = [
            [
                'id' => 'primary-1-3',
                'title' => 'Grade/Primary 1-3',
                'description' => 'Lower primary or Elementary school',
                'image' => 'images/levels/primary-1-3.jpg',
                'has_illustration' => false
            ],
            [
                'id' => 'primary-4-6', 
                'title' => 'Grade/Primary 4-6',
                'description' => 'Upper primary or elementary school',
                'image' => 'images/levels/primary-4-6.jpg',
                'has_illustration' => false
            ],
            [
                'id' => 'jhs-7-9',
                'title' => 'Grade/JHS 7-9', 
                'description' => 'Junior High School or Middle school',
                'image' => 'images/levels/jhs-illustration.png',
                'has_illustration' => true
            ],
            [
                'id' => 'shs-1-3',
                'title' => 'Grade/SHS 1-3',
                'description' => 'High school or Senior High School', 
                'image' => 'images/levels/shs-1-3.jpg',
                'has_illustration' => false
            ]
        ];

        return view('dashboard.level-selection', compact('levels'));
    }

    public function selectLevel($levelId)
    {
        // Store selected level in session
        session(['selected_level' => $levelId]);
        
        // Redirect to main dashboard after level selection
        return redirect()->route('dashboard.main')->with('success', 'Level selected successfully!');
    }

    public function main()
    {
        // Check if user has selected a level
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevel = session('selected_level');
        
        return view('dashboard.main', compact('selectedLevel'));
    }

    public function digilearn()
    {
        // Check if user has selected a level
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevel = session('selected_level');
        
        // Mock lessons data based on selected level
        $lessons = $this->getLessonsForLevel($selectedLevel);

        return view('dashboard.digilearn', compact('lessons', 'selectedLevel'));
    }

    private function getLessonsForLevel($level)
    {
        // Mock data - in real app, this would come from database
        $allLessons = [
            'primary-1-3' => [
                [
                    'id' => 1,
                    'title' => 'Basic Numbers and Counting',
                    'subject' => 'Mathematics Gr-1',
                    'duration' => '12:30',
                    'instructor' => 'Prof. Mensah',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/primary-math-1.jpg',
                    'video_url' => 'videos/lessons/animals-homes.mp4'
                ],
                [
                    'id' => 2,
                    'title' => 'Animals and Their Homes',
                    'subject' => 'Science Gr-2',
                    'duration' => '15:45',
                    'instructor' => 'Prof. Asante',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/animals-homes.jpg',
                    'video_url' => 'videos/lessons/animals-homes.mp4'
                ]
            ],
            'primary-4-6' => [
                [
                    'id' => 3,
                    'title' => 'Fractions and Decimals',
                    'subject' => 'Mathematics Gr-4',
                    'duration' => '18:20',
                    'instructor' => 'Prof. Osei',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/fractions.jpg',
                    'video_url' => 'videos/lessons/fractions.mp4'
                ]
            ],
            'jhs-7-9' => [
                [
                    'id' => 4,
                    'title' => 'Living and Non Living organism',
                    'subject' => 'Science Gr-7',
                    'duration' => '14:07',
                    'instructor' => 'Prof. Aboagye',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/lesson-1.jpg',
                    'video_url' => 'videos/lessons/living-non-living-1.mp4'
                ],
                [
                    'id' => 5,
                    'title' => 'Algebraic Expressions',
                    'subject' => 'Mathematics Gr-8',
                    'duration' => '16:30',
                    'instructor' => 'Prof. Kwame',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/algebra.jpg',
                    'video_url' => 'videos/lessons/algebra.mp4'
                ]
            ],
            'shs-1-3' => [
                [
                    'id' => 6,
                    'title' => 'Chemical Bonding',
                    'subject' => 'Chemistry SHS-1',
                    'duration' => '22:15',
                    'instructor' => 'Prof. Adjei',
                    'year' => '2025',
                    'thumbnail' => 'images/lessons/chemistry.jpg',
                    'video_url' => 'videos/lessons/chemistry.mp4'
                ]
            ]
        ];

        return $allLessons[$level] ?? [];
    }

    public function personalized()
    {
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }
        
        return view('dashboard.personalized');
    }

    public function shop()
    {
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection');
        }
        
        return view('dashboard.shop');
    }

    public function changeLevelSelection()
    {
        // Allow user to change their level selection
        return redirect()->route('dashboard.level-selection');
    }

    public function viewLesson($lessonId)
    {
        // Check if user has selected a level
        if (!session('selected_level')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevel = session('selected_level');
        $lessons = $this->getLessonsForLevel($selectedLevel);
        
        // Find the specific lesson
        $lesson = collect($lessons)->firstWhere('id', $lessonId);
        
        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Get related lessons (exclude current lesson)
        $relatedLessons = collect($lessons)->where('id', '!=', $lessonId)->take(8);
        
        // Mock comments data
        $comments = $this->getCommentsForLesson($lessonId);
        
        return view('dashboard.lesson-view', compact('lesson', 'relatedLessons', 'comments', 'selectedLevel'));
    }

    private function getCommentsForLesson($lessonId)
    {
        // Mock comments data - in real app, this would come from database
        return [
            [
                'id' => 1,
                'user_name' => '@nana kojo',
                'user_avatar' => 'N',
                'comment' => 'very interesting and helpful lesson please add more to it, this time make it more easy.',
                'time_ago' => '3 hours ago',
                'likes' => 22,
                'replies' => []
            ],
            [
                'id' => 2,
                'user_name' => '@sarah mensah',
                'user_avatar' => 'S',
                'comment' => 'This explanation is so clear! Thank you for making science easy to understand.',
                'time_ago' => '5 hours ago',
                'likes' => 15,
                'replies' => []
            ],
            [
                'id' => 3,
                'user_name' => '@kwame asante',
                'user_avatar' => 'K',
                'comment' => 'Could you please add more examples? I want to practice more.',
                'time_ago' => '1 day ago',
                'likes' => 8,
                'replies' => []
            ]
        ];
    }

    // Add method for saving notes
    public function saveNotes(Request $request, $lessonId)
    {
        $request->validate([
            'notes' => 'required|string|max:5000'
        ]);

        // In a real app, you would save to database
        // For now, we'll just store in session
        $userNotes = session('user_notes', []);
        $userNotes[$lessonId] = $request->notes;
        session(['user_notes' => $userNotes]);

        return response()->json([
            'success' => true,
            'message' => 'Notes saved successfully!'
        ]);
    }

    // Add method for posting comments
    public function postComment(Request $request, $lessonId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // In a real app, you would save to database
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Comment posted successfully!',
            'comment' => [
                'user_name' => (auth()->user->name ?? 'Anonymous'),
                'user_avatar' => strtoupper(substr(auth()->user->name ?? 'A', 0, 1)),
                'comment' => $request->comment,
                'time_ago' => 'just now',
                'likes' => 0
            ]
        ]);
    }
}