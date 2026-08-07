<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChapterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

    // Topics
    Route::get('/topics', [TopicController::class, 'index'])->name('topics');
    Route::get('/topics/{id}/assign', [TopicController::class, 'assign'])->name('topics.assign');

    // Quizzes
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{id}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{id}', [QuizController::class, 'update'])->name('quizzes.update');

    // Questions
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions');
    Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');

    // Guides
    Route::get('/guides', [GuideController::class, 'index'])->name('guides');
    Route::get('/guides/create', [GuideController::class, 'create'])->name('guides.create');
    Route::post('/guides', [GuideController::class, 'store'])->name('guides.store');

    // Images (Diagrams)
    Route::get('/diagrams', [ImageController::class, 'index'])->name('diagrams');
    Route::get('/diagrams/create', [ImageController::class, 'create'])->name('diagrams.create');
    Route::post('/diagrams', [ImageController::class, 'store'])->name('diagrams.store');

    // Videos
    Route::get('/videos', [VideoController::class, 'index'])->name('videos');
    Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students');

    // Flashcards
    Route::get('/flashcards', [FlashcardController::class, 'index'])->name('flashcards');
    Route::get('/flashcards/create', [FlashcardController::class, 'create'])->name('flashcards.create');
    Route::post('/flashcards', [FlashcardController::class, 'store'])->name('flashcards.store');

    // Notes
    Route::get('/notes', [NoteController::class, 'index'])->name('notes');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('courses');

    // Summaries
    Route::get('/summaries', [SummaryController::class, 'index'])->name('summaries');
    Route::post('/summaries', [SummaryController::class, 'store'])->name('summaries.store');

    // Chapters
    Route::get('/chapters', [ChapterController::class, 'index'])->name('chapters');
});

require __DIR__.'/auth.php';
