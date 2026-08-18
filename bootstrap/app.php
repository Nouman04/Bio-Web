<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'staff' => \App\Http\Middleware\EnsureUserIsStaff::class,
            'student' => \App\Http\Middleware\EnsureUserIsStudent::class,
        ]);

        // Send unauthenticated visitors to the branded login page for the
        // area they were trying to reach (student vs admin).
        $middleware->redirectGuestsTo(fn ($request) => $request->is('student/*')
            ? route('student.login')
            : route('login'));

        // And send already-signed-in visitors to their own home, so a student
        // who opens /login never lands on the admin dashboard.
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->isStudent()
            ? route('student.dashboard')
            : route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
