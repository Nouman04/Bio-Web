@extends('layouts.auth')

@section('title', 'Admin Sign In')
@section('body-class', 'bg-background text-on-background min-h-screen flex antialiased overflow-hidden')

@section('content')
<div class="w-full h-screen flex flex-col md:flex-row">

    {{-- Left Panel: Branding. A flat brand colour — it used to carry a
         radial mesh with two blurred glows painted over it. --}}
    <div class="hidden md:flex md:w-1/2 brand-panel relative items-center justify-center p-lg overflow-hidden">
        <div class="z-10 text-on-primary flex flex-col items-start max-w-md">
            <div class="flex items-center gap-sm mb-lg">
                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">school</span>
                <h1 class="text-5xl font-bold tracking-tight">Your Biology</h1>
            </div>
            <h2 class="text-3xl font-semibold mb-md text-white">The teaching side of Your Biology</h2>
            <p class="text-lg text-white/75">Build courses and chapters, keep the past-paper question bank current, mark student work, and see how every class is progressing — all from one place.</p>

            <ul class="mt-lg space-y-sm text-white/70 text-base">
                @foreach([
                    ['library_books', 'Courses, chapters and the question bank'],
                    ['fact_check', 'Mark quizzes and release results'],
                    ['insights', 'Track progress across your students'],
                ] as [$icon, $line])
                    <li class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-[20px] text-white/85">{{ $icon }}</span>
                        {{ $line }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Right Panel: Login Form --}}
    <div class="w-full md:w-1/2 flex items-center justify-center p-md md:p-lg bg-surface-container-lowest relative">
        {{-- Mobile Header --}}
        <div class="absolute top-0 left-0 w-full p-md flex items-center gap-xs md:hidden text-primary">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
            <span class="text-2xl font-bold">Your Biology</span>
        </div>

        <div class="w-full max-w-auth-card-width">
            <div class="mb-xl text-center md:text-left">
                <h2 class="text-2xl md:text-3xl font-semibold text-on-surface mb-xs">Welcome back</h2>
                <p class="text-base text-on-surface-variant">Sign in to manage your courses, mark student work, and keep the library up to date.</p>
            </div>

            <x-auth-session-status class="mb-md" :status="session('status')" />

            <form data-validate action="{{ route('admin.login.store') }}" class="space-y-md" method="POST">
                @csrf

                {{-- Email Field --}}
                <div class="space-y-xs">
                    <label class="block text-sm font-medium text-on-surface-variant" for="email">Email Address</label>
                    <div class="input-field rounded border border-transparent overflow-hidden flex items-center px-sm py-sm">
                        <span class="material-symbols-outlined text-on-surface-variant mr-sm">mail</span>
                        <input autocomplete="email" class="w-full bg-transparent border-none p-0 focus:ring-0 text-base text-on-surface placeholder-outline-variant" id="email" name="email" placeholder="admin@example.com" required type="email" value="{{ old('email') }}"/>
                    </div>
                    @error('email')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div class="space-y-xs">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-on-surface-variant" for="password">Password</label>
                        @if (Route::has('password.request'))
                            <a class="text-xs font-semibold text-primary hover:text-primary-container transition-colors" href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>
                    <div class="input-field rounded border border-transparent overflow-hidden flex items-center px-sm py-sm relative">
                        <span class="material-symbols-outlined text-on-surface-variant mr-sm">lock</span>
                        <input autocomplete="current-password" class="w-full bg-transparent border-none p-0 focus:ring-0 text-base text-on-surface placeholder-outline-variant pr-10" id="password" name="password" placeholder="••••••••" required type="password" minlength="8" data-label="Password"/>
                    </div>
                    @error('password')
                        <p class="text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <input class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary-container bg-surface-container-low cursor-pointer" id="remember-me" name="remember" type="checkbox"/>
                    <label class="ml-sm block text-sm text-on-surface-variant cursor-pointer" for="remember-me">
                        Keep me signed in
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="pt-sm">
                    <button class="w-full flex justify-center py-sm px-md border border-transparent rounded shadow-sm text-sm font-semibold text-on-primary bg-primary hover:bg-primary-container hover:shadow-md hover:shadow-primary/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 ease-out active:scale-[0.98]" type="submit">
                        Sign In
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="mt-lg mb-md relative">
                <div aria-hidden="true" class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-surface-variant"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-sm bg-surface-container-lowest text-xs font-semibold text-on-surface-variant">Staff accounts only</span>
                </div>
            </div>

            <p class="text-center text-sm text-on-surface-variant">
                Studying with us? <a class="text-primary font-semibold hover:underline hover:text-primary-container transition-colors" href="{{ route('student.login') }}">Sign in as a student</a>
            </p>
        </div>
    </div>
</div>
@endsection
