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
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Phone number management routes
    Route::post('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::put('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::delete('/profile/phone', [ProfileController::class, 'removePhone'])->name('profile.phone.remove');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    
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

// CSP Report endpoint
Route::post('/csp-report', function () {
    // Log CSP violations
    Log::channel('security')->warning('CSP Violation', request()->all());
    return response('', 204);
});