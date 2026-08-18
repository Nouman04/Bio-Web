<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// The site has no public landing page: everything starts at a sign-in. Guests
// get the student portal's login (the one most visitors want), and anyone
// already signed in goes straight to their own dashboard.
Route::get('/', function () {
    $user = request()->user();

    if (! $user) {
        return redirect()->route('student.login');
    }

    return redirect()->route($user->isStudent() ? 'student.dashboard' : 'dashboard');
})->name('home');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
