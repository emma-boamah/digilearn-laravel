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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/pricing/pricing-details', [PricingController::class, 'show'])->name('pricing-details');

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
    // Level selection and dashboard
    Route::get('/dashboard/level-selection', [DashboardController::class, 'levelSelection'])->name('dashboard.level-selection');
    Route::get('/dashboard/level-group/{groupId}', [DashboardController::class, 'showLevelGroup'])->name('dashboard.level-group');
    Route::post('/dashboard/select-level-group/{groupId}', [DashboardController::class, 'selectLevelGroup'])->name('dashboard.select-level-group');
    Route::get('/dashboard/main', [DashboardController::class, 'main'])->name('dashboard.main');
    Route::get('/dashboard/digilearn', [DashboardController::class, 'digilearn'])->name('dashboard.digilearn');
    Route::get('/dashboard/personalized', [DashboardController::class, 'personalized'])->name('dashboard.personalized');
    Route::get('/dashboard/shop', [DashboardController::class, 'shop'])->name('dashboard.shop');
    Route::get('/dashboard/change-level', [DashboardController::class, 'changeLevelSelection'])->name('dashboard.change-level');

    // Lessons
    Route::get('/dashboard/lesson/{lessonId}', [DashboardController::class, 'viewLesson'])->name('dashboard.lesson.view');
    Route::post('/dashboard/lesson/{lessonId}/notes', [DashboardController::class, 'saveNotes'])->name('dashboard.lesson.notes');
    Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');
    Route::get('/dashboard/lesson/{lessonId}/check-saved', [DashboardController::class, 'checkLessonSaved'])->name('dashboard.lesson.check-saved');
    Route::post('/dashboard/lesson/{lessonId}/save', [DashboardController::class, 'saveLesson'])->name('dashboard.lesson.save');
    Route::delete('/dashboard/lesson/{lessonId}/unsave', [DashboardController::class, 'unsaveLesson'])->name('dashboard.lesson.unsave');

    // Saved lessons
    Route::get('/dashboard/saved-lessons', [DashboardController::class, 'savedLessons'])->name('dashboard.saved-lessons');

    // Progress
    Route::get('/dashboard/my-progress', [ProgressController::class, 'index'])->name('dashboard.my-progress');
    Route::post('/dashboard/lesson/{lessonId}/progress', [ProgressController::class, 'recordLessonProgress'])->name('dashboard.lesson.progress');
    Route::post('/dashboard/quiz/{quizId}/attempt', [ProgressController::class, 'recordQuizAttempt'])->name('dashboard.quiz.attempt');
    Route::get('/dashboard/progress/check/{level}', [ProgressController::class, 'checkProgression'])->name('dashboard.progress.check');
    Route::post('/dashboard/progress/manual/{userId}/{toLevel}', [ProgressController::class, 'manualProgression'])->name('dashboard.progress.manual');

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
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
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

    // Notes
    Route::get('/dashboard/notes', [NotesController::class, 'index'])->name('dashboard.notes');
    Route::get('/dashboard/notes/{id}', [NotesController::class, 'view'])->name('dashboard.notes.view');
    Route::post('/dashboard/notes', [NotesController::class, 'store'])->name('dashboard.notes.store');
    Route::put('/dashboard/notes/{id}', [NotesController::class, 'update'])->name('dashboard.notes.update');
    Route::delete('/dashboard/notes/{id}', [NotesController::class, 'destroy'])->name('dashboard.notes.destroy');

    // Quiz
    Route::prefix('quiz')->name('quiz.')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/{quizId}/instructions', [QuizController::class, 'instructions'])->name('instructions');
        Route::get('/{quizId}/take', [QuizController::class, 'take'])->name('take');
        Route::post('/{quizId}/submit', [QuizController::class, 'submit'])->name('submit');
        Route::get('/results', [QuizController::class, 'results'])->name('results');
    });

    // Virtual classroom
    Route::get('/dashboard/join-class', [DashboardController::class, 'joinClass'])->name('dashboard.join-class');
    Route::get('/dashboard/classroom/{roomId}', [DashboardController::class, 'showClassroom'])->name('dashboard.classroom.show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Authenticated + Admin Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/content', [AdminController::class, 'content'])->name('content');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/security', [AdminController::class, 'security'])->name('security');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/export', [AdminController::class, 'export'])->name('export');
    Route::get('/credentials', [AdminController::class, 'showCredentials'])->name('credentials');
    Route::post('/credentials/update', [AdminController::class, 'updateCredentials'])->name('credentials.update');
    Route::post('/credentials/recovery', [AdminController::class, 'generateRecoveryCodes'])->name('credentials.recovery');
    Route::post('/admin/toggle-lock', [AdminController::class, 'toggleLock'])->name('toggle-lock');
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
    });

    // Content Management - Quizzes (Placeholder)
    Route::prefix('content/quizzes')->name('content.quizzes.')->group(function () {
        Route::get('/', function () { return view('admin.content.quizzes.index'); })->name('index');
        // Add store, edit, update, destroy routes here
    });

    // Content Management - Documents (Placeholder)
    Route::prefix('content/documents')->name('content.documents.')->group(function () {
        Route::get('/', function () { return view('admin.content.documents.index'); })->name('index');
        // Add store, edit, update, destroy routes here
    });
});

/*
|--------------------------------------------------------------------------
| Superuser Routes (Authenticated + Superuser Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superuser'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/toggle-lock', [AdminController::class, 'toggleLock'])->name('toggle-lock');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{id}/unsuspend', [AdminController::class, 'unsuspendUser'])->name('users.unsuspend');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkUserAction'])->name('users.bulk-action');
    Route::get('/content', [AdminController::class, 'content'])->name('content');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/security', [AdminController::class, 'security'])->name('security');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/export-users', [AdminController::class, 'exportUsers'])->name('export-users');
    Route::get('/credentials', [AdminController::class, 'showCredentials'])->name('credentials');
    Route::post('/credentials', [AdminController::class, 'updateCredentials'])->name('credentials.update');
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
Route::post('/csp-report', function () {
    Log::channel('security')->warning('CSP Violation', request()->all());
    return response('', 204);
});