<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\TutorEarningsController;
use App\Http\Controllers\TutorScheduleController;
use App\Http\Controllers\TutorBookingController;
use App\Http\Controllers\TutorContentController;
use App\Http\Controllers\TutorAnalyticsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AdminHeroBannerController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PricingPlanController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\QuizReviewController;
use App\Http\Controllers\VideoStreamController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Models\User;

// Include debug routes in development
if (app()->environment(['local', 'development', 'testing'])) {
    require __DIR__ . '/debug.php';
}

Route::get('/extract-layout', function() {
    $path = resource_path('views/dashboard/digilearn.blade.php');
    $content = file_get_contents($path);
    
    // Replace the body content
    $bodyStart = strpos($content, '<body>') + strlen('<body>');
    $scriptStart = strpos($content, '<script nonce="{{ request()->attributes->get(\'csp_nonce\') }}">', $bodyStart);
    
    $newBody = "
    <!-- Sidebar Overlay for Mobile -->
    <div class=\"sidebar-overlay\" id=\"sidebarOverlay\"></div>

    <div class=\"main-container\">
        @yield('sidebar')
        @include('components.dashboard-header')

        <!-- Search/Filter Bar -->
        <div class=\"filter-bar\" id=\"filterBar\">
            @yield('filter-bar')
        </div>

        <div class=\"subjects-filter-container\">
            @yield('subjects-filter')
        </div>

        <!-- Main Content -->
        <main class=\"main-content\">
            @yield('content')
        </main>
    </div>
";

    $content = substr($content, 0, $bodyStart) . $newBody . substr($content, $scriptStart);
    
    // Remove digilearn specific initializers
    $content = str_replace('initializeInfiniteScroll();', '', $content);
    $content = str_replace('initializeContextFilter();', '', $content);
    $content = str_replace('initializeSearch();', '', $content);
    
    // Remove video facade
    $content = preg_replace('/if \(typeof window\.videoFacadeManager !==.*?initializeAllSaveButtons\(\);\n        }\);/s', '});', $content);
    
    // Remove functions
    $content = preg_replace('/async function initializeAllSaveButtons\(\).*?}\n        }/s', '', $content);
    $content = preg_replace('/window\.addEventListener\(\'pageshow\'.*?}\);/s', '', $content);
    $content = preg_replace('/function escapeHTML\(str\).*?showSearchError\(\'Search failed\. Please try again\.\'\);\n            }\n        }/s', '', $content);
    $content = preg_replace('/function updateLessonGrid\(lessons, query\).*?grid\.innerHTML = html;.*?}/s', '', $content);
    $content = preg_replace('/function restoreOriginalLessons\(\).*?}/s', '', $content);
    $content = preg_replace('/function showSearchError\(message\).*?}/s', '', $content);
    $content = preg_replace('/let isSearching = false;/s', '', $content);
    
    // Remove includes
    $content = str_replace("@include('partials._upgrade_modal')", "", $content);
    $content = str_replace("@include('components.search-autocomplete')", "", $content);
    $content = str_replace("<x-skeleton-loader type=\"digilearn\" />", "", $content);
    
    // Change offset
    $content = str_replace("\$mainContentTopOffset = \$isPrimaryLevel ? '205px' : '255px';", "\$mainContentTopOffset = '145px';", $content);
    $content = str_replace("\$isPrimaryLevel = str_contains(strtolower(\$currentLevelGroup), 'primary') || str_contains(strtolower(\$currentLevelGroup), 'grade');", "", $content);
    
    file_put_contents(resource_path('views/layouts/tutors-layout.blade.php'), $content);
    return 'Done';
});

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
Route::get('/for-schools', [HomeController::class, 'forSchools'])->name('for-schools');
Route::middleware('auth')->group(function () {
    Route::get('/for-schools/register', [\App\Http\Controllers\SchoolRegistrationController::class, 'showRegistrationForm'])->name('school.register');
    Route::post('/for-schools/register', [\App\Http\Controllers\SchoolRegistrationController::class, 'register'])->name('school.register.submit');
    Route::post('/for-schools/register/draft', [\App\Http\Controllers\SchoolRegistrationController::class, 'saveDraft'])->name('school.register.draft');
    Route::get('/for-schools/checkout', [\App\Http\Controllers\SchoolRegistrationController::class, 'checkout'])->name('school.checkout');
});

