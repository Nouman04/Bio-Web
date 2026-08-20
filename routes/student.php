<?php

use App\Http\Controllers\SearchController;
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
    Route::get('/courses/{id}', [StudentCoursesController::class, 'show'])->name('courses.show');

    // Chapters — list of chapters for a course, and individual chapter dashboard
    Route::get('/courses/{courseId}/chapters', [StudentChaptersController::class, 'index'])->name('chapters');
    Route::get('/courses/{courseId}/chapters/{chapterId}', [StudentChaptersController::class, 'show'])->name('chapters.show');

    // Chapter resource sub-pages
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

    // Catalog — browse all available courses
    Route::get('/catalog', [StudentCatalogController::class, 'index'])->name('catalog');

    // Resources — study materials, guides, flashcards, videos, notes
    Route::get('/resources', [StudentResourcesController::class, 'index'])->name('resources');

    // Global search behind the header's magnifier
    Route::get('/search', [SearchController::class, 'student'])->name('search');

    // Demo — bare sidenav + header shell for layout/responsiveness testing
    Route::get('/demo', fn () => view('student.demo'))->name('demo');

});
