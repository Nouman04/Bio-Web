@extends('layouts.auth')

@section('title', 'Reset Your Password')

@section('content')
<div class="w-full max-w-auth-card-width relative">
    {{-- Decorative background blobs, matching the sign-in card. --}}
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary opacity-10 rounded-full blur-3xl z-0 pointer-events-none"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-secondary-container opacity-30 rounded-full blur-3xl z-0 pointer-events-none"></div>

    <div class="glass-card rounded-lg p-lg relative z-10 w-full flex flex-col gap-md">

        <div class="flex flex-col items-center justify-center text-center gap-sm mb-sm">
            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center mb-sm shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
            </div>
            <h1 class="text-3xl font-semibold text-primary">Forgot your password?</h1>
            <p class="text-base text-secondary">
                Give us the email address on your account and we will send you a link to choose a new password.
            </p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form data-validate class="flex flex-col gap-sm" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="email">Email Address</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">mail</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200"
                        id="email" name="email" placeholder="e.g. jdoe@example.com" type="email"
                        value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full flex justify-center items-center gap-xs py-sm px-md rounded bg-primary text-on-primary text-base font-semibold shadow-[0_4px_14px_0_rgba(0,19,48,0.3)] hover:bg-primary-container active:scale-[0.98] transition-all duration-200 mt-sm"
                type="submit">
                Email me a reset link
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </form>

        <p class="text-center text-sm text-secondary">
            Remembered it?
            <a class="font-semibold text-primary hover:text-primary-container transition-colors" href="{{ route('student.login') }}">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
