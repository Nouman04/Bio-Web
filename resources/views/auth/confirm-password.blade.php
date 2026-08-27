@extends('layouts.auth')

@section('title', 'Confirm Your Password')

@section('content')
<div class="w-full max-w-auth-card-width relative">
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary opacity-10 rounded-full blur-3xl z-0 pointer-events-none"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-secondary-container opacity-30 rounded-full blur-3xl z-0 pointer-events-none"></div>

    <div class="glass-card rounded-lg p-lg relative z-10 w-full flex flex-col gap-md">

        <div class="flex flex-col items-center justify-center text-center gap-sm mb-sm">
            <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center mb-sm shadow-sm">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified_user</span>
            </div>
            <h1 class="text-3xl font-semibold text-primary">Confirm it is you</h1>
            <p class="text-base text-secondary">Enter your password again before going any further.</p>
        </div>

        <form class="flex flex-col gap-sm" method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="flex flex-col gap-xs">
                <label class="text-sm font-medium text-on-surface-variant" for="password">Password</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-sm text-outline-variant pointer-events-none z-10">lock</span>
                    <input class="w-full bg-surface-container-low border-none rounded pl-[44px] pr-sm py-sm text-base text-on-surface focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200"
                        id="password" name="password" placeholder="••••••••" type="password"
                        required autocomplete="current-password" autofocus>
                </div>
                @error('password')
                    <p class="text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full flex justify-center items-center gap-xs py-sm px-md rounded bg-primary text-on-primary text-base font-semibold shadow-[0_4px_14px_0_rgba(0,19,48,0.3)] hover:bg-primary-container active:scale-[0.98] transition-all duration-200 mt-sm"
                type="submit">
                Confirm
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </form>
    </div>
</div>
@endsection
