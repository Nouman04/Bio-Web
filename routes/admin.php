<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for the instructor/admin panel — the dashboard, course and
| curriculum management (courses, chapters, topics, categories), the
| question bank and assessments, media/content resources, and the
| student roster. All routes require an authenticated session.
|
| Grouped by area; permission-gated routes name the permission they need,
| which comes from database/seeders/PermissionSeeder.php.
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Curriculum — courses, chapters, topics, categories
    |----------------------------------------------------------------------
    */

    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->middleware('can:view course')->name('courses');
        Route::get('/data', [CourseController::class, 'data'])->middleware('can:view course')->name('courses.data');
        Route::post('/', [CourseController::class, 'store'])->middleware('can:add course')->name('courses.store');
        Route::put('/{course}', [CourseController::class, 'update'])->middleware('can:edit course')->name('courses.update');
        Route::delete('/{course}', [CourseController::class, 'destroy'])->middleware('can:delete course')->name('courses.destroy');

        // Chapters — always scoped to their course
        Route::prefix('{course}/chapters')->group(function () {
            Route::get('/', [ChapterController::class, 'index'])->middleware('can:view course')->name('courses.chapters');
            Route::get('/data', [ChapterController::class, 'data'])->middleware('can:view course')->name('courses.chapters.data');
            Route::get('/{chapter}/dashboard', [ChapterController::class, 'dashboard'])->middleware('can:view course')->name('courses.chapters.dashboard');
            Route::post('/', [ChapterController::class, 'store'])->middleware('can:add chapter')->name('courses.chapters.store');
            Route::put('/{chapter}', [ChapterController::class, 'update'])->middleware('can:edit chapter')->name('courses.chapters.update');
            Route::delete('/{chapter}', [ChapterController::class, 'destroy'])->middleware('can:delete chapter')->name('courses.chapters.destroy');
        });
    });

    Route::prefix('topics')->group(function () {
        Route::get('/', [TopicController::class, 'index'])->name('topics');
        Route::get('/create', [TopicController::class, 'create'])->name('topics.create');
        Route::post('/', [TopicController::class, 'store'])->name('topics.store');
        Route::get('/{id}/assign', [TopicController::class, 'assign'])->name('topics.assign');
        Route::get('/{id}/edit', [TopicController::class, 'edit'])->name('topics.edit');
        Route::put('/{id}', [TopicController::class, 'update'])->name('topics.update');
    });

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

    /*
    |----------------------------------------------------------------------
    | Assessments — quizzes and the question bank
    |----------------------------------------------------------------------
    */

    Route::prefix('quizzes')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('quizzes');
        Route::get('/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('/{id}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/{id}', [QuizController::class, 'update'])->name('quizzes.update');
    });

    Route::prefix('questions')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('questions');
        Route::get('/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/', [QuestionController::class, 'store'])->name('questions.store');
    });

    /*
    |----------------------------------------------------------------------
    | Content resources — guides, diagrams, videos, flashcards, notes, summaries
    |----------------------------------------------------------------------
    */

    Route::prefix('guides')->group(function () {
        Route::get('/', [GuideController::class, 'index'])->name('guides');
        Route::get('/create', [GuideController::class, 'create'])->name('guides.create');
        Route::post('/', [GuideController::class, 'store'])->name('guides.store');
        Route::get('/{id}/edit', [GuideController::class, 'edit'])->name('guides.edit');
        Route::put('/{id}', [GuideController::class, 'update'])->name('guides.update');
    });

    // Diagrams are served by ImageController (shared media handling)
    Route::prefix('diagrams')->group(function () {
        Route::get('/', [ImageController::class, 'index'])->name('diagrams');
        Route::get('/create', [ImageController::class, 'create'])->name('diagrams.create');
        Route::post('/', [ImageController::class, 'store'])->name('diagrams.store');
        Route::get('/{id}/edit', [ImageController::class, 'edit'])->name('diagrams.edit');
        Route::put('/{id}', [ImageController::class, 'update'])->name('diagrams.update');
    });

    Route::prefix('videos')->group(function () {
        Route::get('/', [VideoController::class, 'index'])->name('videos');
        Route::get('/create', [VideoController::class, 'create'])->name('videos.create');
        Route::post('/', [VideoController::class, 'store'])->name('videos.store');
        Route::get('/{id}/edit', [VideoController::class, 'edit'])->name('videos.edit');
        Route::put('/{id}', [VideoController::class, 'update'])->name('videos.update');
    });

    Route::prefix('flashcards')->group(function () {
        Route::get('/', [FlashcardController::class, 'index'])->name('flashcards');
        Route::get('/create', [FlashcardController::class, 'create'])->name('flashcards.create');
        Route::post('/', [FlashcardController::class, 'store'])->name('flashcards.store');
        Route::get('/{id}/edit', [FlashcardController::class, 'edit'])->name('flashcards.edit');
        Route::put('/{id}', [FlashcardController::class, 'update'])->name('flashcards.update');
    });

    Route::prefix('notes')->group(function () {
        Route::get('/', [NoteController::class, 'index'])->name('notes');
        Route::post('/', [NoteController::class, 'store'])->name('notes.store');
        Route::get('/{id}/edit', [NoteController::class, 'edit'])->name('notes.edit');
        Route::put('/{id}', [NoteController::class, 'update'])->name('notes.update');
    });

    Route::prefix('summaries')->group(function () {
        Route::get('/', [SummaryController::class, 'index'])->name('summaries');
        Route::post('/', [SummaryController::class, 'store'])->name('summaries.store');
        Route::get('/{id}/edit', [SummaryController::class, 'edit'])->name('summaries.edit');
        Route::put('/{id}', [SummaryController::class, 'update'])->name('summaries.update');
    });

    /*
    |----------------------------------------------------------------------
    | People — the student roster
    |----------------------------------------------------------------------
    */

    Route::get('/students', [StudentController::class, 'index'])->name('students');

});