// School Admin Dashboard Routes
Route::middleware(['auth', 'role:school-admin|super-admin'])->prefix('school-admin')->name('school.admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SchoolAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [\App\Http\Controllers\SchoolAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\SchoolAdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/users', [\App\Http\Controllers\SchoolAdminController::class, 'users'])->name('users');
    Route::get('/users/invite', [\App\Http\Controllers\SchoolAdminController::class, 'showInviteForm'])->name('users.invite');
    Route::post('/users/invite', [\App\Http\Controllers\SchoolAdminController::class, 'inviteUser'])->name('users.invite.submit');
    Route::get('/users/import', [\App\Http\Controllers\SchoolAdminController::class, 'showImportForm'])->name('users.import');
    Route::post('/users/import', [\App\Http\Controllers\SchoolAdminController::class, 'importUsers'])->name('users.import.submit');
    Route::get('/users/import/template', [\App\Http\Controllers\SchoolAdminController::class, 'downloadTemplate'])->name('users.import.template');
    Route::delete('/users/{user}', [\App\Http\Controllers\SchoolAdminController::class, 'removeUser'])->name('users.remove');

    // Academic Setup
    Route::get('/academic-setup', [\App\Http\Controllers\SchoolAdminController::class, 'academicSetup'])->name('academic.setup');
    Route::post('/academic-setup/year', [\App\Http\Controllers\SchoolAdminController::class, 'storeAcademicYear'])->name('academic.year.store');
    Route::post('/academic-setup/term', [\App\Http\Controllers\SchoolAdminController::class, 'storeTerm'])->name('academic.term.store');
    Route::post('/academic-setup/class', [\App\Http\Controllers\SchoolAdminController::class, 'storeClass'])->name('academic.class.store');

    // Billing & Subscription
    Route::get('/billing', [\App\Http\Controllers\SchoolAdminController::class, 'billing'])->name('billing');
    Route::get('/billing/renew', [\App\Http\Controllers\SchoolAdminController::class, 'renewalForm'])->name('billing.renew');
});

// School Content Studio (Phase 4)
Route::middleware(['auth', 'role:school-admin|super-admin|teacher'])->prefix('studio')->name('school.studio.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ContentStudioController::class, 'index'])->name('index');
    
    // Videos
    Route::get('/video/create', [\App\Http\Controllers\ContentStudioController::class, 'createVideo'])->name('video.create');
    Route::post('/video', [\App\Http\Controllers\ContentStudioController::class, 'storeVideo'])->name('video.store');
    
    // Quizzes
    Route::get('/quiz/create', [\App\Http\Controllers\ContentStudioController::class, 'createQuiz'])->name('quiz.create');
    Route::post('/quiz', [\App\Http\Controllers\ContentStudioController::class, 'storeQuiz'])->name('quiz.store');
    Route::get('/quiz/{id}/edit', [\App\Http\Controllers\ContentStudioController::class, 'editQuiz'])->name('quiz.edit');
    Route::put('/quiz/{id}', [\App\Http\Controllers\ContentStudioController::class, 'updateQuiz'])->name('quiz.update');
    Route::post('/quiz/{id}/share', [\App\Http\Controllers\ContentStudioController::class, 'requestShare'])->name('quiz.share');
});
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/guidelines/account-suspension', function () {
    return view('guidelines.account-suspension');
})->name('guidelines.account-suspension');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/pricing-plan/{slug}', [PricingController::class, 'getPlanDetails'])->name('pricing.plan-details');
Route::get('/pricing/pricing-details', [PricingController::class, 'show'])->name('pricing-details');

// Coming soon route (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/coming-soon', [ComingSoonController::class, 'index'])->name('coming-soon');
});

// Credit Wallet
Route::middleware(['auth'])->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::post('/topup', [WalletController::class, 'initiate'])->name('topup');
});

// Payment routes (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::post('/payment/b2b-initiate', [PaymentController::class, 'initiateB2bPayment'])->name('payment.b2b.initiate');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
});

// Paystack callback (NO auth - must be public for redirects)
Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');

