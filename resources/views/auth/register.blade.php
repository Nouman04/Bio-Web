@extends('layouts.auth')

@section('title', 'Create Student Account')

@section('content')
<div class="w-full max-w-auth-card-width relative">
    {{-- Decorative background blobs --}}
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary opacity-10 rounded-full blur-3xl z-0 pointer-events-none"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-secondary-container opacity-30 rounded-full blur-3xl z-0 pointer-events-none"></div>

    {{-- The Glass Card --}}
    <div class="glass-card rounded-lg p-lg relative z-10 w-full flex flex-col gap-md">

        {{-- Header --}}
        <div class="flex flex-col items-center justify-center text-center gap-sm mb-sm">
            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center mb-sm shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
            </div>
            <h1 class="text-3xl font-semibold text-primary">EduStudent</h1>
            <p class="text-base text-secondary">Create your account to start learning.</p>
        </div>

        {{-- Signup Form --}}
        <form class="flex flex-col gap-sm" method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="name">Full Name</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">badge</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200" id="name" name="name" placeholder="Jane Doe" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
                </div>
                @error('name')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="email">Email Address</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">mail</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200" id="email" name="email" placeholder="e.g. jdoe@example.com" type="email" value="{{ old('email') }}" required autocomplete="username">
                </div>
                @error('email')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="password">Password</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">lock</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200" id="password" name="password" placeholder="••••••••" type="password" required autocomplete="new-password">
                </div>
                @error('password')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="password_confirmation">Confirm Password</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">lock_reset</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200" id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" required autocomplete="new-password">
                </div>
                @error('password_confirmation')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Primary Action --}}
            <button class="w-full bg-primary text-on-primary text-sm font-semibold py-sm rounded mt-sm hover:opacity-90 hover:shadow-[0_0_15px_rgba(0, 19, 48, 0.3)] active:scale-[0.98] transition-all duration-200 flex justify-center items-center gap-xs" type="submit">
                Create Account
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center gap-sm my-xs">
            <div class="h-px bg-outline-variant flex-1"></div>
            <span class="text-xs font-semibold text-outline">OR</span>
            <div class="h-px bg-outline-variant flex-1"></div>
        </div>

        {{-- Google Sign Up --}}
        <a href="{{ route('google.login') }}"
            class="w-full bg-transparent border border-outline-variant rounded py-[10px] px-sm flex items-center justify-center gap-sm hover:bg-surface-container-low active:scale-[0.98] transition-all duration-200 text-on-surface text-sm font-semibold">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        {{-- Footer Link --}}
        <div class="text-center mt-sm">
            <p class="text-base text-secondary">
                Already have an account? <a class="text-primary font-semibold hover:underline hover:text-primary-container transition-colors" href="{{ route('student.login') }}">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
