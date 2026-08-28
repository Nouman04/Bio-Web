@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="w-full max-w-auth-card-width relative">
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary opacity-10 rounded-full blur-3xl z-0 pointer-events-none"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-secondary-container opacity-30 rounded-full blur-3xl z-0 pointer-events-none"></div>

    <div class="glass-card rounded-lg p-lg relative z-10 w-full flex flex-col gap-md">

        <div class="flex flex-col items-center justify-center text-center gap-sm mb-sm">
            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center mb-sm shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">mark_email_read</span>
            </div>
            <h1 class="text-3xl font-semibold text-primary">Check your inbox</h1>
            <p class="text-base text-secondary">
                We have sent a verification link to your email address. Click it to finish setting up your account.
            </p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <p class="text-sm text-tertiary text-center font-medium">
                A fresh link is on its way to the address you registered with.
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="w-full flex justify-center items-center gap-xs py-sm px-md rounded bg-primary text-on-primary text-base font-semibold shadow-[0_4px_14px_0_rgba(0,19,48,0.3)] hover:bg-primary-container active:scale-[0.98] transition-all duration-200"
                type="submit">
                Send it again
                <span class="material-symbols-outlined text-[18px]">send</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-sm font-semibold text-secondary hover:text-primary transition-colors">
                Sign out
            </button>
        </form>
    </div>
</div>
@endsection
