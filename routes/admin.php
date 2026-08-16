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

            // Topics — always scoped to their chapter (course › chapter › topic)
            Route::prefix('{chapter}/topics')->group(function () {
                Route::get('/', [TopicController::class, 'index'])->name('topics');
                Route::get('/data', [TopicController::class, 'data'])->name('topics.data');
                Route::post('/', [TopicController::class, 'store'])->name('topics.store');
                Route::get('/{topic}/assign', [TopicController::class, 'assign'])->name('topics.assign');
                Route::get('/{topic}/questions', [TopicController::class, 'questions'])->name('topics.questions');
                Route::put('/{topic}', [TopicController::class, 'update'])->name('topics.update');
                Route::delete('/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
            });

            // Flashcard decks — also scoped to the chapter they belong to
            Route::prefix('{chapter}/flashcards')->group(function () {
                Route::get('/', [FlashcardController::class, 'index'])->name('flashcards');
                Route::get('/data', [FlashcardController::class, 'data'])->name('flashcards.data');
                Route::post('/', [FlashcardController::class, 'store'])->name('flashcards.store');

                // Dependent picker: the records available for a chosen source type
                Route::get('/sources/{type}', [FlashcardController::class, 'sourceRecords'])->name('flashcards.sources');

                // Step two of the flow — attaching questions from the bank
                Route::get('/{flashcard}/builder', [FlashcardController::class, 'builder'])->name('flashcards.builder');
                Route::get('/{flashcard}/questions', [FlashcardController::class, 'questions'])->name('flashcards.questions');
                Route::post('/{flashcard}/questions', [FlashcardController::class, 'addQuestions'])->name('flashcards.questions.add');
                Route::put('/{flashcard}/questions/order', [FlashcardController::class, 'reorderQuestions'])->name('flashcards.questions.order');
                Route::delete('/{flashcard}/questions/{assessment}', [FlashcardController::class, 'removeQuestion'])->name('flashcards.questions.remove');

                Route::put('/{flashcard}', [FlashcardController::class, 'update'])->name('flashcards.update');
                Route::delete('/{flashcard}', [FlashcardController::class, 'destroy'])->name('flashcards.destroy');
            });

            // Guides — also scoped to the chapter they belong to
            Route::prefix('{chapter}/guides')->group(function () {
                Route::get('/', [GuideController::class, 'index'])->name('guides');
                Route::get('/data', [GuideController::class, 'data'])->name('guides.data');
                Route::post('/', [GuideController::class, 'store'])->name('guides.store');
                Route::get('/{guide}/questions', [GuideController::class, 'questions'])->name('guides.questions');
                Route::put('/{guide}', [GuideController::class, 'update'])->name('guides.update');
                Route::delete('/{guide}', [GuideController::class, 'destroy'])->name('guides.destroy');
            });

            // Study notes — also scoped to the chapter they belong to
            Route::prefix('{chapter}/notes')->group(function () {
                Route::get('/', [NoteController::class, 'index'])->name('notes');
                Route::get('/data', [NoteController::class, 'data'])->name('notes.data');
                Route::post('/', [NoteController::class, 'store'])->name('notes.store');
                Route::put('/{note}', [NoteController::class, 'update'])->name('notes.update');
                Route::delete('/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
            });
        });
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
        // Type-ahead source for the shared question widget
        Route::get('/search', [QuestionController::class, 'search'])->name('questions.search');
        Route::get('/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/', [QuestionController::class, 'store'])->name('questions.store');
    });

    /*
    |----------------------------------------------------------------------
    | Content resources — guides, diagrams, videos, flashcards, notes, summaries
    |----------------------------------------------------------------------
    */


    // Diagrams are served by ImageController (shared media handling)
    Route::prefix('diagrams')->group(function () {
        Route::get('/', [ImageController::class, 'index'])->name('diagrams');
        Route::get('/data', [ImageController::class, 'data'])->name('diagrams.data');
        Route::post('/', [ImageController::class, 'store'])->name('diagrams.store');
        Route::get('/{diagram}/questions', [ImageController::class, 'questions'])->name('diagrams.questions');
        Route::put('/{diagram}', [ImageController::class, 'update'])->name('diagrams.update');
        Route::delete('/{diagram}', [ImageController::class, 'destroy'])->name('diagrams.destroy');
    });

    Route::prefix('videos')->group(function () {
        Route::get('/', [VideoController::class, 'index'])->name('videos');
        Route::get('/data', [VideoController::class, 'data'])->name('videos.data');
        Route::post('/', [VideoController::class, 'store'])->name('videos.store');
        Route::get('/{video}/questions', [VideoController::class, 'questions'])->name('videos.questions');
        Route::put('/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    });



    Route::prefix('summaries')->group(function () {
        Route::get('/', [SummaryController::class, 'index'])->name('summaries');
        Route::get('/data', [SummaryController::class, 'data'])->name('summaries.data');
        Route::post('/', [SummaryController::class, 'store'])->name('summaries.store');
        Route::get('/{summary}/questions', [SummaryController::class, 'questions'])->name('summaries.questions');
        Route::put('/{summary}', [SummaryController::class, 'update'])->name('summaries.update');
        Route::delete('/{summary}', [SummaryController::class, 'destroy'])->name('summaries.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | People — the student roster
    |----------------------------------------------------------------------
    */

    Route::get('/students', [StudentController::class, 'index'])->name('students');

});
