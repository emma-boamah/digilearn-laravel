<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CookieController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Models\User;

// Include debug routes in development
if (app()->environment(['local', 'development', 'testing'])) {
    require __DIR__ . '/debug.php';
}

Route::get('/session-test', function (Request $request) {
    $count = $request->session()->get('count', 0);
    $request->session()->put('count', $count + 1);
    return response()->json([
        'count' => $count + 1,
        'session_id' => session()->getId(),
        'session_data' => $request->session()->all(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/guidelines/account-suspension', function () {
    return view('guidelines.account-suspension');
})->name('guidelines.account-suspension');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/pricing/pricing-details', [PricingController::class, 'show'])->name('pricing-details');

// Payment routes (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/initiate', [App\Http\Controllers\PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.cancel');
});

// Paystack callback (NO auth - must be public for redirects)
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'callback'])
    ->name('payment.callback');

// Paystack webhook (no CSRF, no auth)
Route::post('/webhooks/paystack', [App\Http\Controllers\PaymentController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.paystack');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:login');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup.submit')->middleware('throttle:signup');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/unlock', [AuthController::class, 'showUnlock'])->name('unlock');
Route::post('/unlock', [AuthController::class, 'unlock'])->name('unlock.submit');
Route::get('/suspended', [AuthController::class, 'showSuspended'])->name('auth.suspended');
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->middleware('throttle.redirect:'.config('services.google.rate_limit', 5).',1')
    ->name('auth.google');
