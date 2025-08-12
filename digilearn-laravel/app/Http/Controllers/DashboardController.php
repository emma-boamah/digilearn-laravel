<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SavedLesson;
use App\Models\VirtualClass;

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
        // Check subscription status (optional)
        $user = Auth::user();
        $hasActiveSubscription = $user->currentSubscription && $user->currentSubscription->isActive();
        $isInTrial = $user->currentSubscription && $user->currentSubscription->isInTrial();

        // Log access to level selection
        Log::channel('security')->info('level_selection_accessed', [
            'user_id' => Auth::id(),
            'has_subscription' => $hasActiveSubscription,
            'is_trial' => $isInTrial,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Get available levels
        $levels = $this->getAvailableLevels();

        return view('dashboard.level-selection', compact('levels', 'hasActiveSubscription', 'isInTrial'));
    }

    /**
     * Handle level selection (updated to work with the new flow)
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
            'subscription_status' => Auth::user()->currentSubscription?->status ?? 'none',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return redirect()->route('dashboard.main')
            ->with('success', 'Level selected successfully!');
    }

    /**
     * Handle level group selection and redirect to digilearn
     */
    public function selectLevelGroup(Request $request, $groupId)
    {
        $validGroups = ['primary-lower', 'primary-upper', 'jhs', 'shs', 'university'];

        if (!in_array($groupId, $validGroups)) {
            return redirect()->route('dashboard.level-selection')
                ->withErrors(['group' => 'Invalid level group selected.']);
        }

        // Check subscription access for premium content (optional)
        $user = Auth::user();
        if (!$this->hasAccessToLevelGroup($user, $groupId)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this content.');
        }

        // Update user's grade
        $user->update(['grade' => $groupId]);

        // Store selected level group in session
        session(['selected_level_group' => $groupId]);

        // Log level group selection
        Log::channel('security')->info('level_group_selected', [
            'user_id' => Auth::id(),
            'level_group' => $groupId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Redirect directly to digilearn
        return redirect()->route('dashboard.digilearn')
            ->with('success', 'Level selected successfully!');
    }

    /**
     * Update main dashboard to work with level groups
     */
    public function main()
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevelGroup = session('selected_level_group');
        $user = Auth::user();

        // Get subscription info for dashboard display
        $currentSubscription = $user->currentSubscription;
        $subscriptionInfo = null;
        
        if ($currentSubscription) {
            $subscriptionInfo = [
                'plan_name' => $currentSubscription->pricingPlan->name,
                'status' => $currentSubscription->status,
                'days_remaining' => $currentSubscription->days_remaining,
                'trial_days_remaining' => $currentSubscription->trial_days_remaining,
                'is_trial' => $currentSubscription->isInTrial(),
                'is_active' => $currentSubscription->isActive(),
            ];
        }

        // Log dashboard access
        Log::channel('security')->info('dashboard_main_accessed', [
            'user_id' => Auth::id(),
            'selected_level_group' => $selectedLevelGroup,
            'subscription_plan' => $currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.main', compact('selectedLevelGroup', 'subscriptionInfo'));
    }

    /**
     * Show DigiLearn page (updated to work with level groups)
     */
    public function digilearn()
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevelGroup = session('selected_level_group');
        $user = Auth::user();

        // For university, redirect to programs
        if ($selectedLevelGroup === 'university') {
            return redirect()->route('dashboard.university.programs');
        }

        // Check subscription access
        if (!$this->hasAccessToLevelGroup($user, $selectedLevelGroup)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this content.');
        }

        // Log DigiLearn access
        Log::channel('security')->info('digilearn_accessed', [
            'user_id' => Auth::id(),
            'selected_level_group' => $selectedLevelGroup,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Get lessons for the entire level group
        $lessons = $this->getLessonsForLevelGroup($selectedLevelGroup);
        
        // Filter lessons based on subscription (optional)
        $lessons = $this->filterLessonsBySubscription($user, $lessons);

        return view('dashboard.digilearn', compact('selectedLevelGroup', 'lessons'));
    }

    /**
     * Handle joining a class
     */
    public function joinClass(Request $request)
    {
        $user = Auth::user();

        // Superuser can join any class
        if ($user->is_superuser) {
            $userGrade = $user->grade;

            // Find an active class for the user's grade level
            $virtualClass = VirtualClass::active()->forGrade($userGrade)->first();

            if (!$virtualClass) {
                // If no active class, try to find a class where the user is currently assigned
                if ($user->current_room_id && $user->virtualClass) {
                    $virtualClass = $user->virtualClass;
                } else {
                    Log::info('superuser_join_class_no_active_class', [
                        'user_id' => Auth::id(),
                        'user_grade' => $userGrade,
                        'ip' => request()->ip(),
                    ]);
                    return redirect()->route('dashboard.main')
                        ->with('info', 'No active class for your grade level at the moment. Please check back later or contact support.');
                }
            }

            try {
                $user->update([
                    'current_room_id' => $virtualClass->room_id,
                    'is_online' => true,
                    'last_activity_at' => now(),
                ]);

                Log::channel('security')->info('superuser_joined_class', [
                    'user_id' => Auth::id(),
                    'user_grade' => $userGrade,
                    'room_id' => $virtualClass->room_id,
                    'ip' => request()->ip(),
                    'timestamp' => Carbon::now()->toISOString()
                ]);

                return redirect()->route('dashboard.classroom.show', $virtualClass->room_id);

            } catch (\Exception $e) {
                Log::error('superuser_join_class_error', [
                    'user_id' => Auth::id(),
                    'user_grade' => $userGrade,
                    'error' => $e->getMessage(),
                    'ip' => request()->ip(),
                ]);
                return redirect()->back()->with('error', 'Failed to join class. Please try again.');
            }
        }

        // Regular users must have Extra Tuition plan
        if (!$user->hasExtraTuitionPlan()) {
            return redirect()->route('pricing')
                ->with('warning', 'Please subscribe to the "Extra Tuition" plan to join a class.');
        }

        $userGrade = $user->grade;

        // Find an active class for the user's grade level
        $virtualClass = VirtualClass::active()->forGrade($userGrade)->first();

        if (!$virtualClass) {
            if ($user->current_room_id && $user->virtualClass) {
                $virtualClass = $user->virtualClass;
            } else {
                Log::info('join_class_no_active_class', [
                    'user_id' => Auth::id(),
                    'user_grade' => $userGrade,
                    'ip' => request()->ip(),
                ]);
                return redirect()->route('dashboard.main')
                    ->with('info', 'No active class for your grade level at the moment. Please check back later or contact support.');
            }
        }

        try {
            $user->update([
                'current_room_id' => $virtualClass->room_id,
                'is_online' => true,
                'last_activity_at' => now(),
            ]);

            Log::channel('security')->info('user_joined_class', [
                'user_id' => Auth::id(),
                'user_grade' => $userGrade,
                'room_id' => $virtualClass->room_id,
                'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
                'ip' => request()->ip(),
                'timestamp' => Carbon::now()->toISOString()
            ]);

            return redirect()->route('dashboard.classroom.show', $virtualClass->room_id);

        } catch (\Exception $e) {
            Log::error('join_class_error', [
                'user_id' => Auth::id(),
                'user_grade' => $userGrade,
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            return redirect()->back()->with('error', 'Failed to join class. Please try again.');
        }
    }

    /**
     * Show the virtual classroom page.
     */
    public function showClassroom($roomId)
    {
        $user = Auth::user();

        // Ensure the user is authenticated and potentially in this room
        if (!$user->current_room_id || $user->current_room_id !== $roomId) {
            // If user is trying to access a room they are not assigned to, redirect
            return redirect()->route('dashboard.main')
                ->with('error', 'You are not authorized to access this classroom or the class has ended.');
        }

        $virtualClass = VirtualClass::where('room_id', $roomId)->first();

        if (!$virtualClass) {
            return redirect()->route('dashboard.main')
                ->with('error', 'Classroom not found.');
        }

        Log::channel('security')->info('classroom_accessed', [
            'user_id' => Auth::id(),
            'room_id' => $roomId,
            'grade_level' => $virtualClass->grade_level,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Mark user as online (if not already)
        if (!$user->is_online) {
            $user->update(['is_online' => true]);
        }

        echo view('dashboard.classroom', compact('virtualClass', 'user'));
        echo <<<EOT
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            setInterval(() => {
                fetch('/api/ping')
                    .then(response => response.json())
                    .then(data => console.log('Status updated'));
            }, 60000); // Ping every minute
        </script>
        EOT;
        
    }

    /**
     * Check if user has access to a level group based on subscription
     */
    private function hasAccessToLevelGroup($user, $groupId)
    {
        // Superuser has access to all groups
        if ($user->is_superuser) {
            return true;
        }

        // Free access to primary-lower for all users
        if ($groupId === 'primary-lower') {
            return true;
        }

        // Check if user has active subscription or trial
        $currentSubscription = $user->currentSubscription;
        
        if (!$currentSubscription) {
            return false;
        }

        // All plans have access to all content during active subscription or trial
        return $currentSubscription->isActive() || $currentSubscription->isInTrial();
    }

    /**
     * Filter lessons based on user's subscription
     */
    private function filterLessonsBySubscription($user, $lessons)
    {
        $currentSubscription = $user->currentSubscription;
        
        // If no subscription, limit to first 2 lessons per level
        if (!$currentSubscription || (!$currentSubscription->isActive() && !$currentSubscription->isInTrial())) {
            return array_slice($lessons, 0, 2);
        }

        // Full access for subscribed users
        return $lessons;
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
        $user = Auth::user();

        // Check if personalized learning is available in user's plan
        $hasPersonalizedAccess = $this->hasPersonalizedAccess($user);

        Log::channel('security')->info('personalized_learning_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'has_personalized_access' => $hasPersonalizedAccess,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.personalized', compact('selectedLevel', 'hasPersonalizedAccess'));
    }

    /**
     * Check if user has access to personalized learning
     */
    private function hasPersonalizedAccess($user)
    {
        // Superuser has access to personalized learning
        if ($user->is_superuser) {
            return true;
        }

        $currentSubscription = $user->currentSubscription;
        // Example: Only users with active subscription or trial have access
        return $currentSubscription && ($currentSubscription->isActive() || $currentSubscription->isInTrial());
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
        $user = Auth::user();

        Log::channel('security')->info('shop_accessed', [
            'user_id' => Auth::id(),
            'selected_level' => $selectedLevel,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
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
        $user = Auth::user();
        
        Log::channel('security')->info('change_level_accessed', [
            'user_id' => Auth::id(),
            'current_level' => session('selected_level'),
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Get available levels
        $levels = $this->getAvailableLevels();

        return view('dashboard.level-selection', compact('levels'))->with('isChanging', true);
    }

    /**
     * View specific lesson (updated to work with level groups)
     */
    public function viewLesson($lessonId)
    {
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevelGroup = session('selected_level_group');
        $user = Auth::user();
        
        // Get all lessons from the level group to find the specific lesson
        $allLessons = $this->getLessonsForLevelGroup($selectedLevelGroup);
        $lesson = null;
        
        foreach ($allLessons as $l) {
            if ($l['id'] == $lessonId) {
                $lesson = $l;
                break;
            }
        }

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->withErrors(['lesson' => 'Lesson not found.']);
        }

        // Check if user has access to this specific lesson
        if (!$this->hasLessonAccess($user, $lesson)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this lesson.');
        }

        // Get related lessons (exclude current lesson)
        $relatedLessons = array_filter($allLessons, function($l) use ($lessonId) {
            return $l['id'] != $lessonId;
        });
        $relatedLessons = array_slice($relatedLessons, 0, 8);

        Log::channel('security')->info('lesson_viewed', [
            'user_id' => Auth::id(),
            'lesson_id' => $lessonId,
            'selected_level_group' => $selectedLevelGroup,
            'lesson_level' => $lesson['level'] ?? null,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.lesson-view', compact('lesson', 'selectedLevelGroup', 'relatedLessons'));
    }

    /**
     * Check if user has access to a specific lesson
     */
    private function hasLessonAccess($user, $lesson)
    {
        // Superuser has access to all lessons
        if ($user->is_superuser) {
            return true;
        }

        $currentSubscription = $user->currentSubscription;
        
        // Free users get access to first few lessons
        if (!$currentSubscription) {
            return in_array($lesson['id'], [1, 2, 5, 13]); // Sample free lesson IDs
        }

        // Subscribed users get full access
        return $currentSubscription->isActive() || $currentSubscription->isInTrial();
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
            'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
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
            'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return response()->json(['success' => true, 'message' => 'Comment posted successfully']);
    }

    /**
     * Show saved lessons page
     */
    public function savedLessons()
    {
        $savedLessons = SavedLesson::getSavedLessons(Auth::id());
        
        return view('dashboard.saved-lessons', compact('savedLessons'));
    }

    /**
     * Save a lesson
     */
    public function saveLesson(Request $request, $lessonId)
    {
        $request->validate([
            'lesson_title' => 'required|string|max:255',
            'lesson_subject' => 'required|string|max:100',
            'lesson_instructor' => 'required|string|max:100',
            'lesson_year' => 'required|string|max:4',
            'lesson_duration' => 'required|string|max:20',
            'lesson_thumbnail' => 'required|string|max:500',
            'lesson_video_url' => 'required|string|max:500',
            'selected_level' => 'required|string|max:50',
        ]);

        try {
            SavedLesson::create([
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'lesson_title' => $request->lesson_title,
                'lesson_subject' => $request->lesson_subject,
                'lesson_instructor' => $request->lesson_instructor,
                'lesson_year' => $request->lesson_year,
                'lesson_duration' => $request->lesson_duration,
                'lesson_thumbnail' => $request->lesson_thumbnail,
                'lesson_video_url' => $request->lesson_video_url,
                'selected_level' => $request->selected_level,
                'saved_at' => now(),
            ]);

            Log::info('lesson_saved', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lesson saved successfully!',
                'saved' => true
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === '23000') { // Duplicate entry
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson is already saved!',
                    'saved' => true
                ]);
            }

            Log::error('lesson_save_error', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save lesson. Please try again.',
                'saved' => false
            ], 500);
        }
    }

    /**
     * Unsave a lesson
     */
    public function unsaveLesson($lessonId)
    {
        try {
            $deleted = SavedLesson::where('user_id', Auth::id())
                                 ->where('lesson_id', $lessonId)
                                 ->delete();

            if ($deleted) {
                Log::info('lesson_unsaved', [
                    'user_id' => Auth::id(),
                    'lesson_id' => $lessonId,
                    'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Lesson removed from saved lessons!',
                    'saved' => false
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson was not found in saved lessons.',
                    'saved' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('lesson_unsave_error', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove lesson. Please try again.',
                'saved' => true
            ], 500);
        }
    }

    /**
     * Check if lesson is saved
     */
    public function checkLessonSaved($lessonId)
    {
        $isSaved = SavedLesson::isSaved(Auth::id(), $lessonId);
        
        return response()->json([
            'saved' => $isSaved
        ]);
    }

    /**
     * Show University Programs
     */
    public function universityPrograms()
    {
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $user = Auth::user();
        $programs = $this->getUniversityPrograms();

        Log::channel('security')->info('university_programs_accessed', [
            'user_id' => Auth::id(),
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.programs', compact('programs'));
    }

    /**
     * Show Program Courses
     */
    public function programCourses($programId)
    {
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $user = Auth::user();
        $program = $this->getProgramById($programId);
        
        if (!$program) {
            return redirect()->route('dashboard.university.programs')
                ->withErrors(['program' => 'Program not found.']);
        }

        $courses = $this->getCoursesForProgram($programId);
        session(['selected_program' => $programId]);

        Log::channel('security')->info('program_courses_accessed', [
            'user_id' => Auth::id(),
            'program_id' => $programId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.courses', compact('program', 'courses'));
    }

    /**
     * Show Course Lessons
     */
    public function courseLessons($courseId)
    {
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $user = Auth::user();
        $course = $this->getCourseById($courseId);
        
        if (!$course) {
            return redirect()->route('dashboard.university.programs')
                ->withErrors(['course' => 'Course not found.']);
        }

        $lessons = $this->getLessonsForCourse($courseId);
        session(['selected_course' => $courseId]);

        Log::channel('security')->info('course_lessons_accessed', [
            'user_id' => Auth::id(),
            'course_id' => $courseId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.lessons', compact('course', 'lessons'));
    }

    /**
     * Get University Programs
     */
    private function getUniversityPrograms()
    {
        return [
            [
                'id' => 'computer-science',
                'name' => 'Computer Science',
                'description' => 'Learn programming, algorithms, data structures, and software engineering',
                'duration' => '4 Years',
                'courses_count' => 32,
                'thumbnail' => 'images/programs/computer-science.jpg',
                'level' => 'Undergraduate',
                'department' => 'Engineering & Technology'
            ],
            [
                'id' => 'business-administration',
                'name' => 'Business Administration',
                'description' => 'Master business fundamentals, management, and entrepreneurship',
                'duration' => '4 Years',
                'courses_count' => 28,
                'thumbnail' => 'images/programs/business-admin.jpg',
                'level' => 'Undergraduate',
                'department' => 'Business & Economics'
            ],
            [
                'id' => 'medicine',
                'name' => 'Medicine',
                'description' => 'Comprehensive medical education and clinical training',
                'duration' => '6 Years',
                'courses_count' => 45,
                'thumbnail' => 'images/programs/medicine.jpg',
                'level' => 'Undergraduate',
                'department' => 'Health Sciences'
            ],
            [
                'id' => 'engineering',
                'name' => 'Engineering',
                'description' => 'Civil, Mechanical, Electrical, and Chemical Engineering',
                'duration' => '4 Years',
                'courses_count' => 38,
                'thumbnail' => 'images/programs/engineering.jpg',
                'level' => 'Undergraduate',
                'department' => 'Engineering & Technology'
            ],
            [
                'id' => 'law',
                'name' => 'Law',
                'description' => 'Legal studies, jurisprudence, and legal practice',
                'duration' => '4 Years',
                'courses_count' => 24,
                'thumbnail' => 'images/programs/law.jpg',
                'level' => 'Undergraduate',
                'department' => 'Law & Social Sciences'
            ],
            [
                'id' => 'education',
                'name' => 'Education',
                'description' => 'Teacher training and educational methodology',
                'duration' => '4 Years',
                'courses_count' => 26,
                'thumbnail' => 'images/programs/education.jpg',
                'level' => 'Undergraduate',
                'department' => 'Education & Humanities'
            ]
        ];
    }

    /**
     * Get Program by ID
     */
    private function getProgramById($programId)
    {
        $programs = $this->getUniversityPrograms();
        return collect($programs)->firstWhere('id', $programId);
    }

    /**
     * Get Courses for Program
     */
    private function getCoursesForProgram($programId)
    {
        $coursesData = [
            'computer-science' => [
                [
                    'id' => 'cs-101',
                    'name' => 'Introduction to Programming',
                    'code' => 'CS 101',
                    'description' => 'Fundamentals of programming using Python',
                    'credit_hours' => 3,
                    'semester' => 1,
                    'year' => 1,
                    'lessons_count' => 24,
                    'thumbnail' => 'images/courses/intro-programming.jpg',
                    'instructor' => 'Dr. Kwame Asante'
                ],
                [
                    'id' => 'cs-102',
                    'name' => 'Data Structures',
                    'code' => 'CS 102',
                    'description' => 'Arrays, linked lists, stacks, queues, trees, and graphs',
                    'credit_hours' => 4,
                    'semester' => 2,
                    'year' => 1,
                    'lessons_count' => 28,
                    'thumbnail' => 'images/courses/data-structures.jpg',
                    'instructor' => 'Prof. Ama Osei'
                ],
                [
                    'id' => 'cs-201',
                    'name' => 'Algorithms',
                    'code' => 'CS 201',
                    'description' => 'Algorithm design and analysis techniques',
                    'credit_hours' => 4,
                    'semester' => 1,
                    'year' => 2,
                    'lessons_count' => 32,
                    'thumbnail' => 'images/courses/algorithms.jpg',
                    'instructor' => 'Dr. Kofi Mensah'
                ],
                [
                    'id' => 'cs-202',
                    'name' => 'Database Systems',
                    'code' => 'CS 202',
                    'description' => 'Database design, SQL, and database management',
                    'credit_hours' => 3,
                    'semester' => 2,
                    'year' => 2,
                    'lessons_count' => 26,
                    'thumbnail' => 'images/courses/database-systems.jpg',
                    'instructor' => 'Dr. Akosua Frimpong'
                ]
            ],
            'business-administration' => [
                [
                    'id' => 'ba-101',
                    'name' => 'Principles of Management',
                    'code' => 'BA 101',
                    'description' => 'Fundamentals of business management and organization',
                    'credit_hours' => 3,
                    'semester' => 1,
                    'year' => 1,
                    'lessons_count' => 20,
                    'thumbnail' => 'images/courses/management.jpg',
                    'instructor' => 'Prof. Yaw Boateng'
                ],
                [
                    'id' => 'ba-102',
                    'name' => 'Financial Accounting',
                    'code' => 'BA 102',
                    'description' => 'Basic accounting principles and financial statements',
                    'credit_hours' => 4,
                    'semester' => 2,
                    'year' => 1,
                    'lessons_count' => 24,
                    'thumbnail' => 'images/courses/accounting.jpg',
                    'instructor' => 'Dr. Efua Adjei'
                ]
            ]
        ];

        return $coursesData[$programId] ?? [];
    }

    /**
     * Get Course by ID
     */
    private function getCourseById($courseId)
    {
        $allCourses = [];
        $programs = $this->getUniversityPrograms();
        
        foreach ($programs as $program) {
            $courses = $this->getCoursesForProgram($program['id']);
            $allCourses = array_merge($allCourses, $courses);
        }

        return collect($allCourses)->firstWhere('id', $courseId);
    }

    /**
     * Get Lessons for Course
     */
    private function getLessonsForCourse($courseId)
    {
        $lessonsData = [
            'cs-101' => [
                [
                    'id' => 'cs101-001',
                    'title' => 'Introduction to Python Programming',
                    'description' => 'Getting started with Python syntax and basic concepts',
                    'duration' => '45 min',
                    'video_url' => 'videos/courses/cs101/lesson1.mp4',
                    'thumbnail' => 'images/lessons/python-intro.jpg',
                    'instructor' => 'Dr. Kwame Asante',
                    'week' => 1,
                    'order' => 1
                ],
                [
                    'id' => 'cs101-002',
                    'title' => 'Variables and Data Types',
                    'description' => 'Understanding Python variables, strings, numbers, and booleans',
                    'duration' => '38 min',
                    'video_url' => 'videos/courses/cs101/lesson2.mp4',
                    'thumbnail' => 'images/lessons/variables.jpg',
                    'instructor' => 'Dr. Kwame Asante',
                    'week' => 1,
                    'order' => 2
                ],
                [
                    'id' => 'cs101-003',
                    'title' => 'Control Structures - If Statements',
                    'description' => 'Conditional logic and decision making in Python',
                    'duration' => '42 min',
                    'video_url' => 'videos/courses/cs101/lesson3.mp4',
                    'thumbnail' => 'images/lessons/control-structures.jpg',
                    'instructor' => 'Dr. Kwame Asante',
                    'week' => 2,
                    'order' => 3
                ]
            ],
            'cs-102' => [
                [
                    'id' => 'cs102-001',
                    'title' => 'Introduction to Data Structures',
                    'description' => 'Overview of data structures and their importance',
                    'duration' => '40 min',
                    'video_url' => 'videos/courses/cs102/lesson1.mp4',
                    'thumbnail' => 'images/lessons/data-structures-intro.jpg',
                    'instructor' => 'Prof. Ama Osei',
                    'week' => 1,
                    'order' => 1
                ]
            ]
        ];

        return $lessonsData[$courseId] ?? [];
    }

    /**
     * Update getAvailableLevels to use the new structure
     */
    private function getAvailableLevels()
    {
        $levelGroups = $this->getLevelGroups();
        $availableLevels = [];

        foreach ($levelGroups as $groupId => $group) {
            $availableLevels[] = [
                'id' => $groupId,
                'title' => $group['title'],
                'description' => $group['description'],
                'has_illustration' => $group['has_illustration']
            ];
        }

        return $availableLevels;
    }

    public function showLevelGroup($groupId)
    {
        $levelGroups = $this->getLevelGroups();
        
        if (!isset($levelGroups[$groupId])) {
            return redirect()->route('dashboard.level-selection')
                ->withErrors(['group' => 'Invalid level group selected.']);
        }

        $group = $levelGroups[$groupId];
        
        // Log group selection access
        Log::channel('security')->info('level_group_accessed', [
            'user_id' => Auth::id(),
            'group_id' => $groupId,
            'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.level-group-selection', compact('group', 'groupId'));
    }

    /**
     * Get lessons for a level group (combines all levels in the group)
     */
    private function getLessonsForLevelGroup($groupId)
    {
        $levelGroups = $this->getLevelGroups();
        
        if (!isset($levelGroups[$groupId])) {
            return [];
        }

        $allLessons = [];
        $individualLevels = array_keys($levelGroups[$groupId]['levels']);

        // Get lessons from all individual levels in the group
        foreach ($individualLevels as $level) {
            $levelLessons = $this->getLessonsForLevel($level);
            
            // Add level information to each lesson
            foreach ($levelLessons as &$lesson) {
                $lesson['level'] = $level;
                $lesson['level_display'] = $this->getLevelDisplayName($level);
            }
            
            $allLessons = array_merge($allLessons, $levelLessons);
        }

        return $allLessons;
    }

    /**
     * Get display name for level
     */
    private function getLevelDisplayName($level)
    {
        $displayNames = [
            'primary-1' => 'Primary 1',
            'primary-2' => 'Primary 2', 
            'primary-3' => 'Primary 3',
            'primary-4' => 'Primary 4',
            'primary-5' => 'Primary 5',
            'primary-6' => 'Primary 6',
            'jhs-1' => 'JHS 1',
            'jhs-2' => 'JHS 2',
            'jhs-3' => 'JHS 3',
            'shs-1' => 'SHS 1',
            'shs-2' => 'SHS 2',
            'shs-3' => 'SHS 3',
        ];

        return $displayNames[$level] ?? ucwords(str_replace('-', ' ', $level));
    }

    /**
     * Get level groups for the two-step selection process
     */
    public function getLevelGroups()
    {
        return [
            'primary-lower' => [
                'title' => 'Grade/Primary 1-3',
                'description' => 'Lower primary or Elementary school',
                'has_illustration' => false,
                'levels' => [
                    'primary-1' => [
                        'title' => 'Primary 1',
                        'description' => 'Foundation learning for young minds'
                    ],
                    'primary-2' => [
                        'title' => 'Primary 2', 
                        'description' => 'Building on fundamentals'
                    ],
                    'primary-3' => [
                        'title' => 'Primary 3',
                        'description' => 'Developing critical thinking skills'
                    ]
                ]
            ],
            'primary-upper' => [
                'title' => 'Grade/Primary 4-6',
                'description' => 'Upper primary or elementary school',
                'has_illustration' => false,
                'levels' => [
                    'primary-4' => [
                        'title' => 'Primary 4',
                        'description' => 'Advanced primary education'
                    ],
                    'primary-5' => [
                        'title' => 'Primary 5',
                        'description' => 'Preparing for junior high transition'
                    ],
                    'primary-6' => [
                        'title' => 'Primary 6',
                        'description' => 'BECE preparation focus'
                    ]
                ]
            ],
            'jhs' => [
                'title' => 'Grade/JHS 7-9',
                'description' => 'Junior High School or Middle school',
                'has_illustration' => true,
                'levels' => [
                    'jhs-1' => [
                        'title' => 'JHS 1',
                        'description' => 'Introduction to junior high curriculum'
                    ],
                    'jhs-2' => [
                        'title' => 'JHS 2',
                        'description' => 'Intermediate junior high studies'
                    ],
                    'jhs-3' => [
                        'title' => 'JHS 3',
                        'description' => 'Final JHS year with BECE preparation'
                    ]
                ]
            ],
            'shs' => [
                'title' => 'Grade/SHS 1-3',
                'description' => 'High school or Senior High School',
                'has_illustration' => false,
                'levels' => [
                    'shs-1' => [
                        'title' => 'SHS 1',
                        'description' => 'Senior high foundation with specialized tracks'
                    ],
                    'shs-2' => [
                        'title' => 'SHS 2',
                        'description' => 'Advanced senior high studies'
                    ],
                    'shs-3' => [
                        'title' => 'SHS 3',
                        'description' => 'Final SHS year with WASSCE preparation'
                    ]
                ]
                    ],
            'university' => [
                'title' => 'University',
                'description' => 'Higher education with specialized programs and courses',
                'has_illustration' => true,
                'programs' => $this->getUniversityPrograms()
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

    /**
     * Get class forum data based on user's grade.
     * In a real application, this would fetch from a database.
     */
    private function getClassForumData($grade)
    {
        $forumTopics = [
            '1' => [
                ['id' => 1, 'title' => 'Welcome to Primary 1 Class!', 'author' => 'Admin', 'posts' => 5, 'last_post' => '2 hours ago'],
                ['id' => 2, 'title' => 'Help with Basic Counting', 'author' => 'Student A', 'posts' => 3, 'last_post' => '1 day ago'],
            ],
            '2' => [
                ['id' => 3, 'title' => 'Discussion: Simple Addition', 'author' => 'Teacher B', 'posts' => 8, 'last_post' => '4 hours ago'],
            ],
            '3' => [
                ['id' => 4, 'title' => 'Understanding Multiplication', 'author' => 'Student C', 'posts' => 10, 'last_post' => '30 mins ago'],
            ],
            '4' => [
                ['id' => 5, 'title' => 'Fractions Explained', 'author' => 'Teacher D', 'posts' => 12, 'last_post' => '1 hour ago'],
            ],
            '5' => [
                ['id' => 6, 'title' => 'Preparing for Junior High', 'author' => 'Admin', 'posts' => 7, 'last_post' => '5 hours ago'],
            ],
            '6' => [
                ['id' => 7, 'title' => 'BECE Math Strategies', 'author' => 'Teacher E', 'posts' => 15, 'last_post' => '10 mins ago'],
            ],
            '7' => [ // JHS 1
                ['id' => 8, 'title' => 'Algebraic Expressions', 'author' => 'Teacher F', 'posts' => 20, 'last_post' => '1 hour ago'],
                ['id' => 9, 'title' => 'English Grammar Challenges', 'author' => 'Student D', 'posts' => 18, 'last_post' => '2 hours ago'],
            ],
            '8' => [ // JHS 2
                ['id' => 10, 'title' => 'Geometry Problems', 'author' => 'Teacher G', 'posts' => 25, 'last_post' => '3 hours ago'],
                ['id' => 11, 'title' => 'Science Project Ideas', 'author' => 'Student E', 'posts' => 22, 'last_post' => '4 hours ago'],
            ],
            '9' => [ // JHS 3
                ['id' => 12, 'title' => 'BECE Science Revision', 'author' => 'Teacher H', 'posts' => 30, 'last_post' => '15 mins ago'],
            ],
            'shs-1' => [
                ['id' => 13, 'title' => 'Advanced Math Concepts', 'author' => 'Teacher I', 'posts' => 35, 'last_post' => '1 hour ago'],
            ],
            'shs-2' => [
                ['id' => 14, 'title' => 'Literature Deep Dive', 'author' => 'Teacher J', 'posts' => 40, 'last_post' => '2 hours ago'],
            ],
            'shs-3' => [
                ['id' => 15, 'title' => 'WASSCE Exam Tips', 'author' => 'Teacher K', 'posts' => 45, 'last_post' => '30 mins ago'],
            ],
        ];

        return $forumTopics[$grade] ?? [];
    }
}
