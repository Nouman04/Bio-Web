<?php

use App\Http\Controllers\SearchController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\SavedContentController;
use App\Http\Controllers\Student\StudentQuizController;
use App\Http\Controllers\Student\StudentWorksheetController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCoursesController;
use App\Http\Controllers\Student\StudentCatalogController;
use App\Http\Controllers\Student\StudentResourcesController;
use App\Http\Controllers\Student\StudentChaptersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
| These routes are scoped under the /student prefix and are for signed-in
| students only — staff accounts are bounced back to the admin dashboard.
|
*/
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    // Courses — enrolled courses list
    Route::get('/courses', [StudentCoursesController::class, 'index'])->name('courses');
    Route::get('/courses/{course}', [StudentCoursesController::class, 'show'])->name('courses.show');

    // Chapters — list of chapters for a course, and individual chapter dashboard
    Route::get('/courses/{courseId}/chapters', [StudentChaptersController::class, 'index'])->name('chapters');

    /*
    | Everything below opens one chapter's content, so the paywall applies:
    | a private chapter needs a subscription, a public one is the free preview.
    | Declaring it here means a new route under a chapter is covered by being
    | in the group, rather than by remembering to check inside the action.
    */
    Route::middleware('subscribed')->group(function () {
        Route::get('/courses/{courseId}/chapters/{chapterId}', [StudentChaptersController::class, 'show'])->name('chapters.show');

        Route::get('/courses/{courseId}/chapters/{chapterId}/notes', [StudentChaptersController::class, 'notes'])->name('chapters.notes');
        Route::get('/courses/{courseId}/chapters/{chapterId}/notes/{noteId}', [StudentChaptersController::class, 'showNote'])->name('chapters.notes.show');
    
        Route::get('/courses/{courseId}/chapters/{chapterId}/flashcards', [StudentChaptersController::class, 'flashcards'])->name('chapters.flashcards');
        Route::get('/courses/{courseId}/chapters/{chapterId}/flashcards/{flashcardId}', [StudentChaptersController::class, 'showFlashcard'])->name('chapters.flashcards.show');
    
        Route::get('/courses/{courseId}/chapters/{chapterId}/diagrams', [StudentChaptersController::class, 'diagrams'])->name('chapters.diagrams');
        Route::get('/courses/{courseId}/chapters/{chapterId}/diagrams/{diagramId}', [StudentChaptersController::class, 'showDiagram'])->name('chapters.diagrams.show');
    
        Route::get('/courses/{courseId}/chapters/{chapterId}/summaries', [StudentChaptersController::class, 'summaries'])->name('chapters.summaries');
        Route::get('/courses/{courseId}/chapters/{chapterId}/summaries/{summaryId}', [StudentChaptersController::class, 'showSummary'])->name('chapters.summaries.show');

        Route::get('/courses/{courseId}/chapters/{chapterId}/videos', [StudentChaptersController::class, 'videos'])->name('chapters.videos');
        Route::get('/courses/{courseId}/chapters/{chapterId}/videos/{videoId}', [StudentChaptersController::class, 'showVideo'])->name('chapters.videos.show');

        Route::get('/courses/{courseId}/chapters/{chapterId}/guides', [StudentChaptersController::class, 'guides'])->name('chapters.guides');
        Route::get('/courses/{courseId}/chapters/{chapterId}/guides/{guideId}', [StudentChaptersController::class, 'showGuide'])->name('chapters.guides.show');

    // Quizzes — sat in the portal so the attempt is recorded and timed
        Route::get('/courses/{courseId}/chapters/{chapterId}/quizzes', [StudentQuizController::class, 'chapterIndex'])->name('chapters.quizzes');
        Route::get('/courses/{courseId}/chapters/{chapterId}/quizzes/{quizId}', [StudentQuizController::class, 'show'])->name('chapters.quizzes.show');
        Route::post('/courses/{courseId}/chapters/{chapterId}/quizzes/{quizId}', [StudentQuizController::class, 'submit'])->name('chapters.quizzes.submit');
    });

    // Worksheets — a student builds their own practice paper out of the
    // question bank, then downloads it with or without the answers.
    Route::get('/worksheets', [StudentWorksheetController::class, 'index'])->name('worksheets');
    Route::get('/worksheets/create', [StudentWorksheetController::class, 'create'])->name('worksheets.create');
    Route::get('/worksheets/options', [StudentWorksheetController::class, 'options'])->name('worksheets.options');
    Route::get('/worksheets/preview', [StudentWorksheetController::class, 'preview'])->name('worksheets.preview');
    Route::post('/worksheets', [StudentWorksheetController::class, 'store'])->name('worksheets.store');
    Route::get('/worksheets/{worksheet}', [StudentWorksheetController::class, 'show'])->name('worksheets.show');
    Route::get('/worksheets/{worksheet}/paper', [StudentWorksheetController::class, 'paper'])->name('worksheets.paper');
    Route::get('/worksheets/{worksheet}/mark-scheme', [StudentWorksheetController::class, 'markScheme'])->name('worksheets.mark-scheme');
    Route::delete('/worksheets/{worksheet}', [StudentWorksheetController::class, 'destroy'])->name('worksheets.destroy');

    Route::get('/quizzes', [StudentQuizController::class, 'index'])->name('quizzes');
    Route::get('/quizzes/{attempt}/report', [StudentQuizController::class, 'report'])->name('quizzes.report');

    // Bookmarking — saved items are listed on the resources page
    Route::post('/saved', [SavedContentController::class, 'toggle'])->name('saved.toggle');

    // Progress — what the reader did with a module, reported from the page
    Route::post('/progress/{module}/watched', [ProgressController::class, 'watched'])->name('progress.watched');
    Route::post('/progress/{module}/viewed', [ProgressController::class, 'viewed'])->name('progress.viewed');
    Route::post('/progress/{module}/manual', [ProgressController::class, 'manual'])->name('progress.manual');

    // Catalog — browse all available courses
    Route::get('/catalog', [StudentCatalogController::class, 'index'])->name('catalog');

    // Resources — study materials, guides, flashcards, videos, notes
    Route::get('/resources', [StudentResourcesController::class, 'index'])->name('resources');

    // Global search behind the header's magnifier
    Route::get('/search', [SearchController::class, 'student'])->name('search');

    // Demo — bare sidenav + header shell for layout/responsiveness testing
    Route::get('/demo', fn () => view('student.demo'))->name('demo');

});
