<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SavedLesson;
use App\Models\VirtualClass;
use App\Models\Comment;
use App\Models\Video;
use App\Models\UserNote;
use App\Models\Course;

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
     * Show level selection page - Updated with University Years
     */
    public function levelSelection()
    {
        $user = Auth::user();
        $hasActiveSubscription = $user->currentSubscription && $user->currentSubscription->isActive();
        $isInTrial = $user->currentSubscription && $user->currentSubscription->isInTrial();

        Log::channel('security')->info('level_selection_accessed', [
            'user_id' => Auth::id(),
            'has_subscription' => $hasActiveSubscription,
            'is_trial' => $isInTrial,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

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
     * Handle level group selection and redirect appropriately
     */
    public function selectLevelGroup(Request $request, $groupId)
    {
        $validGroups = ['primary-lower', 'primary-upper', 'jhs', 'shs', 'university'];

        if (!in_array($groupId, $validGroups)) {
            return redirect()->route('dashboard.level-selection')
                ->withErrors(['group' => 'Invalid level group selected.']);
        }

        $user = Auth::user();
        if (!$this->hasAccessToLevelGroup($user, $groupId)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this content.');
        }

        // Set the user's grade to the lowest level in the selected group
        $lowestGrade = $this->getLowestGradeForLevelGroup($groupId);
        $user->update(['grade' => $lowestGrade]);
        session(['selected_level_group' => $groupId]);

        // Initialize user progress for the selected level group
        $this->initializeUserProgressForLevelGroup($user, $groupId);

        Log::channel('security')->info('level_group_selected', [
            'user_id' => Auth::id(),
            'level_group' => $groupId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // For all levels including university, redirect to digilearn
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
     * Show DigiLearn page (updated to handle university structure)
     */
    public function digilearn()
    {
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevelGroup = session('selected_level_group');
        $user = Auth::user();

        // University is now handled directly in digilearn view
        // No special redirect needed

        if (!$this->hasAccessToLevelGroup($user, $selectedLevelGroup)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this content.');
        }

        Log::channel('security')->info('digilearn_accessed', [
            'user_id' => Auth::id(),
            'selected_level_group' => $selectedLevelGroup,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Handle university level specially to show courses instead of lessons
        if ($selectedLevelGroup === 'university') {
            $universityCourses = $this->getUniversityCourses();
            $universityCourses = $this->filterCoursesBySubscription($user, $universityCourses);
            return view('dashboard.digilearn', compact('selectedLevelGroup', 'universityCourses'));
        }

        $lessons = $this->getLessonsForLevelGroup($selectedLevelGroup);
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
     * Filter courses based on user's subscription
     */
    private function filterCoursesBySubscription($user, $courses)
    {
        $currentSubscription = $user->currentSubscription;
        
        // If no subscription, limit to first 2 courses
        if (!$currentSubscription || (!$currentSubscription->isActive() && !$currentSubscription->isInTrial())) {
            return array_slice($courses, 0, 2);
        }

        // Full access for subscribed users
        return $courses;
    }

    /**
     * Get lessons for a specific level (grade level)
     */
    private function getLessonsForLevel($level)
    {
        try {
            // Convert level format from "primary-1" to "Primary 1" to match database
            $dbLevel = $this->convertLevelFormat($level);

            // Query approved videos for the specific grade level
            $videos = Video::approved()
                ->where('grade_level', $dbLevel)
                ->with(['uploader', 'documents', 'quiz'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('DashboardController::getLessonsForLevel - Videos with documents', [
                'level' => $level,
                'videos_count' => $videos->count(),
                'videos_with_documents' => $videos->filter(function($video) {
                    return $video->documents->count() > 0;
                })->map(function($video) {
                    return [
                        'id' => $video->id,
                        'title' => $video->title,
                        'documents_count' => $video->documents->count(),
                        'documents' => $video->documents->pluck('title')->toArray()
                    ];
                })->toArray()
            ]);

            $lessons = [];
            foreach ($videos as $index => $video) {
                $lesson = [
                    'id' => $video->id,
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'duration' => $this->formatDuration($video->duration_seconds),
                    'video_url' => $video->getVideoUrl(),
                    'thumbnail' => $video->getThumbnailUrl(),
                    'instructor' => $video->uploader ? $video->uploader->name : 'Unknown',
                    'subject' => $this->getSubjectFromLevel($level),
                    'year' => date('Y'),
                    'level' => $level,
                    'level_display' => $this->getLevelDisplayName($level),
                    'documents_count' => $video->documents->count(),
                    'documents' => $video->documents->map(function($doc) {
                        return [
                            'id' => $doc->id,
                            'title' => $doc->title,
                            'file_path' => $doc->file_path,
                            'description' => $doc->description,
                            'uploaded_by' => $doc->uploader ? $doc->uploader->name : 'Unknown',
                            'views' => $doc->views ?? 0,
                        ];
                    }),
                    'has_quiz' => $video->quiz ? true : false,
                    'views' => $video->views ?? 0,
                    'is_featured' => $video->is_featured,
                ];

                $lessons[] = $lesson;
            }

            Log::info('getLessonsForLevel query results', [
                'level' => $level,
                'videos_found' => $videos->count(),
                'lessons_returned' => count($lessons),
                'timestamp' => now()->toISOString()
            ]);

            return $lessons;
        } catch (\Exception $e) {
            Log::error('getLessonsForLevel error', [
                'level' => $level,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Convert level format from "primary-1" to "Primary 1" to match database
     */
    private function convertLevelFormat($level)
    {
        $levelMapping = [
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

        return $levelMapping[$level] ?? ucwords(str_replace('-', ' ', $level));
    }

    /**
     * Get subject name from level (for display purposes)
     */
    private function getSubjectFromLevel($level)
    {
        $subjectMapping = [
            'primary-1' => 'Primary Education',
            'primary-2' => 'Primary Education',
            'primary-3' => 'Primary Education',
            'primary-4' => 'Primary Education',
            'primary-5' => 'Primary Education',
            'primary-6' => 'Primary Education',
            'jhs-1' => 'Junior High School',
            'jhs-2' => 'Junior High School',
            'jhs-3' => 'Junior High School',
            'shs-1' => 'Senior High School',
            'shs-2' => 'Senior High School',
            'shs-3' => 'Senior High School',
        ];

        return $subjectMapping[$level] ?? 'General Education';
    }

    /**
     * Get all university courses in a format similar to lessons
     */
    private function getUniversityCourses()
    {
        try {
            // Query published courses with their relationships
            $courses = Course::published()
                ->with(['creator', 'videos', 'documents', 'quizzes'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedCourses = [];
            foreach ($courses as $course) {
                $stats = $course->getStats();

                $formattedCourse = [
                    'id' => $course->id,
                    'title' => $course->title,
                    'subject' => $course->subject,
                    'duration' => $this->calculateCourseDuration($course),
                    'thumbnail' => $course->getThumbnailUrl(),
                    'video_url' => $this->getCourseIntroVideoUrl($course),
                    'instructor' => $course->creator ? $course->creator->name : 'Unknown',
                    'year' => $course->created_at->format('Y'),
                    'level_display' => $course->subject . ' ' . $course->id,
                    'description' => $course->description,
                    'credit_hours' => $stats['total_content'], // Using total content as credit hours approximation
                    'semester' => 1, // Default to semester 1
                    'lessons_count' => $stats['videos_count'],
                    'documents_count' => $stats['documents_count'],
                    'quizzes_count' => $stats['quizzes_count'],
                    'total_content' => $stats['total_content'],
                    'first_lesson_id' => $this->getFirstLessonId($course),
                ];

                $formattedCourses[] = $formattedCourse;
            }

            Log::info('getUniversityCourses query results', [
                'courses_found' => $courses->count(),
                'courses_returned' => count($formattedCourses),
                'timestamp' => now()->toISOString()
            ]);

            return $formattedCourses;
        } catch (\Exception $e) {
            Log::error('getUniversityCourses error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Calculate total duration for a course based on its videos
     */
    private function calculateCourseDuration($course)
    {
        $totalSeconds = $course->videos->sum('duration_seconds');
        return $this->formatDuration($totalSeconds);
    }

    /**
     * Get intro video URL for a course
     */
    private function getCourseIntroVideoUrl($course)
    {
        $firstVideo = $course->videos->first();
        return $firstVideo ? $firstVideo->getVideoUrl() : null;
    }

    /**
     * Get first lesson ID for a course
     */
    private function getFirstLessonId($course)
    {
        $firstVideo = $course->videos->first();
        return $firstVideo ? $firstVideo->id : null;
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
     * View specific lesson (updated to work with level groups and courses)
     */
    public function viewLesson($lessonId, Request $request)
    {
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection');
        }

        $selectedLevelGroup = session('selected_level_group');
        $user = Auth::user();

        // Check if this is a course view (when course_id is passed)
        $courseId = $request->get('course_id');
        if ($courseId) {
            // Load course content instead of individual lesson
            $course = \App\Models\Course::with([
                'creator',
                'videos' => function($query) {
                    $query->orderBy('course_videos.order');
                },
                'documents' => function($query) {
                    $query->orderBy('course_documents.order');
                },
                'quizzes' => function($query) {
                    $query->orderBy('course_quizzes.order');
                }
            ])->findOrFail($courseId);

            // Check if course is published
            if (!$course->isPublished()) {
                return redirect()->route('dashboard.digilearn')
                    ->withErrors(['course' => 'Course not available.']);
            }

            // Increment views for all videos in the course
            $course->videos()->increment('views');

            $stats = $course->getStats();

            Log::channel('security')->info('course_viewed_via_lesson', [
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'course_title' => $course->title,
                'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
                'ip' => request()->ip(),
                'timestamp' => Carbon::now()->toISOString()
            ]);

            return view('dashboard.lesson-view', compact('course', 'selectedLevelGroup', 'stats'));
        }

        // Debug: Log lesson loading attempt
        Log::info('Lesson View Debug - Starting', [
            'lesson_id' => $lessonId,
            'selected_level_group' => $selectedLevelGroup,
            'user_id' => Auth::id(),
            'request_params' => $request->all()
        ]);

        // University: search across all course lessons
        if ($selectedLevelGroup === 'university') {
            $allLessons = [];
            // Build a flat list of all university lessons by iterating known programs
            foreach (['computer-science', 'business-administration'] as $programKey) {
                $courses = $this->getCoursesForProgram($programKey);
                foreach ($courses as $course) {
                    $courseLessons = $this->getLessonsForCourse($course['id']);
                    foreach ($courseLessons as $ul) {
                        // Attach helpful metadata to lesson (subject, instructor, year, level_display)
                        $ul['subject'] = $course['name'] ?? ($course['code'] ?? '');
                        $ul['instructor'] = $course['instructor'] ?? ($ul['instructor'] ?? '');
                        $ul['year'] = date('Y');
                        $ul['level_display'] = $course['code'] ?? ($course['id'] ?? '');
                        $allLessons[] = $ul;
                    }
                }
            }

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

            // Increment video views for university lessons
            if (isset($lesson['video_id'])) {
                $video = Video::find($lesson['video_id']);
                if ($video) {
                    $video->increment('views');
                }
            }

            // Related lessons from same course if possible
            $relatedLessons = array_values(array_filter($allLessons, function ($l) use ($lessonId) {
                return $l['id'] !== $lessonId;
            }));
            $relatedLessons = array_slice($relatedLessons, 0, 8);

            Log::channel('security')->info('lesson_viewed', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'selected_level_group' => $selectedLevelGroup,
                'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
                'ip' => request()->ip(),
                'timestamp' => Carbon::now()->toISOString()
            ]);

            return view('dashboard.lesson-view', compact('lesson', 'selectedLevelGroup', 'relatedLessons'));
        }

        // Get all lessons from the level group (non-university)
        Log::info('Lesson View Debug - Non-University Path', [
            'lesson_id' => $lessonId,
            'selected_level_group' => $selectedLevelGroup
        ]);

        $allLessons = $this->getLessonsForLevelGroup($selectedLevelGroup);
        Log::info('Lesson View Debug - Level Group Lessons', [
            'level_group' => $selectedLevelGroup,
            'total_lessons' => count($allLessons),
            'lesson_ids' => array_column($allLessons, 'id')
        ]);

        $lesson = null;
        foreach ($allLessons as $l) {
            if ($l['id'] == $lessonId) {
                $lesson = $l;
                Log::info('Lesson View Debug - Lesson Found in Level Group', [
                    'lesson_id' => $lessonId,
                    'lesson_data' => $l
                ]);
                break;
            }
        }

        if (!$lesson) {
            Log::error('Lesson View Debug - Lesson Not Found in Level Group', [
                'lesson_id' => $lessonId,
                'level_group' => $selectedLevelGroup,
                'total_lessons_searched' => count($allLessons)
            ]);
            return redirect()->route('dashboard.digilearn')
                ->withErrors(['lesson' => 'Lesson not found.']);
        }

        // Check if user has access to this specific lesson
        if (!$this->hasLessonAccess($user, $lesson)) {
            return redirect()->route('pricing')
                ->with('warning', 'Please upgrade your subscription to access this lesson.');
        }

        // Increment video views
        if (isset($lesson['video_id'])) {
            $video = Video::find($lesson['video_id']);
            if ($video) {
                $video->increment('views');
            }
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
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        // Find the video associated with this lesson
        $video = $this->findVideoByLessonId($lessonId);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video not found'], 404);
        }

        // Check if user has access to comment
        if (!$this->hasLessonAccess(Auth::user(), ['id' => $lessonId])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $comment = Comment::create([
                'content' => $request->comment,
                'user_id' => Auth::id(),
                'video_id' => $video->id,
                'parent_id' => $request->parent_id,
                'is_approved' => true, // Auto-approve for now
            ]);

            // Load the user relationship for the response
            $comment->load('user');

            // Fire the event for real-time broadcasting
            broadcast(new \App\Events\CommentCreated($comment))->toOthers();

            Log::info('lesson_comment_posted', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'video_id' => $video->id,
                'comment_id' => $comment->id,
                'parent_id' => $request->parent_id,
                'comment_length' => strlen($request->comment),
                'subscription_plan' => Auth::user()->currentSubscription?->pricingPlan?->name ?? 'Free',
                'timestamp' => Carbon::now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'time_ago' => $comment->time_ago,
                    'likes_count' => $comment->likes_count,
                    'dislikes_count' => $comment->dislikes_count,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('comment_post_error', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to post comment'], 500);
        }
    }

    /**
     * Get comments for a lesson
     */
    public function getComments($lessonId)
    {
        // Find the video associated with this lesson
        $video = $this->findVideoByLessonId($lessonId);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video not found'], 404);
        }

        try {
            $comments = Comment::forVideo($video->id)->get();

            $formattedComments = $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                        'avatar_initial' => $comment->avatar_initial,
                    ],
                    'time_ago' => $comment->time_ago,
                    'likes_count' => $comment->likes_count,
                    'dislikes_count' => $comment->dislikes_count,
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user' => [
                                'name' => $reply->user->name,
                                'avatar' => $reply->user->avatar,
                                'avatar_initial' => $reply->avatar_initial,
                            ],
                            'time_ago' => $reply->time_ago,
                            'likes_count' => $reply->likes_count,
                            'dislikes_count' => $reply->dislikes_count,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'comments' => $formattedComments,
                'total_count' => $comments->count() + $comments->sum(function ($comment) {
                    return $comment->replies->count();
                })
            ]);
        } catch (\Exception $e) {
            Log::error('get_comments_error', [
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to load comments'], 500);
        }
    }

    /**
     * Like or dislike a comment
     */
    public function likeComment(Request $request, $commentId)
    {
        $request->validate([
            'action' => 'required|in:like,dislike,unlike,undislike'
        ]);

        try {
            $comment = Comment::findOrFail($commentId);

            switch ($request->action) {
                case 'like':
                    $comment->increment('likes_count');
                    break;
                case 'dislike':
                    $comment->increment('dislikes_count');
                    break;
                case 'unlike':
                    $comment->decrement('likes_count');
                    break;
                case 'undislike':
                    $comment->decrement('dislikes_count');
                    break;
            }

            return response()->json([
                'success' => true,
                'likes_count' => $comment->likes_count,
                'dislikes_count' => $comment->dislikes_count
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update comment'], 500);
        }
    }

    /**
     * Find video by lesson ID
     */
    private function findVideoByLessonId($lessonId)
    {
        // For university lessons, search through all courses
        if (session('selected_level_group') === 'university') {
            // Search through university courses to find the lesson and get its video_id
            $allCourses = [];
            foreach (['computer-science', 'business-administration'] as $programKey) {
                $courses = $this->getCoursesForProgram($programKey);
                $allCourses = array_merge($allCourses, $courses);
            }

            foreach ($allCourses as $course) {
                $courseLessons = $this->getLessonsForCourse($course['id']);
                foreach ($courseLessons as $lesson) {
                    if ($lesson['id'] == $lessonId) {
                        return Video::find($lesson['video_id']);
                    }
                }
            }
        }

        // For regular lessons, search through all level groups
        $allLessons = [];
        $levelGroups = $this->getLevelGroups();

        foreach ($levelGroups as $groupId => $group) {
            if (isset($group['levels'])) {
                foreach ($levelGroups[$groupId]['levels'] as $level => $levelData) {
                    $levelLessons = $this->getLessonsForLevel($level);
                    $allLessons = array_merge($allLessons, $levelLessons);
                }
            }
        }

        foreach ($allLessons as $lesson) {
            if ($lesson['id'] == $lessonId) {
                // Use the video_id from the lesson data
                return Video::find($lesson['video_id']);
            }
        }

        return null;
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
     * Show University Years Selection (Uni-1, Uni-2, etc.)
     */
    public function universityYears()
    {
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $user = Auth::user();
        $universityYears = $this->getUniversityYears();

        Log::channel('security')->info('university_years_accessed', [
            'user_id' => Auth::id(),
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.years', compact('universityYears'));
    }

    /**
     * Handle University Year Selection
     */
    public function selectUniversityYear($yearId)
    {
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $validYears = ['uni-1', 'uni-2', 'uni-3', 'uni-4'];
        
        if (!in_array($yearId, $validYears)) {
            return redirect()->route('dashboard.university.years')
                ->withErrors(['year' => 'Invalid university year selected.']);
        }

        session(['selected_university_year' => $yearId]);

        return redirect()->route('dashboard.university.programs', $yearId)
            ->with('success', 'University year selected successfully!');
    }

    /**
     * Show University Programs for Selected Year
     */
    public function universityPrograms($yearId)
    {
        if (session('selected_level_group') !== 'university' || session('selected_university_year') !== $yearId) {
            return redirect()->route('dashboard.university.years');
        }

        $user = Auth::user();
        $programs = $this->getUniversityPrograms($yearId);
        $yearInfo = $this->getUniversityYearInfo($yearId);

        Log::channel('security')->info('university_programs_accessed', [
            'user_id' => Auth::id(),
            'university_year' => $yearId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.programs', compact('programs', 'yearInfo', 'yearId'));
    }

    /**
     * Show Program Courses (DigiLearn Style)
     */
    public function programCourses($yearId, $programId)
    {
        if (session('selected_level_group') !== 'university' || session('selected_university_year') !== $yearId) {
            return redirect()->route('dashboard.university.years');
        }

        $user = Auth::user();
        $program = $this->getProgramById($programId);
        
        if (!$program) {
            return redirect()->route('dashboard.university.programs', $yearId)
                ->withErrors(['program' => 'Program not found.']);
        }

        $courses = $this->getCoursesForProgram($programId);
        session(['selected_program' => $programId]);

        Log::channel('security')->info('program_courses_accessed', [
            'user_id' => Auth::id(),
            'university_year' => $yearId,
            'program_id' => $programId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.courses-digilearn', compact('program', 'courses', 'yearId', 'programId'));
    }

    /**
     * Show Course Lessons (Individual Lesson View)
     */
    public function courseLessons($yearId, $programId, $courseId)
    {
        if (session('selected_level_group') !== 'university' || session('selected_university_year') !== $yearId) {
            return redirect()->route('dashboard.university.years');
        }

        $user = Auth::user();
        $course = $this->getCourseById($courseId);
        
        if (!$course) {
            return redirect()->route('dashboard.university.program.courses', [$yearId, $programId])
                ->withErrors(['course' => 'Course not found.']);
        }

        $lessons = $this->getLessonsForCourse($courseId);
        session(['selected_course' => $courseId]);

        Log::channel('security')->info('course_lessons_accessed', [
            'user_id' => Auth::id(),
            'university_year' => $yearId,
            'program_id' => $programId,
            'course_id' => $courseId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.university.lessons', compact('course', 'lessons', 'yearId', 'programId', 'courseId'));
    }

    /**
     * Show Course Lessons by Course ID only (direct from DigiLearn university view)
     */
    public function courseLessonsById($courseId)
    {
        // Ensure user selected university level group
        if (session('selected_level_group') !== 'university') {
            return redirect()->route('dashboard.level-selection');
        }

        $user = Auth::user();

        // Find the course by ID across all years/programs
        $course = $this->getCourseById($courseId);
        if (!$course) {
            return redirect()->route('dashboard.digilearn')
                ->withErrors(['course' => 'Course not found.']);
        }

        // Get lessons for the course
        $lessons = $this->getLessonsForCourse($courseId);

        Log::channel('security')->info('course_lessons_accessed_by_id', [
            'user_id' => Auth::id(),
            'course_id' => $courseId,
            'subscription_plan' => $user->currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        // Note: university/lessons view only relies on $course and $lessons
        return view('dashboard.university.lessons', compact('course', 'lessons'));
    }

    /**
     * Get University Years
     */
    private function getUniversityYears()
    {
        return [
            [
                'id' => 'uni-1',
                'name' => 'University Year 1',
                'description' => 'First year undergraduate programs and foundation courses',
                'programs_count' => 12,
                'thumbnail' => 'images/university/year1.jpg',
                'level' => 'Undergraduate',
                'year_number' => 1
            ],
            [
                'id' => 'uni-2',
                'name' => 'University Year 2',
                'description' => 'Second year undergraduate programs with specialized tracks',
                'programs_count' => 15,
                'thumbnail' => 'images/university/year2.jpg',
                'level' => 'Undergraduate',
                'year_number' => 2
            ],
            [
                'id' => 'uni-3',
                'name' => 'University Year 3',
                'description' => 'Third year undergraduate programs with advanced coursework',
                'programs_count' => 18,
                'thumbnail' => 'images/university/year3.jpg',
                'level' => 'Undergraduate',
                'year_number' => 3
            ],
            [
                'id' => 'uni-4',
                'name' => 'University Year 4',
                'description' => 'Final year undergraduate programs and capstone projects',
                'programs_count' => 14,
                'thumbnail' => 'images/university/year4.jpg',
                'level' => 'Undergraduate',
                'year_number' => 4
            ]
        ];
    }

    /**
     * Get University Year Info
     */
    private function getUniversityYearInfo($yearId)
    {
        $years = $this->getUniversityYears();
        return collect($years)->firstWhere('id', $yearId);
    }

    /**
     * Get University Programs for specific year
     */
    private function getUniversityPrograms($yearId)
    {
        $allPrograms = [
            'uni-1' => [
                [
                    'id' => 'computer-science-1',
                    'name' => 'Computer Science Fundamentals',
                    'description' => 'Introduction to programming, algorithms, and computer systems',
                    'duration' => '1 Year',
                    'courses_count' => 8,
                    'thumbnail' => 'images/programs/cs-year1.jpg',
                    'level' => 'Year 1',
                    'department' => 'Engineering & Technology'
                ],
                [
                    'id' => 'business-admin-1',
                    'name' => 'Business Fundamentals',
                    'description' => 'Basic business principles, accounting, and management',
                    'duration' => '1 Year',
                    'courses_count' => 6,
                    'thumbnail' => 'images/programs/business-year1.jpg',
                    'level' => 'Year 1',
                    'department' => 'Business & Economics'
                ],
                [
                    'id' => 'medicine-1',
                    'name' => 'Pre-Medical Sciences',
                    'description' => 'Biology, chemistry, and basic medical terminology',
                    'duration' => '1 Year',
                    'courses_count' => 10,
                    'thumbnail' => 'images/programs/medicine-year1.jpg',
                    'level' => 'Year 1',
                    'department' => 'Health Sciences'
                ]
            ],
            'uni-2' => [
                [
                    'id' => 'computer-science-2',
                    'name' => 'Advanced Programming',
                    'description' => 'Data structures, object-oriented programming, and databases',
                    'duration' => '1 Year',
                    'courses_count' => 10,
                    'thumbnail' => 'images/programs/cs-year2.jpg',
                    'level' => 'Year 2',
                    'department' => 'Engineering & Technology'
                ],
                [
                    'id' => 'business-admin-2',
                    'name' => 'Business Analytics',
                    'description' => 'Statistics, market research, and business intelligence',
                    'duration' => '1 Year',
                    'courses_count' => 8,
                    'thumbnail' => 'images/programs/business-year2.jpg',
                    'level' => 'Year 2',
                    'department' => 'Business & Economics'
                ]
            ],
            'uni-3' => [
                [
                    'id' => 'computer-science-3',
                    'name' => 'Software Engineering',
                    'description' => 'Software design, testing, and project management',
                    'duration' => '1 Year',
                    'courses_count' => 12,
                    'thumbnail' => 'images/programs/cs-year3.jpg',
                    'level' => 'Year 3',
                    'department' => 'Engineering & Technology'
                ]
            ],
            'uni-4' => [
                [
                    'id' => 'computer-science-4',
                    'name' => 'Advanced Computer Science',
                    'description' => 'Capstone projects, internships, and specialization',
                    'duration' => '1 Year',
                    'courses_count' => 6,
                    'thumbnail' => 'images/programs/cs-year4.jpg',
                    'level' => 'Year 4',
                    'department' => 'Engineering & Technology'
                ]
            ]
        ];

        return $allPrograms[$yearId] ?? [];
    }

    /**
     * Get Program by ID
     */
    private function getProgramById($programId)
    {
        $allPrograms = [];
        $years = $this->getUniversityYears();
        
        foreach ($years as $year) {
            $programs = $this->getUniversityPrograms($year['id']);
            $allPrograms = array_merge($allPrograms, $programs);
        }

        return collect($allPrograms)->firstWhere('id', $programId);
    }

    /**
     * Get Courses for Program
     */
    private function getCoursesForProgram($programId)
    {
        try {
            // Map program IDs to subject filters
            $subjectMapping = [
                'computer-science' => 'Computer Science',
                'computer-science-1' => 'Computer Science',
                'computer-science-2' => 'Computer Science',
                'computer-science-3' => 'Computer Science',
                'computer-science-4' => 'Computer Science',
                'business-admin' => 'Business Administration',
                'business-admin-1' => 'Business Administration',
                'business-admin-2' => 'Business Administration',
                'medicine-1' => 'Medicine',
            ];

            $subject = $subjectMapping[$programId] ?? null;

            if (!$subject) {
                Log::warning('getCoursesForProgram: Unknown program ID', [
                    'program_id' => $programId,
                    'timestamp' => now()->toISOString()
                ]);
                return [];
            }

            // Query courses by subject and published status
            $courses = Course::published()
                ->where('subject', $subject)
                ->with(['creator', 'videos', 'documents', 'quizzes'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedCourses = [];
            foreach ($courses as $course) {
                $stats = $course->getStats();

                $formattedCourses[] = [
                    'id' => $course->id,
                    'name' => $course->title,
                    'code' => $course->subject . ' ' . $course->id,
                    'description' => $course->description,
                    'credit_hours' => $stats['total_content'],
                    'semester' => 1, // Default to semester 1
                    'year' => 1, // Default to year 1
                    'lessons_count' => $stats['videos_count'],
                    'thumbnail' => $course->getThumbnailUrl(),
                    'instructor' => $course->creator ? $course->creator->name : 'Unknown',
                    'videos_count' => $stats['videos_count'],
                    'documents_count' => $stats['documents_count'],
                    'quizzes_count' => $stats['quizzes_count'],
                    'total_content' => $stats['total_content'],
                ];
            }

            Log::info('getCoursesForProgram query results', [
                'program_id' => $programId,
                'subject' => $subject,
                'courses_found' => $courses->count(),
                'courses_returned' => count($formattedCourses),
                'timestamp' => now()->toISOString()
            ]);

            return $formattedCourses;
        } catch (\Exception $e) {
            Log::error('getCoursesForProgram error', [
                'program_id' => $programId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Get Course by ID
     */
    private function getCourseById($courseId)
    {
        // Aggregate across known program keys (aligns with getCoursesForProgram keys)
        $allCourses = [];
        foreach (['computer-science', 'business-administration'] as $programKey) {
            $courses = $this->getCoursesForProgram($programKey);
            $allCourses = array_merge($allCourses, $courses);
        }

        return collect($allCourses)->firstWhere('id', $courseId);
    }

    /**
     * Get Lessons for Course
     */
    private function getLessonsForCourse($courseId)
    {
        try {
            // Find the course first
            $course = Course::find($courseId);
            if (!$course) {
                Log::warning('getLessonsForCourse: Course not found', [
                    'course_id' => $courseId,
                    'timestamp' => now()->toISOString()
                ]);
                return [];
            }

            // Get videos for this course with their relationships
            $videos = $course->videos()
                ->with(['documents', 'quiz', 'uploader'])
                ->orderBy('course_videos.order')
                ->get();

            $lessons = [];
            foreach ($videos as $index => $video) {
                $lesson = [
                    'id' => $video->id,
                    'video_id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'duration' => $this->formatDuration($video->duration_seconds),
                    'video_url' => $video->getVideoUrl(),
                    'thumbnail' => $video->getThumbnailUrl(),
                    'instructor' => $video->uploader ? $video->uploader->name : 'Unknown',
                    'week' => ceil(($index + 1) / 3), // Assuming 3 lessons per week
                    'order' => $index + 1,
                    'documents_count' => $video->documents->count(),
                    'documents' => $video->documents->map(function($doc) {
                        return [
                            'id' => $doc->id,
                            'title' => $doc->title,
                            'file_path' => $doc->file_path,
                            'description' => $doc->description,
                            'uploaded_by' => $doc->uploader ? $doc->uploader->name : 'Unknown',
                            'views' => $doc->views ?? 0,
                        ];
                    }),
                    'has_quiz' => $video->quiz ? true : false,
                ];

                $lessons[] = $lesson;
            }

            Log::info('getLessonsForCourse query results', [
                'course_id' => $courseId,
                'course_title' => $course->title,
                'videos_found' => $videos->count(),
                'lessons_returned' => count($lessons),
                'documents_loaded' => collect($lessons)->sum(function($lesson) { return count($lesson['documents'] ?? []); }),
                'timestamp' => now()->toISOString()
            ]);

            return $lessons;
        } catch (\Exception $e) {
            Log::error('getLessonsForCourse error', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty array as fallback
            return [];
        }
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
        // Some groups (e.g., 'university') do not have 'levels' but rather 'years'
        if (!isset($levelGroups[$groupId]['levels']) || !is_array($levelGroups[$groupId]['levels'])) {
            return [];
        }
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
            'uni-1' => 'University Year 1',
            'uni-2' => 'University Year 2',
            'uni-3' => 'University Year 3',
            'uni-4' => 'University Year 4',
            'uni-5' => 'University Year 5',
            'uni-6' => 'University Year 6',
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
                'years' => $this->getUniversityYears()
            ]
        ];
    }

    /**
     * Initialize user progress for the selected level group
     */
    private function initializeUserProgressForLevelGroup($user, $levelGroup)
    {
        try {
            // Check if user already has progress for this level group
            $existingProgress = \App\Models\UserProgress::where('user_id', $user->id)
                ->where('level_group', $levelGroup)
                ->first();

            if (!$existingProgress) {
                // Get grade levels for this level group
                $gradeLevels = $this->getGradeLevelsForLevelGroup($levelGroup);

                // Get actual counts from database for this level group
                $totalLessons = Video::approved()
                    ->whereIn('grade_level', $gradeLevels)
                    ->count();

                $totalQuizzes = \App\Models\Quiz::whereIn('grade_level', $gradeLevels)
                    ->count();

                // Create progress record for the selected level group
                \App\Models\UserProgress::create([
                    'user_id' => $user->id,
                    'current_level' => $levelGroup,
                    'level_group' => $levelGroup,
                    'total_lessons_in_level' => $totalLessons,
                    'completed_lessons' => 0,
                    'total_quizzes_in_level' => $totalQuizzes,
                    'completed_quizzes' => 0,
                    'average_quiz_score' => 0,
                    'completion_percentage' => 0,
                    'level_started_at' => now(),
                ]);

                Log::info('Initialized user progress for level group', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'level_group' => $levelGroup,
                    'total_lessons' => $totalLessons,
                    'total_quizzes' => $totalQuizzes
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to initialize user progress for level group', [
                'user_id' => $user->id,
                'level_group' => $levelGroup,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get grade levels for a level group
     */
    private function getGradeLevelsForLevelGroup($levelGroup)
    {
        $levelMappings = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => [], // University handled differently
        ];

        return $levelMappings[$levelGroup] ?? [];
    }

    /**
     * Get the lowest grade level for a level group (for new user assignment)
     */
    private function getLowestGradeForLevelGroup($levelGroup)
    {
        $lowestGrades = [
            'primary-lower' => 'Primary 1',
            'primary-upper' => 'Primary 4',
            'jhs' => 'JHS 1',
            'shs' => 'SHS 1',
            'university' => 'University Year 1',
        ];

        return $lowestGrades[$levelGroup] ?? $levelGroup;
    }

    /**
     * Format duration from seconds to human readable format
     */
    private function formatDuration($seconds)
    {
        if (!$seconds) return 'Unknown';

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%d min', $minutes);
        } else {
            return sprintf('%d sec', $remainingSeconds);
        }
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

    /**
     * Save user notes for a video
     */
    public function saveUserNotes(Request $request, $videoId)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:1000',
        ]);

        try {
            $userId = Auth::id();

            // Check if notes already exist for this user and video
            $existingNote = UserNote::forUserAndVideo($userId, $videoId)->first();

            if ($existingNote) {
                // Update existing note
                $existingNote->update([
                    'title' => $request->title,
                    'content' => $request->content,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Notes updated successfully!',
                    'action' => 'updated'
                ]);
            } else {
                // Create new note
                UserNote::create([
                    'user_id' => $userId,
                    'video_id' => $videoId,
                    'title' => $request->title,
                    'content' => $request->content,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Notes saved successfully!',
                    'action' => 'created'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('save_user_notes_error', [
                'user_id' => Auth::id(),
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save notes. Please try again.'
            ], 500);
        }
    }

    /**
     * Load user notes for a video
     */
    public function loadUserNotes($videoId)
    {
        try {
            $userId = Auth::id();

            $note = UserNote::forUserAndVideo($userId, $videoId)->first();

            if ($note) {
                return response()->json([
                    'success' => true,
                    'note' => [
                        'id' => $note->id,
                        'title' => $note->title,
                        'content' => $note->content,
                        'created_at' => $note->formatted_created_at,
                        'updated_at' => $note->formatted_updated_at,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'note' => null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('load_user_notes_error', [
                'user_id' => Auth::id(),
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load notes.'
            ], 500);
        }
    }

    /**
     * Delete user notes for a video
     */
    public function deleteUserNotes($videoId)
    {
        try {
            $userId = Auth::id();

            $note = UserNote::forUserAndVideo($userId, $videoId)->first();

            if ($note) {
                $note->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Notes deleted successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No notes found to delete.'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('delete_user_notes_error', [
                'user_id' => Auth::id(),
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notes. Please try again.'
            ], 500);
        }
    }

    /**
     * Get all user notes (for a dashboard or overview)
     */
     public function getAllUserNotes()
     {
         try {
             $userId = Auth::id();

             $notes = UserNote::where('user_id', $userId)
                 ->with('video')
                 ->orderBy('updated_at', 'desc')
                 ->get()
                 ->map(function ($note) {
                     return [
                         'id' => $note->id,
                         'video_id' => $note->video_id,
                         'title' => $note->title ?: 'Untitled Notes',
                         'content_preview' => substr(strip_tags($note->content), 0, 100) . '...',
                         'updated_at' => $note->formatted_updated_at,
                         'video_title' => $note->video ? $note->video->title : 'Unknown Video',
                     ];
                 });

             return response()->json([
                 'success' => true,
                 'notes' => $notes
             ]);
         } catch (\Exception $e) {
             Log::error('get_all_user_notes_error', [
                 'user_id' => Auth::id(),
                 'error' => $e->getMessage()
             ]);

             return response()->json([
                 'success' => false,
                 'message' => 'Failed to load notes.'
             ], 500);
         }
     }

     /**
      * Search lessons within user's level group (YouTube-like search)
      */
     public function searchLessons(Request $request)
     {
         $query = $request->get('q', '');
         $levelGroup = $request->get('level_group', session('selected_level_group'));

         if (!$levelGroup) {
             return response()->json(['success' => false, 'message' => 'No level group selected'], 400);
         }

         $user = Auth::user();
         $gradeLevels = $this->getGradeLevelsForLevelGroup($levelGroup);

         try {
             // YouTube-like search: split query into words and search each word
             $videos = Video::approved()
                 ->whereIn('grade_level', $gradeLevels)
                 ->where(function($q) use ($query) {
                     if (empty($query)) {
                         return; // Return all if no query
                     }

                     $words = explode(' ', trim($query));
                     foreach ($words as $word) {
                         if (!empty(trim($word))) {
                             $q->where(function($subQ) use ($word) {
                                 $subQ->where('title', 'LIKE', "%{$word}%")
                                      ->orWhere('description', 'LIKE', "%{$word}%");
                             });
                         }
                     }
                 })
                 ->with(['uploader', 'documents', 'quiz'])
                 ->orderBy('created_at', 'desc')
                 ->limit(50) // Limit for performance
                 ->get();

             // Format results like getLessonsForLevel
             $lessons = [];
             foreach ($videos as $video) {
                 $lesson = [
                     'id' => $video->id,
                     'video_id' => $video->id,
                     'title' => $video->title,
                     'description' => $video->description,
                     'duration' => $this->formatDuration($video->duration_seconds),
                     'video_url' => $video->getVideoUrl(),
                     'thumbnail' => $video->getThumbnailUrl(),
                     'instructor' => $video->uploader ? $video->uploader->name : 'Unknown',
                     'subject' => $this->getSubjectFromLevel($this->convertLevelFormatBack($video->grade_level)),
                     'year' => date('Y'),
                     'level' => $this->convertLevelFormatBack($video->grade_level),
                     'level_display' => $this->getLevelDisplayName($this->convertLevelFormatBack($video->grade_level)),
                     'documents_count' => $video->documents->count(),
                     'documents' => $video->documents->map(function($doc) {
                         return [
                             'id' => $doc->id,
                             'title' => $doc->title,
                             'file_path' => $doc->file_path,
                             'description' => $doc->description,
                             'uploaded_by' => $doc->uploader ? $doc->uploader->name : 'Unknown',
                             'views' => $doc->views ?? 0,
                         ];
                     }),
                     'has_quiz' => $video->quiz ? true : false,
                     'views' => $video->views ?? 0,
                     'is_featured' => $video->is_featured,
                 ];

                 $lessons[] = $lesson;
             }

             // Apply subscription filtering
             $filteredLessons = $this->filterLessonsBySubscription($user, $lessons);

             Log::info('search_lessons_executed', [
                 'user_id' => Auth::id(),
                 'query' => $query,
                 'level_group' => $levelGroup,
                 'grade_levels' => $gradeLevels,
                 'total_videos_found' => $videos->count(),
                 'filtered_lessons_returned' => count($filteredLessons),
                 'timestamp' => now()->toISOString()
             ]);

             return response()->json([
                 'success' => true,
                 'lessons' => $filteredLessons,
                 'query' => $query,
                 'level_group' => $levelGroup,
                 'total_results' => count($filteredLessons)
             ]);

         } catch (\Exception $e) {
             Log::error('search_lessons_error', [
                 'user_id' => Auth::id(),
                 'query' => $query,
                 'level_group' => $levelGroup,
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
             ]);

             return response()->json([
                 'success' => false,
                 'message' => 'Search failed. Please try again.',
                 'error' => $e->getMessage()
             ], 500);
         }
     }

     /**
      * Convert grade level back to level format (e.g., "Primary 1" -> "primary-1")
      */
     private function convertLevelFormatBack($gradeLevel)
     {
         $levelMapping = [
             'Primary 1' => 'primary-1',
             'Primary 2' => 'primary-2',
             'Primary 3' => 'primary-3',
             'Primary 4' => 'primary-4',
             'Primary 5' => 'primary-5',
             'Primary 6' => 'primary-6',
             'JHS 1' => 'jhs-1',
             'JHS 2' => 'jhs-2',
             'JHS 3' => 'jhs-3',
             'SHS 1' => 'shs-1',
             'SHS 2' => 'shs-2',
             'SHS 3' => 'shs-3',
         ];

         return $levelMapping[$gradeLevel] ?? strtolower(str_replace(' ', '-', $gradeLevel));
     }
}
