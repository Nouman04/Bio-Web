<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\VideoController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/students', [StudentController::class, 'index'])->name('students');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
Route::get('/courses', [CourseController::class, 'index'])->name('courses');
Route::get('/chapters', [ChapterController::class, 'index'])->name('chapters');
Route::get('/topics', [TopicController::class, 'index'])->name('topics');
Route::get('/topics/{id}/assign', [TopicController::class, 'assign'])->name('topics.assign');
Route::get('/questions', [QuestionController::class, 'index'])->name('questions');
Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
Route::get('/flashcards', [FlashcardController::class, 'index'])->name('flashcards');
Route::get('/flashcards/create', [FlashcardController::class, 'create'])->name('flashcards.create');
Route::post('/flashcards', [FlashcardController::class, 'store'])->name('flashcards.store');
Route::get('/notes', [NoteController::class, 'index'])->name('notes');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::get('/summaries', [SummaryController::class, 'index'])->name('summaries');
Route::post('/summaries', [SummaryController::class, 'store'])->name('summaries.store');
Route::get('/guides', [GuideController::class, 'index'])->name('guides');
Route::get('/guides/create', [GuideController::class, 'create'])->name('guides.create');
Route::post('/guides', [GuideController::class, 'store'])->name('guides.store');

Route::get('/images', [ImageController::class, 'index'])->name('images');
Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
Route::post('/images', [ImageController::class, 'store'])->name('images.store');

Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes');
Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');

Route::get('/videos', [VideoController::class, 'index'])->name('videos');
Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