Route::get('auth/google/callback/route', [GoogleController::class, 'handleGoogleCallback'])
    ->middleware('throttle.redirect:'.config('services.google.rate_limit', 5).',1')
    ->name('auth.google.callback');

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
    ->middleware('throttle:5,60') // 5 attempts per hour
    ->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Form Submission Routes (Rate Limited)
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:forms'])->group(function () {
    Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
    Route::post('/feedback/submit', [ContactController::class, 'submitFeedback'])->name('feedback.submit');
    Route::post('/newsletter/subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Level selection and dashboard - unified method handles both initial selection and changes
    Route::get('/dashboard/level-selection', [DashboardController::class, 'levelSelection'])->name('dashboard.level-selection');
    Route::get('/dashboard/level-group/{groupId}', [DashboardController::class, 'showLevelGroup'])->name('dashboard.level-group');
    Route::post('/dashboard/select-level-group/{groupId}', [DashboardController::class, 'selectLevelGroup'])->name('dashboard.select-level-group');
    Route::get('/dashboard/main', [DashboardController::class, 'main'])->name('dashboard.main');
    Route::get('/dashboard/digilearn', [DashboardController::class, 'digilearn'])->name('dashboard.digilearn');
    Route::get('/dashboard/personalized', [DashboardController::class, 'personalized'])->name('dashboard.personalized');
    Route::get('/dashboard/shop', [DashboardController::class, 'shop'])->name('dashboard.shop');
    Route::get('/dashboard/change-level', [DashboardController::class, 'levelSelection'])->name('dashboard.change-level');

    // Lessons
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::get('/dashboard/lesson/{lessonId}', [DashboardController::class, 'viewLesson'])->name('dashboard.lesson.view');
        Route::post('/dashboard/lesson/{lessonId}/notes', [DashboardController::class, 'saveNotes'])->name('dashboard.lesson.notes');
        Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');
        Route::get('/dashboard/lesson/{lessonId}/check-saved', [DashboardController::class, 'checkLessonSaved'])->name('dashboard.lesson.check-saved');
        Route::post('/dashboard/lesson/{lessonId}/save', [DashboardController::class, 'saveLesson'])->name('dashboard.lesson.save');
        Route::delete('/dashboard/lesson/{lessonId}/unsave', [DashboardController::class, 'unsaveLesson'])->name('dashboard.lesson.unsave');

        // Documents
        Route::get('/dashboard/lesson/{lessonId}/document/{type}', [DocumentController::class, 'viewDocument'])
            ->name('dashboard.lesson.document')->where('type', 'pdf|ppt');
        Route::get('/dashboard/lesson/{lessonId}/document/{type}/content', [DocumentController::class, 'viewDocumentContent'])
            ->name('dashboard.lesson.document.content')->where('type', 'pdf|ppt');
        Route::post('/dashboard/lesson/{lessonId}/document/{type}/save', [DocumentController::class, 'saveDocumentChanges'])
            ->name('dashboard.lesson.document.save')->where('type', 'pdf|ppt');
        Route::get('/dashboard/lesson/{lessonId}/ppt/create', [DocumentController::class, 'createPpt'])->name('dashboard.lesson.ppt.create');
        Route::post('/dashboard/lesson/{lessonId}/ppt/store', [DocumentController::class, 'storePpt'])->name('dashboard.lesson.ppt.store');
        Route::post('/dashboard/lesson/{lessonId}/ppt/{pptId}/update', [DocumentController::class, 'updatePpt'])->name('dashboard.lesson.ppt.update');
    });

    // User Notes for Videos
    Route::post('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'saveUserNotes'])->name('dashboard.lesson.user-notes.save');
    Route::get('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'loadUserNotes'])->name('dashboard.lesson.user-notes.load');
    Route::delete('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'deleteUserNotes'])->name('dashboard.lesson.user-notes.delete');
    Route::get('/dashboard/user-notes', [DashboardController::class, 'getAllUserNotes'])->name('dashboard.user-notes.all');

    // University years selection
    Route::get('/dashboard/university/years', [DashboardController::class, 'universityYears'])
        ->name('dashboard.university.years');
    
    // University year selection
    Route::post('/dashboard/university/year/{yearId}', [DashboardController::class, 'selectUniversityYear'])
        ->name('dashboard.university.select-year');
    
    // University programs for specific year
    Route::get('/dashboard/university/{yearId}/programs', [DashboardController::class, 'universityPrograms'])
        ->name('dashboard.university.programs');
    
    // Program courses (DigiLearn style)
    Route::get('/dashboard/university/{yearId}/program/{programId}/courses', [DashboardController::class, 'programCourses'])
        ->name('dashboard.university.program.courses');
    
    // Course lessons
    Route::get('/dashboard/university/{yearId}/program/{programId}/course/{courseId}/lessons', [DashboardController::class, 'courseLessons'])
        ->name('dashboard.university.course.lessons');

    // Course lessons by course ID only (direct from DigiLearn university view)
    Route::get('/dashboard/university/course/{courseId}/lessons', [DashboardController::class, 'courseLessonsById'])
        ->name('dashboard.university.course.lessons.by-id');

    // Saved lessons
    Route::get('/dashboard/saved-lessons', [DashboardController::class, 'savedLessons'])->name('dashboard.saved-lessons');


    // Progress
    Route::get('/dashboard/my-progress', [ProgressController::class, 'index'])->name('dashboard.my-progress');
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::post('/dashboard/lesson/{lessonId}/progress', [ProgressController::class, 'recordLessonProgress'])->name('dashboard.lesson.progress');
        Route::post('/dashboard/quiz/{quizId}/attempt', [ProgressController::class, 'recordQuizAttempt'])->name('dashboard.quiz.attempt');
    });
    Route::get('/dashboard/progress/check/{level}', [ProgressController::class, 'checkProgression'])->name('dashboard.progress.check');
    Route::post('/dashboard/progress/manual/{userId}/{toLevel}', [ProgressController::class, 'manualProgression'])->name('dashboard.progress.manual');
    Route::post('/dashboard/my-progress/refresh', [ProgressController::class, 'refreshRecentLessons'])->name('dashboard.my-progress.refresh');

    // Projects
    Route::get('/dashboard/my-projects', [ProjectController::class, 'index'])->name('dashboard.my-projects');
    Route::post('/dashboard/projects/start', [ProjectController::class, 'startProject'])->name('dashboard.projects.start');
    Route::post('/dashboard/projects/{projectId}/progress', [ProjectController::class, 'updateProgress'])->name('dashboard.projects.progress');
    Route::post('/dashboard/projects/{projectId}/pause', [ProjectController::class, 'pauseProject'])->name('dashboard.projects.pause');
    Route::post('/dashboard/projects/{projectId}/resume', [ProjectController::class, 'resumeProject'])->name('dashboard.projects.resume');
    Route::post('/dashboard/projects/{projectId}/complete', [ProjectController::class, 'completeProject'])->name('dashboard.projects.complete');
    Route::post('/dashboard/projects/{projectId}/favorite', [ProjectController::class, 'toggleFavorite'])->name('dashboard.projects.favorite');
    Route::delete('/dashboard/projects/{projectId}', [ProjectController::class, 'deleteProject'])->name('dashboard.projects.delete');
    Route::get('/dashboard/projects/{projectId}', [ProjectController::class, 'getProject'])->name('dashboard.projects.show');
    Route::get('/dashboard/projects/analytics', [ProjectController::class, 'getAnalytics'])->name('dashboard.projects.analytics');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::get('/settings/notifications', [App\Http\Controllers\ProfileController::class, 'notifications'])->name('settings.notifications');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // API endpoints
    Route::get('/api/user/avatar-info', [ProfileController::class, 'getAvatarInfo'])->name('api.user.avatar-info');
    Route::put('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::delete('/profile/phone', [ProfileController::class, 'removePhone'])->name('profile.phone.remove');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    Route::post('/profile/phone/resend-verification', [ProfileController::class, 'resendPhoneVerification'])->name('profile.phone.resend-verification');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Subscription APIs
    Route::get('/api/pricing-plans', [ProfileController::class, 'getPricingPlans'])->name('api.pricing-plans');
    Route::get('/api/current-subscription', [ProfileController::class, 'getCurrentSubscription'])->name('api.current-subscription');
    Route::post('/api/subscribe', [ProfileController::class, 'subscribeToPlan'])->name('api.subscribe');
    Route::post('/api/cancel-subscription', [ProfileController::class, 'cancelSubscription'])->name('api.cancel-subscription');


    // Notes
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::get('/dashboard/notes', [NotesController::class, 'index'])->name('dashboard.notes');
        Route::get('/dashboard/notes/{id}', [NotesController::class, 'view'])->name('dashboard.notes.view');
        Route::post('/dashboard/notes', [NotesController::class, 'store'])->name('dashboard.notes.store');
        Route::put('/dashboard/notes/{id}', [NotesController::class, 'update'])->name('dashboard.notes.update');
        Route::delete('/dashboard/notes/{id}', [NotesController::class, 'destroy'])->name('dashboard.notes.destroy');
    });

    // Comments
    Route::get('/dashboard/lesson/{lessonId}/comments', [DashboardController::class, 'getComments'])->name('dashboard.lesson.comments');
    Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');
    Route::post('/dashboard/comment/{commentId}/like', [DashboardController::class, 'likeComment'])->name('dashboard.comment.like');

    // Quiz
    Route::prefix('quiz')->name('quiz.')->middleware(['decode.obfuscated'])->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/{quizId}/instructions', [QuizController::class, 'instructions'])->name('instructions');
        Route::get('/{quizId}/take', [QuizController::class, 'take'])->name('take');
        Route::get('/{quizId}/essay', [QuizController::class, 'takeEssay'])->name('essay');
        Route::post('/{quizId}/submit', [QuizController::class, 'submit'])->name('submit');
        Route::post('/{quizId}/essay/submit', [QuizController::class, 'submitEssay'])->name('essay.submit');
        Route::post('/{quizId}/violation', [QuizController::class, 'violation'])->name('violation');
        Route::post('/{quizId}/rate', [QuizController::class, 'rate'])->name('rate');
        Route::get('/results', [QuizController::class, 'results'])->name('results');
    });

    // Quiz API endpoints (outside the quiz prefix to match JavaScript calls)
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::get('/api/quiz/{quizId}/reviews', [QuizController::class, 'getReviews'])->name('api.quiz.reviews');
    });

    // Virtual classroom
    Route::get('/dashboard/join-class', [DashboardController::class, 'joinClass'])->name('dashboard.join-class');
    Route::get('/dashboard/classroom/{roomId}', [DashboardController::class, 'showClassroom'])->name('dashboard.classroom.show');

    // Notifications
    Route::get('/dashboard/notifications', [NotificationController::class, 'dashboardIndex'])->name('dashboard.notifications');
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::get('/dashboard/notifications/{notificationId}', [NotificationController::class, 'show'])->name('dashboard.notification.show');
    });

    // Activity ping endpoint - update user's last activity time
    // This endpoint is called frequently during long-running uploads to keep session alive
    Route::post('/ping', function ($request) {
        try {
            // Check if user is authenticated
            if (!$request->user()) {
                return response()->json(['status' => 'unauthenticated'], 401);
            }
            
            $user = $request->user();
            
            // Only update if enough time has passed (throttle updates to prevent database stress)
            $lastUpdate = $user->last_activity_at;
            $now = now();
            
            // Only update if more than 60 seconds have passed (increased throttle)
            if (!$lastUpdate || $lastUpdate->diffInSeconds($now) > 60) {
                try {
                    // Use raw query update to avoid model overhead during uploads
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $user->id)
                        ->update(['last_activity_at' => $now]);
                } catch (\Exception $updateError) {
                    // Log but don't fail the ping - session timeout is handled by Laravel
                    \Illuminate\Support\Facades\Log::warning('Ping update failed (non-blocking)', [
                        'user_id' => $user->id,
                        'error' => $updateError->getMessage()
                    ]);
                }
            }
            
            // Return success quickly - don't wait for database response
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Ping endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getFile() . ':' . $e->getLine()
            ]);
            // Return 200 even on error so uploads don't fail
            // Session timeout is managed separately
            return response()->json(['status' => 'ok'], 200);
        }
    })->middleware('auth')->name('ping');

    // Online users API
    Route::get('/online-users', function () {
        try {
            $keys = Redis::keys('user:*:last_seen');
            $onlineUsers = [];

            foreach ($keys as $key) {
                $userId = str_replace(['user:', ':last_seen'], '', $key);
                $user = User::find($userId);
                if ($user) {
                    $onlineUsers[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'last_seen' => Redis::get($key),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'online_users' => $onlineUsers,
                'count' => count($onlineUsers)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch online users',
                'count' => 0
            ]);
        }
    })->name('online-users');

    // Recommendation feeds
    Route::get('/api/dashboard/feeds', [App\Http\Controllers\RecommendationController::class, 'getDashboardFeeds'])->name('api.dashboard.feeds');
    Route::get('/api/analytics', [App\Http\Controllers\RecommendationController::class, 'getAnalytics'])->name('api.analytics');

    // Lesson search API
    Route::get('/api/dashboard/search-lessons', [DashboardController::class, 'searchLessons'])->name('api.dashboard.search-lessons');

    // Plan change API
    Route::post('/api/dashboard/change-plan', [DashboardController::class, 'changePlan'])->name('api.dashboard.change-plan');

    // Notifications API
    Route::middleware(['decode.obfuscated'])->prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::put('/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notificationId}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/preferences', [NotificationController::class, 'getPreferences'])->name('preferences');
        Route::put('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::post('/grade-opt-out', [NotificationController::class, 'toggleGradeOptOut'])->name('grade-opt-out');
    });

    
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Authenticated + Admin Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStatsAjax'])->name('dashboard.stats');
    Route::get('/contents', [AdminController::class, 'contents'])->name('contents.index');
    Route::post('/contents', [AdminController::class, 'storeContentPackage'])->name('contents.store');
    Route::post('/fix-vimeo-privacy', [AdminController::class, 'fixVimeoPrivacy'])->name('fix-vimeo-privacy');
    Route::delete('/contents/youtube/{contentId}', [AdminController::class, 'destroyYouTubeContent'])->name('contents.youtube.destroy');
    Route::get('/contents/{contentId}/edit', [AdminController::class, 'editContent'])->name('contents.edit');
    Route::put('/contents/{contentId}', [AdminController::class, 'updateContent'])->name('contents.update');
    Route::delete('/contents/{contentId}', [AdminController::class, 'destroyContent'])->name('contents.destroy');
    Route::delete('/contents/vimeo/delete', [AdminController::class, 'destroyVimeoVideo'])->name('contents.vimeo.delete');
    Route::post('/contents/upload/video', [AdminController::class, 'uploadVideoComponent'])->name('contents.upload.video');
    Route::post('/contents/upload/video-chunk', [AdminController::class, 'uploadVideoChunk'])->name('contents.upload.video-chunk');
    Route::post('/contents/upload/documents', [AdminController::class, 'uploadDocumentsComponent'])->name('contents.upload.documents');
    Route::post('/contents/upload/quiz', [AdminController::class, 'uploadQuizComponent'])->name('contents.upload.quiz');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/{id}/update-avatar', [AdminController::class, 'updateUserAvatar'])->name('users.update-avatar');
    Route::delete('/users/{id}/delete-avatar', [AdminController::class, 'deleteUserAvatar'])->name('users.delete-avatar');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/content', [AdminController::class, 'content'])->name('content');
    Route::get('/revenue', [AdminController::class, 'revenue'])->name('revenue');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/security', [AdminController::class, 'security'])->name('security');
    Route::get('/security/data', [AdminController::class, 'getSecurityDataAjax'])->name('security.data');
    Route::get('/activities', [AdminController::class, 'activities'])->name('activities');
    Route::get('/user-activities', [AdminController::class, 'getUserActivitiesApi'])->name('user-activities');
    Route::get('/activity-stats', [AdminController::class, 'getActivityStatsApi'])->name('activity-stats');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/export', [AdminController::class, 'exportUsers'])->name('export');
    Route::get('/credentials', [AdminController::class, 'showCredentials'])->name('credentials');
    Route::post('/credentials/update', [AdminController::class, 'updateCredentials'])->name('credentials.update');
    Route::post('/credentials/recovery', [AdminController::class, 'generateRecoveryCodes'])->name('credentials.recovery');
    Route::post('/toggle-lock', [AdminController::class, 'toggleLock'])->name('toggle-lock');
    // Class management
    Route::get('/classes/create', [AdminController::class, 'showCreateClassForm'])->name('classes.create');
    Route::post('/classes', [AdminController::class, 'createClass'])->name('classes.store');

    // Content Management - Videos
    Route::prefix('content/videos')->name('content.videos.')->group(function () {
        Route::get('/', [AdminController::class, 'indexVideos'])->name('index');
        Route::post('/', [AdminController::class, 'storeVideo'])->name('store');
        Route::get('/{video}/edit', [AdminController::class, 'editVideo'])->name('edit');
        Route::put('/{video}', [AdminController::class, 'updateVideo'])->name('update');
        Route::delete('/{video}', [AdminController::class, 'destroyVideo'])->name('destroy');
        Route::post('/{video}/toggle-feature', [AdminController::class, 'toggleVideoFeature'])->name('toggle-feature');
        
        // Video review workflow
        Route::post('/{id}/approve', [AdminController::class, 'approveVideo'])->name('approve')->where('id', '[0-9]+');
        Route::post('/{id}/reject', [AdminController::class, 'rejectVideo'])->name('reject')->where('id', '[0-9]+');
        Route::get('/{id}/preview', [AdminController::class, 'previewVideo'])->name('preview')->where('id', '[0-9]+');
        Route::get('/{id}/stream', [App\Http\Controllers\VideoStreamController::class, 'stream'])->name('stream')->where('id', '[0-9]+');

        // Video verification route
        Route::post('/{video}/verify', [AdminController::class, 'verifyVideoUpload'])
            ->name('admin.content.videos.verify');
    });


    // Content Management - Documents
    Route::prefix('content/documents')->name('content.documents.')->group(function () {
        Route::get('/', [AdminController::class, 'indexDocuments'])->name('index');
        Route::post('/', [AdminController::class, 'storeDocument'])->name('store');
        Route::get('/{document}/edit', [AdminController::class, 'editDocument'])->name('edit');
        Route::put('/{document}', [AdminController::class, 'updateDocument'])->name('update');
        Route::delete('/{document}', [AdminController::class, 'destroyDocument'])->name('destroy');
        Route::post('/{document}/toggle-feature', [AdminController::class, 'toggleDocumentFeature'])->name('toggle-feature');
    });

    // Content Management - Subjects
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);

    // Progress Management
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('/', [AdminController::class, 'progressOverview'])->name('overview');
        Route::get('/standards', [AdminController::class, 'progressionStandards'])->name('standards');
        Route::post('/standards', [AdminController::class, 'storeProgressionStandard'])->name('standards.store');
        Route::put('/standards/{standard}', [AdminController::class, 'updateProgressionStandard'])->name('standards.update');
        Route::post('/standards/{standard}/toggle', [AdminController::class, 'toggleStandardStatus'])->name('standards.toggle');
        Route::get('/user/{userId}', [AdminController::class, 'userProgressDetail'])->name('user.detail');
        Route::post('/user/{userId}/progress', [AdminController::class, 'manualProgressUser'])->name('user.progress');
    });

    // Pricing Management
    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [App\Http\Controllers\PricingPlanController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\PricingPlanController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\PricingPlanController::class, 'store'])->name('store');
        Route::get('/{pricingPlan}', [App\Http\Controllers\PricingPlanController::class, 'show'])->name('show');
        Route::get('/{pricingPlan}/edit', [App\Http\Controllers\PricingPlanController::class, 'edit'])->name('edit');
        Route::put('/{pricingPlan}', [App\Http\Controllers\PricingPlanController::class, 'update'])->name('update');
        Route::delete('/{pricingPlan}', [App\Http\Controllers\PricingPlanController::class, 'destroy'])->name('destroy');
        Route::post('/{pricingPlan}/toggle-active', [App\Http\Controllers\PricingPlanController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{pricingPlan}/toggle-featured', [App\Http\Controllers\PricingPlanController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::post('/update-sort-order', [App\Http\Controllers\PricingPlanController::class, 'updateSortOrder'])->name('update-sort-order');
    });

    Route::get('notifications/', [NotificationController::class, 'adminIndex'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'adminShow'])->name('notifications.show');
        Route::post('notifications/send', [NotificationController::class, 'sendNotification'])->name('notifications.send');
        Route::post('notifications/system-announcement', [NotificationController::class, 'sendSystemAnnouncement'])->name('notifications.system-announcement');
        Route::post('notifications/targeted', [NotificationController::class, 'sendTargetedNotification'])->name('notifications.targeted');
        Route::get('notifications/types', [NotificationController::class, 'getNotificationTypes'])->name('notifications.types');
        Route::post('notifications/types', [NotificationController::class, 'createNotificationType'])->name('notifications.types.create');
        Route::put('notifications/types/{type}', [NotificationController::class, 'updateNotificationType'])->name('notifications.types.update');
        Route::delete('notifications/types/{type}', [NotificationController::class, 'deleteNotificationType'])->name('notifications.types.delete');
        Route::post('notifications/types/{type}/toggle', [NotificationController::class, 'toggleNotificationType'])->name('notifications.types.toggle');

    // Cookie Analytics
    Route::get('/cookie-stats', [CookieController::class, 'adminStatsPage'])->name('cookie-stats');

    // Payment Analytics Export
    Route::get('/payments/export', [AdminController::class, 'exportPayments'])->name('payments.export');

    // Storage Monitoring
    Route::prefix('storage')->name('storage.')->group(function () {
        Route::get('/', [AdminController::class, 'storageDashboard'])->name('dashboard');
        Route::get('/analytics', [AdminController::class, 'storageAnalytics'])->name('analytics');
        Route::get('/alerts', [AdminController::class, 'storageAlerts'])->name('alerts');
        Route::get('/settings', [AdminController::class, 'storageSettings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateStorageSettings'])->name('settings.update');
        Route::get('/api/analytics/{days}', [AdminController::class, 'getStorageAnalytics'])->name('api.analytics');
        Route::get('/api/alerts', [AdminController::class, 'getStorageAlerts'])->name('api.alerts');
    });
});