// Paystack webhook (no CSRF, no auth)
Route::post('/webhooks/paystack', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([ValidateCsrfToken::class, VerifyCsrfToken::class])
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
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify-otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp.submit');
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->middleware('throttle.redirect:' . config('services.google.rate_limit', 5) . ',1')
    ->name('auth.google');
Route::get('auth/google/callback/route', [GoogleController::class, 'handleGoogleCallback'])
    ->middleware('throttle.redirect:' . config('services.google.rate_limit', 5) . ',1')
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
 | Dashboard Routes (Authenticated Users)
 |--------------------------------------------------------------------------
 */
Route::middleware(['auth'])->group(function () {
    // Level selection and dashboard - unified method handles both initial selection and changes
    Route::get('/dashboard/level-selection', [DashboardController::class, 'levelSelection'])->name('dashboard.level-selection');
    Route::get('/dashboard/level-group/{groupId}', [DashboardController::class, 'showLevelGroup'])->name('dashboard.level-group');
    Route::post('/dashboard/select-level-group/{groupId}', [DashboardController::class, 'selectLevelGroup'])->name('dashboard.select-level-group');
    Route::get('/dashboard/main', [DashboardController::class, 'main'])->name('dashboard.main');
    Route::get('/dashboard/change-level', [DashboardController::class, 'levelSelection'])->name('dashboard.change-level');

    // Profile & Settings (Unrestricted - Auth only)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::get('/settings/notifications', [ProfileController::class, 'notifications'])->name('settings.notifications');
    Route::get('/settings/billing', [ProfileController::class, 'billing'])->name('settings.billing');
    Route::get('/settings/billing-history', [ProfileController::class, 'billingHistory'])->name('settings.billing-history');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API endpoints (Unrestricted)
    Route::get('/api/user/avatar-info', [ProfileController::class, 'getAvatarInfo'])->name('api.user.avatar-info');
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::delete('/profile/phone', [ProfileController::class, 'removePhone'])->name('profile.phone.remove');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    Route::post('/profile/phone/resend-verification', [ProfileController::class, 'resendPhoneVerification'])->name('profile.phone.resend-verification');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Subscription APIs (Unrestricted)
    Route::get('/api/pricing-plans', [ProfileController::class, 'getPricingPlans'])->name('api.pricing-plans');
    Route::get('/api/current-subscription', [ProfileController::class, 'getCurrentSubscription'])->name('api.current-subscription');
    Route::post('/api/subscribe', [ProfileController::class, 'subscribeToPlan'])->name('api.subscribe');
    Route::post('/api/cancel-subscription', [ProfileController::class, 'cancelSubscription'])->name('api.cancel-subscription');

    // Form Submissions (Unrestricted)
    Route::middleware(['throttle:forms'])->group(
        function () {
            Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
            Route::post('/feedback/submit', [ContactController::class, 'submitFeedback'])->name('feedback.submit');
            Route::post('/newsletter/subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');
        }
    );

    // Activity ping endpoint - update user's last activity time
    // This endpoint is called frequently during long-running uploads to keep session alive
    Route::post(
        '/ping',
        function (Request $request) {
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
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['last_activity_at' => $now]);
                    } catch (\Exception $updateError) {
                        // Log but don't fail the ping - session timeout is handled by Laravel
                        Log::warning('Ping update failed (non-blocking)', [
                            'user_id' => $user->id,
                            'error' => $updateError->getMessage()
                        ]);
                    }
                }

                // Return success quickly - don't wait for database response
                return response()->json(['status' => 'ok'], 200);
            } catch (\Exception $e) {
                Log::error('Ping endpoint error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getFile() . ':' . $e->getLine()
                ]);
                // Return 200 even on error so uploads don't fail
                // Session timeout is managed separately
                return response()->json(['status' => 'ok'], 200);
            }
        }
    )->name('ping');

    Route::get(
        '/online-users',
        function () {
            try {
                $keys = Redis::keys('user:*:last_seen');
                $onlineUsers = [];
                foreach ($keys as $key) {
                    $userId = str_replace(['user:', ':last_seen'], '', $key);
                    $user = User::find($userId);
                    if ($user) {
                        $onlineUsers[] = ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar, 'last_seen' => Redis::get($key)];
                    }
                }
                return response()->json(['success' => true, 'online_users' => $onlineUsers, 'count' => count($onlineUsers)]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Unable to fetch online users', 'count' => 0]);
            }
        }
    )->name('online-users');

    // Notifications (Available to all authenticated users)
    Route::get('/dashboard/notifications', [NotificationController::class, 'dashboardIndex'])->name('dashboard.notifications');
    Route::middleware(['decode.obfuscated'])->group(function () {
        Route::get('/dashboard/notifications/{notificationId}', [NotificationController::class, 'show'])->name('dashboard.notification.show');
    });
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

    // Restricted Premium Routes (Subscribed only)
    Route::middleware(['subscribed'])->group(
        function () {
            Route::get('/dashboard/digilearn', [DashboardController::class, 'digilearn'])->name('dashboard.digilearn');
            Route::get('/dashboard/load-more-lessons', [DashboardController::class, 'loadMoreLessons'])->name('dashboard.load-more-lessons');
            Route::get('/dashboard/personalized', [DashboardController::class, 'personalized'])->name('dashboard.personalized');
            Route::get('/dashboard/shop', [DashboardController::class, 'shop'])->name('dashboard.shop');
            Route::get('/dashboard/library', [DocumentController::class, 'index'])->name('dashboard.library');
            Route::get('/dashboard/documents', [DocumentController::class, 'index'])->name('dashboard.documents');
            Route::get('/dashboard/library/document/{docId}', [DocumentController::class, 'openLibraryDocument'])->name('dashboard.library.document');
            Route::post('/dashboard/document/{docId}/synthesize', [DocumentController::class, 'synthesizeCognitiveStructure'])->name('dashboard.document.synthesize');
            Route::post('/dashboard/document/evaluate-recall', [DocumentController::class, 'evaluateRecallCheck'])->name('dashboard.document.evaluate-recall');

            // Lessons
            Route::middleware(['decode.obfuscated'])->group(
                function () {
                Route::get('/dashboard/lesson/{lessonId}', [DashboardController::class, 'viewLesson'])->name('dashboard.lesson.view');
                Route::post('/dashboard/lesson/{lessonId}/notes', [DashboardController::class, 'saveNotes'])->name('dashboard.lesson.notes');
                Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');
                Route::get('/dashboard/lesson/{lessonId}/check-saved', [DashboardController::class, 'checkLessonSaved'])->name('dashboard.lesson.check-saved');
                Route::post('/dashboard/lesson/{lessonId}/save', [DashboardController::class, 'saveLesson'])->name('dashboard.lesson.save');
                Route::delete('/dashboard/lesson/{lessonId}/unsave', [DashboardController::class, 'unsaveLesson'])->name('dashboard.lesson.unsave');

                // Documents
                Route::get('/dashboard/lesson/{lessonId}/document/{type}', [DocumentController::class, 'viewDocument'])->name('dashboard.lesson.document')->where('type', 'pdf|ppt');
                Route::get('/dashboard/lesson/{lessonId}/document/{type}/content', [DocumentController::class, 'viewDocumentContent'])->name('dashboard.lesson.document.content')->where('type', 'pdf|ppt');
                Route::post('/dashboard/lesson/{lessonId}/document/{type}/save', [DocumentController::class, 'saveDocumentChanges'])->name('dashboard.lesson.document.save')->where('type', 'pdf|ppt');
                Route::get('/dashboard/lesson/{lessonId}/ppt/create', [DocumentController::class, 'createPpt'])->name('dashboard.lesson.ppt.create');
                Route::post('/dashboard/lesson/{lessonId}/ppt/store', [DocumentController::class, 'storePpt'])->name('dashboard.lesson.ppt.store');
                Route::post('/dashboard/lesson/{lessonId}/ppt/{pptId}/update', [DocumentController::class, 'updatePpt'])->name('dashboard.lesson.ppt.update');
            }
            );

            // User Notes for Videos
            Route::post('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'saveUserNotes'])->name('dashboard.lesson.user-notes.save');
            Route::get('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'loadUserNotes'])->name('dashboard.lesson.user-notes.load');
            Route::delete('/dashboard/lesson/{videoId}/user-notes', [DashboardController::class, 'deleteUserNotes'])->name('dashboard.lesson.user-notes.delete');
            Route::get('/dashboard/user-notes', [DashboardController::class, 'getAllUserNotes'])->name('dashboard.user-notes.all');

            // University
            Route::get('/dashboard/university/years', [DashboardController::class, 'universityYears'])->name('dashboard.university.years');
            Route::post('/dashboard/university/year/{yearId}', [DashboardController::class, 'selectUniversityYear'])->name('dashboard.university.select-year');
            Route::get('/dashboard/university/{yearId}/programs', [DashboardController::class, 'universityPrograms'])->name('dashboard.university.programs');
            Route::get('/dashboard/university/{yearId}/program/{programId}/courses', [DashboardController::class, 'programCourses'])->name('dashboard.university.program.courses');
            Route::get('/dashboard/university/{yearId}/program/{programId}/course/{courseId}/lessons', [DashboardController::class, 'courseLessons'])->name('dashboard.university.course.lessons');
            Route::get('/dashboard/university/course/{courseId}/lessons', [DashboardController::class, 'courseLessonsById'])->name('dashboard.university.course.lessons.by-id');

            // Saved & Progress
            Route::get('/dashboard/saved-lessons', [DashboardController::class, 'savedLessons'])->name('dashboard.saved-lessons');
            Route::get('/dashboard/my-progress', [ProgressController::class, 'index'])->name('dashboard.my-progress');
            Route::get('/dashboard/detailed-report/{grade?}', [ProgressController::class, 'detailedReport'])->name('dashboard.detailed-report');
            Route::middleware(['decode.obfuscated'])->group(
                function () {
                    Route::post('/dashboard/lesson/{lessonId}/progress', [ProgressController::class, 'recordLessonProgress'])->name('dashboard.lesson.progress');
                    Route::post('/dashboard/quiz/{quizId}/attempt', [ProgressController::class, 'recordQuizAttempt'])->name('dashboard.quiz.attempt');
                }
            );
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

            // Notes
            Route::middleware(['decode.obfuscated'])->group(
                function () {
                Route::get('/dashboard/notes', [NotesController::class, 'index'])->name('dashboard.notes');
                Route::get('/dashboard/notes/{id}', [NotesController::class, 'view'])->name('dashboard.notes.view');
                Route::post('/dashboard/notes', [NotesController::class, 'store'])->name('dashboard.notes.store');
                Route::put('/dashboard/notes/{id}', [NotesController::class, 'update'])->name('dashboard.notes.update');
                Route::delete('/dashboard/notes/{id}', [NotesController::class, 'destroy'])->name('dashboard.notes.destroy');
            }
            );

            // Comments
            Route::get('/dashboard/lesson/{lessonId}/comments', [DashboardController::class, 'getComments'])->name('dashboard.lesson.comments');
            Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');
            Route::post('/dashboard/comment/{commentId}/like', [DashboardController::class, 'likeComment'])->name('dashboard.comment.like');
            Route::put('/dashboard/comment/{commentId}', [DashboardController::class, 'updateComment'])->name('dashboard.comment.update');

            // Quiz
            Route::prefix('quiz')->name('quiz.')->middleware(['decode.obfuscated'])->group(
                function () {
                Route::get('/', [QuizController::class, 'index'])->name('index');
                Route::get('/{quizId}/instructions', [QuizController::class, 'instructions'])->name('instructions');
                Route::get('/{quizId}/take', [QuizController::class, 'take'])->name('take');
                Route::get('/{quizId}/essay', [QuizController::class, 'takeEssay'])->name('essay');
                Route::post('/{quizId}/submit', [QuizController::class, 'submit'])->name('submit');
                Route::post('/{quizId}/essay/submit', [QuizController::class, 'submitEssay'])->name('essay.submit');
                Route::post('/{quizId}/violation', [QuizController::class, 'violation'])->name('violation');
                Route::post('/{quizId}/appeal', [QuizController::class, 'appeal'])->name('appeal');
                Route::post('/{quizId}/heartbeat', [QuizController::class, 'heartbeat'])->name('heartbeat');
                Route::post('/{quizId}/rate', [QuizController::class, 'rate'])->name('rate');
                Route::get('/results', [QuizController::class, 'results'])->name('results');
            }
            );
            Route::middleware(['decode.obfuscated'])->group(
                function () {
                    Route::get('/api/quiz/{quizId}/reviews', [QuizController::class, 'getReviews'])->name('api.quiz.reviews');
                }
            );

            // Virtual classroom
            Route::get('/dashboard/join-class', [DashboardController::class, 'joinClass'])->name('dashboard.join-class');
            Route::get('/dashboard/classroom/{roomId}', [DashboardController::class, 'showClassroom'])->name('dashboard.classroom.show');

            // Tutors and Bookings (Personalized Learning)
            Route::prefix('tutors')->name('tutors.')->group(function () {
                Route::get('/', [TutorController::class, 'index'])->name('index');
                Route::get('/apply', [TutorController::class, 'apply'])->name('apply');
                Route::post('/apply', [TutorController::class, 'storeApplication'])->name('storeApplication');
                Route::get('/dashboard', [TutorController::class, 'dashboard'])->name('dashboard');

                // Profile Settings
                Route::get('/profile-settings', [TutorController::class, 'profileSettings'])->name('profile.settings');
                Route::put('/profile-settings', [TutorController::class, 'updateProfileSettings'])->name('profile.settings.update');

                // Wallet & Earnings
                Route::get('/earnings', [TutorEarningsController::class, 'index'])->name('earnings.index');
                Route::get('/earnings/transactions', [TutorEarningsController::class, 'transactions'])->name('earnings.transactions');
                Route::post('/earnings/payout', [TutorEarningsController::class, 'requestPayout'])->name('earnings.payout');
                // Schedule & Availability
                Route::get('/schedule/availability', [TutorScheduleController::class, 'availability'])->name('schedule.availability');
                Route::post('/schedule/availability', [TutorScheduleController::class, 'storeAvailability'])->name('schedule.availability.store');
                Route::get('/schedule/calendar', [TutorScheduleController::class, 'calendar'])->name('schedule.calendar');
                Route::post('/schedule/block-date', [TutorScheduleController::class, 'blockDate'])->name('schedule.block');
                Route::delete('/schedule/unblock-date', [TutorScheduleController::class, 'unblockDate'])->name('schedule.unblock');
                Route::get('/schedule/api/slots/{tutorId}', [TutorScheduleController::class, 'apiSlots'])->name('schedule.api.slots');

                // Content Studio
                Route::get('/content', [TutorContentController::class, 'index'])->name('content.index');
                Route::get('/content/course/create', [TutorContentController::class, 'createCourse'])->name('content.course.create');
                Route::post('/content/course', [TutorContentController::class, 'storeCourse'])->name('content.course.store');
                Route::post('/content/video', [TutorContentController::class, 'storeVideo'])->name('content.video.store');
                Route::post('/content/document', [TutorContentController::class, 'storeDocument'])->name('content.document.store');
                Route::post('/content/quiz', [TutorContentController::class, 'storeQuiz'])->name('content.quiz.store');
                Route::delete('/content/course/{id}', [TutorContentController::class, 'deleteCourse'])->name('content.course.delete');

                // Booking Requests
                Route::get('/my-bookings', [TutorBookingController::class, 'incoming'])->name('bookings.index');
                Route::post('/my-bookings/{id}/accept', [TutorBookingController::class, 'accept'])->name('bookings.accept');
                Route::post('/my-bookings/{id}/decline', [TutorBookingController::class, 'decline'])->name('bookings.decline');
                Route::post('/my-bookings/{id}/reschedule', [TutorBookingController::class, 'reschedule'])->name('bookings.reschedule');
                Route::post('/my-bookings/{id}/notes', [TutorBookingController::class, 'sessionNotes'])->name('bookings.notes');
                Route::get('/my-bookings/history', [TutorBookingController::class, 'history'])->name('bookings.history');

                // Analytics
                Route::get('/analytics', [TutorAnalyticsController::class, 'index'])->name('analytics.index');
                Route::get('/analytics/api', [TutorAnalyticsController::class, 'apiStats'])->name('analytics.api');

                Route::get('/{tutorId}', [TutorController::class, 'show'])->name('show');
            });

            Route::prefix('bookings')->name('bookings.')->group(function () {
                Route::get('/checkout', function () {
                    return redirect()->route('tutors.index');
                });
                Route::post('/checkout', [BookingController::class, 'checkout'])->name('checkout');
                Route::get('/{bookingId}/confirm', [BookingController::class, 'confirm'])->name('confirm'); // Could be webhook later
                Route::post('/{bookingId}/complete', [BookingController::class, 'complete'])->name('complete');
            });

            // Recommendations & Search
            Route::get('/api/dashboard/feeds', [RecommendationController::class, 'getDashboardFeeds'])->name('api.dashboard.feeds');
            Route::get('/api/analytics', [RecommendationController::class, 'getAnalytics'])->name('api.analytics');
            Route::post(
                '/api/lessons/track-analytics',
                function (Request $request) {
                    return response()->json(['status' => 'ok']);
                }
            )->name('api.lessons.track-analytics');
            Route::get('/api/dashboard/search-lessons', [DashboardController::class, 'searchLessons'])->name('api.dashboard.search-lessons');
            Route::post('/api/dashboard/change-plan', [DashboardController::class, 'changePlan'])->name('api.dashboard.change-plan');

            // Search Analytics
            Route::post('/api/search/track', [\App\Http\Controllers\SearchAnalyticsController::class, 'track'])->name('api.search.track');
            Route::get('/api/search/suggestions', [\App\Http\Controllers\SearchAnalyticsController::class, 'suggestions'])->name('api.search.suggestions');

            // AI Learning Agent
            Route::get('/dashboard/agent', [AgentController::class, 'index'])->name('dashboard.agent');
            Route::post('/api/agent/ask', [AgentController::class, 'ask'])->name('api.agent.ask');
            Route::get('/api/agent/history', [AgentController::class, 'history'])->name('api.agent.history');
            Route::get('/api/agent/session/{id}', [AgentController::class, 'loadSession'])->name('api.agent.session.load');
        }
    );

    // Teacher Routes (Protected by role)
    Route::middleware(['role:teacher|school-admin|super-admin'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/classes', [App\Http\Controllers\TeacherController::class, 'classes'])->name('classes');
        Route::get('/gradebook/{classSubjectId}/{termId?}', [App\Http\Controllers\TeacherController::class, 'gradebook'])->name('gradebook');
        Route::post('/assessments', [App\Http\Controllers\TeacherController::class, 'storeAssessment'])->name('assessments.store');
        Route::post('/scores', [App\Http\Controllers\TeacherController::class, 'saveScores'])->name('scores.save');

        // CBT Integration
        Route::get('/cbt/available-quizzes', [App\Http\Controllers\TeacherController::class, 'availableQuizzes'])->name('cbt.available-quizzes');
        Route::post('/cbt/assign', [App\Http\Controllers\TeacherController::class, 'assignCbt'])->name('cbt.assign');

        // Reporting
        Route::get('/reports', [App\Http\Controllers\ReportCardController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [App\Http\Controllers\ReportCardController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/class/{classId}/term/{termId}', [App\Http\Controllers\ReportCardController::class, 'viewClassReports'])->name('reports.view');
        Route::get('/reports/{id}/pdf', [App\Http\Controllers\ReportCardController::class, 'downloadPdf'])->name('reports.pdf');
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
    Route::get('/ai-contents', [AdminController::class, 'aiContents'])->name('ai-contents.index');
    Route::get('/ai-contents/{id}', [AdminController::class, 'showAiContent'])->name('ai-contents.show');
    Route::post('/contents', [AdminController::class, 'storeContentPackage'])->name('contents.store');
    Route::post('/contents/bulk-action', [AdminController::class, 'bulkAction'])->name('contents.bulk-action');
    Route::post('/fix-vimeo-privacy', [AdminController::class, 'fixVimeoPrivacy'])->name('fix-vimeo-privacy');
    Route::delete('/contents/youtube/{contentId}', [AdminController::class, 'destroyYouTubeContent'])->name('contents.youtube.destroy');
    Route::get('/contents/{contentId}', [AdminController::class, 'showContent'])->name('contents.show');
    Route::get('/contents/{contentId}/edit', [AdminController::class, 'editContent'])->name('contents.edit');
    Route::put('/contents/{contentId}', [AdminController::class, 'updateContent'])->name('contents.update');
    Route::delete('/contents/{contentId}', [AdminController::class, 'destroyContent'])->name('contents.destroy');
    Route::delete('/contents/vimeo/delete', [AdminController::class, 'destroyVimeoVideo'])->name('contents.vimeo.delete');
    Route::post('/contents/upload/video', [AdminController::class, 'uploadVideoComponent'])->name('contents.upload.video');
    Route::post('/contents/upload/video-chunk', [AdminController::class, 'uploadVideoChunk'])->name('contents.upload.video-chunk');
    Route::post('/contents/upload/documents', [AdminController::class, 'uploadDocumentsComponent'])->name('contents.upload.documents');
    Route::post('/contents/upload/quiz', [AdminController::class, 'uploadQuizComponent'])->name('contents.upload.quiz');
    Route::post('/contents/upload/image', [AdminController::class, 'uploadImage'])->name('contents.upload.image');
    Route::get('/contents/upload-tasks/active', [AdminController::class, 'getActiveUploadTasks'])->name('contents.upload-tasks.active');
    Route::get('/contents/upload-tasks/{taskId}', [AdminController::class, 'getUploadTaskStatus'])->name('contents.upload-tasks.status')->where('taskId', '[0-9a-fA-F\-]+');
    Route::post('/contents/upload-tasks/{taskId}/cancel', [AdminController::class, 'cancelUploadTask'])->name('contents.upload-tasks.cancel')->where('taskId', '[0-9a-fA-F\-]+');
    Route::post('/contents/generate-ai-questions', [AdminController::class, 'generateAiQuestions'])->name('contents.generate-ai-questions');
    Route::post('/quizzes/generate-ai', [AdminController::class, 'generateAiQuestions'])->name('quizzes.generate-ai');
    Route::post('/contents/batch-store', [AdminController::class, 'storeBatchContents'])->name('contents.batch-store');
    Route::get('/contents/batch/{batchId}/status', [AdminController::class, 'getBatchStatus'])->name('contents.batch-status');

    // Super Admin Protected Routes
    Route::middleware(['superuser'])->group(
        function () {
            Route::get('/users', [AdminController::class, 'users'])->name('users');
            Route::get('/users/invite', [AdminController::class, 'inviteAdmin'])->name('users.invite');
            Route::post('/users/invite', [AdminController::class, 'storeAdminInvite'])->name('users.invite.store');
            Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
            Route::get('/users/{id}/activity', [AdminController::class, 'showUserActivity'])->name('users.activity');
            Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
            Route::post('/users/{id}/demote', [AdminController::class, 'demoteAdmin'])->name('users.demote');
            Route::post('/users/{id}/update-avatar', [AdminController::class, 'updateUserAvatar'])->name('users.update-avatar');
            Route::delete('/users/{id}/delete-avatar', [AdminController::class, 'deleteUserAvatar'])->name('users.delete-avatar');
            Route::post('/users/bulk-action', [AdminController::class, 'bulkUserAction'])->name('users.bulk-action');
            Route::post('/mark-invite-notice-seen', [AdminController::class, 'markInviteNoticeSeen'])->name('mark-invite-notice-seen');
            Route::get('/revenue', [AdminController::class, 'revenue'])->name('revenue');
            Route::get('/revenue/export/{type}', [AdminController::class, 'exportRevenueSummary'])->name('revenue.export');
            Route::get('/revenue/export-trends', [AdminController::class, 'exportRevenueTrends'])->name('revenue.export-trends');
            Route::get('/revenue/export-payments', [AdminController::class, 'exportPayments'])->name('revenue.export-payments');
            Route::post('/revenue/aggregate', [AdminController::class, 'aggregateRevenue'])->name('revenue.aggregate');
            Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
            Route::get('/subscriber-analytics', [AdminController::class, 'subscriberAnalytics'])->name('subscriber-analytics');
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

            // Tutor Verification & Management
            Route::get('/tutors', [AdminController::class, 'tutors'])->name('tutors.index');
            Route::get('/tutors/{id}', [AdminController::class, 'showTutor'])->name('tutors.show');
            Route::get('/tutors/{id}/document/{type}', [AdminController::class, 'viewTutorDocument'])->name('tutors.document');
            Route::post('/tutors/{id}/approve', [AdminController::class, 'approveTutor'])->name('tutors.approve');
            Route::post('/tutors/{id}/reject', [AdminController::class, 'rejectTutor'])->name('tutors.reject');

            // Platform Settings (Commission & Payout Config)
            Route::get('/platform-settings', [AdminController::class, 'platformSettings'])->name('platform-settings.index');
            Route::post('/platform-settings', [AdminController::class, 'updatePlatformSettings'])->name('platform-settings.update');

            // Hero Banner Carousel Management
            Route::prefix('hero-banners')->name('hero-banners.')->group(function () {
                Route::get('/', [AdminHeroBannerController::class, 'index'])->name('index');
                Route::get('/create', [AdminHeroBannerController::class, 'create'])->name('create');
                Route::post('/', [AdminHeroBannerController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [AdminHeroBannerController::class, 'edit'])->name('edit');
                Route::put('/{id}', [AdminHeroBannerController::class, 'update'])->name('update');
                Route::delete('/{id}', [AdminHeroBannerController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/toggle', [AdminHeroBannerController::class, 'toggleActive'])->name('toggle');
            });

            // Level Group Management
            Route::get('/level-groups', [AdminController::class, 'levelGroups'])->name('level-groups.index');
            Route::post('/level-groups/{id}/toggle', [AdminController::class, 'toggleLevelGroup'])->name('level-groups.toggle');
        }
    );

    Route::get('/content', [AdminController::class, 'content'])->name('content');


    // Content Management - Videos
    Route::prefix('content/videos')->name('content.videos.')->group(
        function () {
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
            Route::get('/{id}/stream', [VideoStreamController::class, 'stream'])->name('stream')->where('id', '[0-9]+');

            // Video verification route
            Route::post('/{video}/verify', [AdminController::class, 'verifyVideoUpload'])
                ->name('admin.content.videos.verify');
        }
    );


    // Content Management - Documents
    Route::prefix('content/documents')->name('content.documents.')->group(
        function () {
            Route::get('/', [AdminController::class, 'indexDocuments'])->name('index');
            Route::post('/', [AdminController::class, 'storeDocument'])->name('store');
            Route::get('/{document}/edit', [AdminController::class, 'editDocument'])->name('edit');
            Route::put('/{document}', [AdminController::class, 'updateDocument'])->name('update');
            Route::delete('/{document}', [AdminController::class, 'destroyDocument'])->name('destroy');
            Route::post('/{document}/toggle-feature', [AdminController::class, 'toggleDocumentFeature'])->name('toggle-feature');
        }
    );

    // Content Management - Subjects
    Route::resource('subjects', SubjectController::class);

    // Progress Management
    Route::middleware(['role:super-admin'])->prefix('progress')->name('progress.')->group(
        function () {
            Route::get('/', [AdminController::class, 'progressOverview'])->name('overview');
            Route::get('/standards', [AdminController::class, 'progressionStandards'])->name('standards');
            Route::post('/standards', [AdminController::class, 'storeProgressionStandard'])->name('standards.store');
            Route::put('/standards/{standard}', [AdminController::class, 'updateProgressionStandard'])->name('standards.update');
            Route::post('/standards/{standard}/toggle', [AdminController::class, 'toggleStandardStatus'])->name('standards.toggle');
            Route::get('/user/{userId}', [AdminController::class, 'userProgressDetail'])->name('user.detail');
            Route::post('/user/{userId}/progress', [AdminController::class, 'manualProgressUser'])->name('user.progress');
        }
    );

    // Pricing Management
    Route::middleware(['role:super-admin'])->prefix('pricing')->name('pricing.')->group(
        function () {
            Route::get('/', [PricingPlanController::class, 'index'])->name('index');
            Route::get('/create', [PricingPlanController::class, 'create'])->name('create');
            Route::post('/', [PricingPlanController::class, 'store'])->name('store');
            Route::get('/{pricingPlan}', [PricingPlanController::class, 'show'])->name('show');
            Route::get('/{pricingPlan}/edit', [PricingPlanController::class, 'edit'])->name('edit');
            Route::put('/{pricingPlan}', [PricingPlanController::class, 'update'])->name('update');
            Route::delete('/{pricingPlan}', [PricingPlanController::class, 'destroy'])->name('destroy');
            Route::post('/{pricingPlan}/toggle-active', [PricingPlanController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{pricingPlan}/toggle-featured', [PricingPlanController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('/update-sort-order', [PricingPlanController::class, 'updateSortOrder'])->name('update-sort-order');
        }
    );

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
    Route::middleware(['role:super-admin'])->group(
        function () {
            Route::get('/cookie-stats', [CookieController::class, 'adminStatsPage'])->name('cookie-stats');
            Route::get('/cookie-stats/export', [CookieController::class, 'exportCsv'])->name('cookie-stats.export');
        }
    );

    // Payment Analytics & Management
    Route::get('/payments/export', [AdminController::class, 'exportPayments'])->name('payments.export');
    Route::post('/payments/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
    Route::post('/payments/sync-all-pending', [AdminController::class, 'syncPendingPayments'])->name('payments.sync-all-pending');

    // Storage Monitoring
    Route::middleware(['role:super-admin'])->prefix('storage')->name('storage.')->group(
        function () {
            Route::get('/', [AdminController::class, 'storageDashboard'])->name('dashboard');
            Route::get('/analytics', [AdminController::class, 'storageAnalytics'])->name('analytics');
            Route::get('/alerts', [AdminController::class, 'storageAlerts'])->name('alerts');
            Route::get('/settings', [AdminController::class, 'storageSettings'])->name('settings');
            Route::post('/settings', [AdminController::class, 'updateStorageSettings'])->name('settings.update');
            // ... (rest of storage routes could also be here)
        }
    );

    // Task Management
    Route::resource('tasks', TaskController::class);

    // Quiz Review System
    Route::prefix('quizzes')->name('quizzes.')->group(
        function () {
            Route::get('/review', [QuizReviewController::class, 'index'])->name('review.index');
            Route::get('/review/{id}', [QuizReviewController::class, 'show'])->name('review.show');
            Route::post('/review/{id}/grade', [QuizReviewController::class, 'grade'])->name('review.grade');
            Route::post('/review/{id}/auto-grade', [QuizReviewController::class, 'autoGrade'])->name('review.auto-grade');
            Route::post('/review/{id}/invalidate', [QuizReviewController::class, 'invalidate'])->name('review.invalidate');
        }
    );
});

/*
 |--------------------------------------------------------------------------
 | Superuser Routes (Authenticated + Superuser Middleware)
 |--------------------------------------------------------------------------
 */
// Legacy superuser block removed in favor of RBAC role-based protection above

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
    Route::middleware(['auth', 'admin'])->group(
        function () {
            Route::get('/stats', [CookieController::class, 'stats'])->name('stats');
        }
    );
});

Route::post(config('csp.report_uri', '/csp-report'), function (Request $request) {
    Log::channel('security')->warning('CSP violation', [
        'data' => $request->getContent()
    ]);
    return response()->noContent();
})->withoutMiddleware([VerifyCsrfToken::class]);

// Fallback just in case config is cached incorrectly
Route::post('/csp-reports', function (Request $request) {
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
    ->withoutMiddleware([ValidateCsrfToken::class, VerifyCsrfToken::class])
    ->name('webhooks.mux');

/*
 |--------------------------------------------------------------------------
 | Quiz Image Routes
 |--------------------------------------------------------------------------
 */
Route::get('/quiz/{quizId}/image/{imageIndex}', function ($quizId, $imageIndex) {
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