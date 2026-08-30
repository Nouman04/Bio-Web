<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') | Your Biology</title>

    {{-- Tailwind CSS --}}
    {{-- Opens the font connections while the document is still parsing. --}}

    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    {{-- The built stylesheet. Was cdn.tailwindcss.com, which compiles in
         the browser on every load — 3.6s of it on the chapter page. --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    {{-- Google Fonts --}}
    <link rel="stylesheet" href="{{ asset('fonts/symbols.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/geist.css') }}">

    {{-- Tailwind Config (shared design tokens) --}}

    <style>
        body { font-family: 'Geist', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-card, .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 19, 48, 0.05);
        }
        .bg-pattern {
            background-color: #f7f9fb;
            background-image: radial-gradient(#d2e1f7 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .input-field {
            background-color: #f2f4f6;
            transition: all 0.2s ease-in-out;
        }
        .input-field:focus-within {
            background-color: #ffffff;
            border-color: #001330;
            box-shadow: 0 0 0 2px #80b2ff;
        }
        /* The branding panel beside the sign-in form. It was a five-stop
           radial mesh with two blurred glows over it; it is the flat brand
           colour now, which is why it is no longer called a gradient. */
        .brand-panel {
            background-color: rgb(var(--c-primary));
        }
    </style>

    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-pattern min-h-screen flex items-center justify-center p-sm md:p-lg text-on-surface antialiased')">
    @yield('content')

    {{-- SweetAlert2 and the shared form helpers: the sign-in, register and
         password forms use App.validateForm to report a bad value inline
         before the round trip. --}}
    <script src="{{ asset('cdn/sweet-alert/sweetAlert2.min.js') }}"></script>
    <script src="{{ asset('js/app-ajax.js') }}"></script>
    <script src="{{ asset('js/password-strength.js') }}"></script>

    @stack('scripts')
</body>
</html>