/*
|--------------------------------------------------------------------------
| Superuser Routes (Authenticated + Superuser Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superuser'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/superuser-dashboard', [AdminController::class, 'dashboard'])->name('superuser-dashboard');
    Route::post('/toggle-lock', [AdminController::class, 'toggleLock'])->name('toggle-lock');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{id}/unsuspend', [AdminController::class, 'unsuspendUser'])->name('users.unsuspend');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkUserAction'])->name('users.bulk-action');
    Route::get('/content', [AdminController::class, 'content'])->name('content');
    Route::get('/revenue', [AdminController::class, 'revenue'])->name('revenue');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/security', [AdminController::class, 'security'])->name('security');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/export-users', [AdminController::class, 'exportUsers'])->name('export-users');
    Route::get('/superuser-credentials', [AdminController::class, 'showCredentials'])->name('superuser-credentials');
    Route::post('/superuser-credentials/update', [AdminController::class, 'updateCredentials'])->name('superuser-credentials.update');
    Route::post('/generate-recovery-codes', [AdminController::class, 'generateRecoveryCodes'])->name('generate-recovery-codes');
    // Virtual Class Management
    Route::get('/classes/create', [AdminController::class, 'showCreateClassForm'])->name('classes.create');
    Route::post('/classes', [AdminController::class, 'createClass'])->name('classes.store');
});

/*
|--------------------------------------------------------------------------
| CSP Report Endpoint
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Cookie Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('cookies')->name('cookies.')->group(function () {
    Route::get('/status', [CookieController::class, 'status'])->name('status');
    Route::post('/consent', [CookieController::class, 'setConsent'])->name('consent');
    Route::post('/accept-all', [CookieController::class, 'acceptAll'])->name('accept-all');
    Route::post('/reject-all', [CookieController::class, 'rejectAll'])->name('reject-all');
    Route::post('/delete', [CookieController::class, 'deleteAll'])->name('delete');
    Route::get('/policy', [CookieController::class, 'policy'])->name('policy');
    Route::get('/settings', [CookieController::class, 'settings'])->name('settings');

    // Admin routes for cookie statistics
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/stats', [CookieController::class, 'stats'])->name('stats');
    });
});

Route::post('/csp-report', function (Request $request) {
    Log::channel('security')->warning('CSP violation', [
        'data' => $request->getContent()
    ]);
    return response()->noContent();
})->withoutMiddleware([VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Mux Webhook Endpoint
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/mux', [App\Http\Controllers\MuxWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.mux');

/*
|--------------------------------------------------------------------------
| Quiz Image Routes
|--------------------------------------------------------------------------
*/
Route::get('/quiz/{quizId}/image/{imageIndex}', function($quizId, $imageIndex) {
    $path = "quiz_images/{$quizId}/{$imageIndex}";

    // Check for different extensions
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($extensions as $ext) {
        $fullPath = "{$path}.{$ext}";
        if (Storage::disk('public')->exists($fullPath)) {
            return response()->file(storage_path("app/public/{$fullPath}"));
        }
    }

    return response()->json(['error' => 'Image not found'], 404);
})->name('quiz.image');