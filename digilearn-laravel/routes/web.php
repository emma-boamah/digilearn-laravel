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
use Illuminate\Support\Facades\Redis; // Added for test-redis route

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Add to web.php
Route::get('/test-redis', function() {
    try {
        Redis::connection()->ping();
        return "Redis connected successfully!";
    } catch (\Exception $e) {
        return "Redis error: " . $e->getMessage();
    }
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/pricing/pricing-details', [PricingController::class, 'show'])->name('pricing-details');

// Authentication routes with rate limiting
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit')
    ->middleware('throttle:login');  // Only throttle login submission

Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])
    ->name('signup.submit')
    ->middleware('throttle:signup');  // Only throttle signup submission

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Form submissions with rate limiting
Route::middleware(['throttle:forms'])->group(function () {
    Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
    Route::post('/feedback/submit', [ContactController::class, 'submitFeedback'])->name('feedback.submit');
    Route::post('/newsletter/subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');
});

// Dashboard routes (protected by auth middleware only - no 'verified' middleware)
Route::middleware(['auth'])->group(function () {
    // Level selection (first page after login)
    Route::get('/dashboard/level-selection', [DashboardController::class, 'levelSelection'])->name('dashboard.level-selection');
    
    // Show specific level group (Primary 1-3, JHS 1-3, etc.)
    Route::get('/dashboard/level-group/{groupId}', [DashboardController::class, 'showLevelGroup'])->name('dashboard.level-group');

    // Select level group and go to digilearn
    Route::post('/dashboard/select-level-group/{groupId}', [DashboardController::class, 'selectLevelGroup'])->name('dashboard.select-level-group');

    // Main dashboard (after level selection)
    Route::get('/dashboard/main', [DashboardController::class, 'main'])->name('dashboard.main');

    // DigiLearn video grid (shows videos for selected level group)
    Route::get('/dashboard/digilearn', [DashboardController::class, 'digilearn'])->name('dashboard.digilearn');
    
    // Other sections
    Route::get('/dashboard/personalized', [DashboardController::class, 'personalized'])->name('dashboard.personalized');
    Route::get('/dashboard/shop', [DashboardController::class, 'shop'])->name('dashboard.shop');
    
    // Allow users to change level
    Route::get('/dashboard/change-level', [DashboardController::class, 'changeLevelSelection'])->name('dashboard.change-level');

    // Lesson viewing routes
    Route::get('/dashboard/lesson/{lessonId}', [DashboardController::class, 'viewLesson'])->name('dashboard.lesson.view');
    Route::post('/dashboard/lesson/{lessonId}/notes', [DashboardController::class, 'saveNotes'])->name('dashboard.lesson.notes');
    Route::post('/dashboard/lesson/{lessonId}/comment', [DashboardController::class, 'postComment'])->name('dashboard.lesson.comment');

    // Saved lessons routes
    Route::get('/dashboard/saved-lessons', [DashboardController::class, 'savedLessons'])->name('dashboard.saved-lessons');
    Route::post('/dashboard/lesson/{lessonId}/save', [DashboardController::class, 'saveLesson'])->name('dashboard.lesson.save');
    Route::delete('/dashboard/lesson/{lessonId}/unsave', [DashboardController::class, 'unsaveLesson'])->name('dashboard.lesson.unsave');
    Route::get('/dashboard/lesson/{lessonId}/check-saved', [DashboardController::class, 'checkLessonSaved'])->name('dashboard.lesson.check-saved');

    // Progress tracking routes
    Route::get('/dashboard/my-progress', [ProgressController::class, 'index'])->name('dashboard.my-progress');
    Route::post('/dashboard/lesson/{lessonId}/progress', [ProgressController::class, 'recordLessonProgress'])->name('dashboard.lesson.progress');
    Route::post('/dashboard/quiz/{quizId}/attempt', [ProgressController::class, 'recordQuizAttempt'])->name('dashboard.quiz.attempt');
    Route::get('/dashboard/progress/check/{level}', [ProgressController::class, 'checkProgression'])->name('dashboard.progress.check');
    Route::post('/dashboard/progress/manual/{userId}/{toLevel}', [ProgressController::class, 'manualProgression'])->name('dashboard.progress.manual');

    // Project management routes
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

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // This is the correct route name

    // Phone number management routes
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::put('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::delete('/profile/phone', [ProfileController::class, 'removePhone'])->name('profile.phone.remove');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    Route::post('/profile/phone/resend-verification', [ProfileController::class, 'resendPhoneVerification'])->name('profile.phone.resend-verification');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update'); // Added password update route
    
    // API routes for subscriptions
    Route::get('/api/pricing-plans', [ProfileController::class, 'getPricingPlans'])->name('api.pricing-plans');
    Route::get('/api/current-subscription', [ProfileController::class, 'getCurrentSubscription'])->name('api.current-subscription');
    Route::post('/api/subscribe', [ProfileController::class, 'subscribeToPlan'])->name('api.subscribe');
    Route::post('/api/cancel-subscription', [ProfileController::class, 'cancelSubscription'])->name('api.cancel-subscription');

    // Document viewing routes
    Route::get('/dashboard/lesson/{lessonId}/document/{type}', [DocumentController::class, 'viewDocument'])
        ->name('dashboard.lesson.document')
        ->where('type', 'pdf|ppt');
    
    Route::get('/dashboard/lesson/{lessonId}/document/{type}/content', [DocumentController::class, 'viewDocumentContent'])
        ->name('dashboard.lesson.document.content')
        ->where('type', 'pdf|ppt');

    Route::post('/dashboard/lesson/{lessonId}/document/{type}/save', [DocumentController::class, 'saveDocumentChanges'])
        ->name('dashboard.lesson.document.save')
        ->where('type', 'pdf|ppt');

    Route::get('/dashboard/lesson/{lessonId}/ppt/create', [DocumentController::class, 'createPpt'])
        ->name('dashboard.lesson.ppt.create');

    Route::post('/dashboard/lesson/{lessonId}/ppt/store', [DocumentController::class, 'storePpt'])
        ->name('dashboard.lesson.ppt.store');

    Route::post('/dashboard/lesson/{lessonId}/ppt/{pptId}/update', [DocumentController::class, 'updatePpt'])
        ->name('dashboard.lesson.ppt.update');

    // Notes routes
    Route::get('/dashboard/notes', [NotesController::class, 'index'])->name('dashboard.notes');
    Route::get('/dashboard/notes/{id}', [NotesController::class, 'view'])->name('dashboard.notes.view');
    Route::post('/dashboard/notes', [NotesController::class, 'store'])->name('dashboard.notes.store');
    Route::put('/dashboard/notes/{id}', [NotesController::class, 'update'])->name('dashboard.notes.update');
    Route::delete('/dashboard/notes/{id}', [NotesController::class, 'destroy'])->name('dashboard.notes.destroy');

    // Quiz routes
    Route::prefix('quiz')->name('quiz.')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/{quizId}/instructions', [QuizController::class, 'instructions'])->name('instructions');
        Route::get('/{quizId}/take', [QuizController::class, 'take'])->name('take');
        Route::post('/{quizId}/submit', [QuizController::class, 'submit'])->name('submit');
        Route::get('/results', [QuizController::class, 'results'])->name('results');
    });
});

// Admin routes (authenticated + admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Main admin dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::post('/users/bulk-action', [AdminController::class, 'bulkAction'])->name('users.bulk-action');
    
    // Content management
    Route::get('/content', [AdminController::class, 'content'])->name('content');
    
    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    
    // Security monitoring
    Route::get('/security', [AdminController::class, 'security'])->name('security');
    
    // System settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    
    // Export functionality
    Route::get('/export', [AdminController::class, 'export'])->name('export');
});

// CSP Report endpoint
Route::post('/csp-report', function () {
    // Log CSP violations
    Log::channel('security')->warning('CSP Violation', request()->all());
    return response('', 204);
});
