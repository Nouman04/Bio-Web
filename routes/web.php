<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
|
| The marketing pages, open to everyone: the landing page plus the support
| and legal pages linked from the footer.
|
*/

Route::get('/home', [PublicPageController::class, 'home'])->name('public.home');

// The course gallery. It cannot live at /courses — that path is the admin
// course manager — so the public listing gets its own.
Route::get('/our-courses', [PublicPageController::class, 'courses'])->name('public.courses');
Route::get('/our-courses/instructors', [PublicPageController::class, 'instructors'])->name('public.courses.instructors');

// What a subscription includes, in general
Route::get('/subscribe', [PublicCourseController::class, 'subscribe'])->name('public.subscribe');

// The plan for one course, and the handover to Stripe
Route::get('/subscribe/{course}', [PublicCourseController::class, 'plans'])->name('public.subscribe.plans');
Route::get('/subscribe/{course}/checkout', [PublicCourseController::class, 'checkout'])->name('public.subscribe.checkout');
// Where Stripe returns the reader after paying
Route::get('/subscribe/{course}/done', [PublicCourseController::class, 'subscribed'])->name('public.subscribe.success');

/*
| The catalogue chain: course › chapters › chapter › listing › detail.
| Private chapters are not reachable here — see PublicCourseController::scope().
*/
Route::prefix('our-courses/{course}')->name('public.course.')->group(function () {
    Route::get('/', [PublicCourseController::class, 'chapters'])->name('chapters');

    Route::prefix('chapters/{chapter}')->name('chapter.')->group(function () {
        Route::get('/', [PublicCourseController::class, 'chapter'])->name('show');

        // Where a locked chapter sends the reader
        Route::get('/subscribe', [PublicCourseController::class, 'subscribe'])->name('subscribe');
        Route::get('/subscribe/plan', [PublicCourseController::class, 'plans'])->name('subscribe.plans');

        Route::get('/notes', [PublicCourseController::class, 'notes'])->name('notes');
        Route::get('/notes/{note}', [PublicCourseController::class, 'note'])->name('notes.show');

        Route::get('/flashcards', [PublicCourseController::class, 'flashcards'])->name('flashcards');
        Route::get('/flashcards/{flashcard}', [PublicCourseController::class, 'flashcard'])->name('flashcards.show');

        Route::get('/mcqs', [PublicCourseController::class, 'mcqs'])->name('mcqs');
        Route::get('/mcqs/{quiz}', [PublicCourseController::class, 'mcq'])->name('mcqs.show');
        // Marks an attempt and returns the score; nothing is stored.
        Route::post('/mcqs/{quiz}', [PublicCourseController::class, 'submitMcq'])->name('mcqs.submit');

        Route::get('/theory', [PublicCourseController::class, 'theory'])->name('theory');
        Route::get('/theory/{quiz}', [PublicCourseController::class, 'theoryDetail'])->name('theory.show');
    });
});

Route::get('/about', [PublicPageController::class, 'page'])->defaults('page', 'about')->name('public.about');
Route::get('/contact-us', [PublicPageController::class, 'page'])->defaults('page', 'contact')->name('public.contact');
Route::get('/faq', [PublicPageController::class, 'page'])->defaults('page', 'faq')->name('public.faq');
Route::get('/disclaimer', [PublicPageController::class, 'page'])->defaults('page', 'disclaimer')->name('public.disclaimer');
Route::get('/privacy-policy', [PublicPageController::class, 'page'])->defaults('page', 'privacy')->name('public.privacy');
Route::get('/terms-and-conditions', [PublicPageController::class, 'page'])->defaults('page', 'terms')->name('public.terms');

// The root lands on the public home page.
Route::get('/', fn () => redirect()->route('public.home'))->name('home');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
