<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') | EduAdmin LMS</title>

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
        .mesh-gradient {
            background-color: #001330;
            background-image:
                radial-gradient(at 15% 15%, rgba(0, 65, 163, 0.85) 0px, transparent 55%),
                radial-gradient(at 85% 10%, rgba(29, 95, 208, 0.55) 0px, transparent 50%),
                radial-gradient(at 75% 80%, rgba(0, 40, 102, 0.95) 0px, transparent 55%),
                radial-gradient(at 10% 90%, rgba(0, 19, 48, 1) 0px, transparent 60%),
                radial-gradient(at 50% 50%, rgba(128, 178, 255, 0.16) 0px, transparent 65%);
        }
    </style>

    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-pattern min-h-screen flex items-center justify-center p-sm md:p-lg text-on-surface antialiased')">
    @yield('content')

    @stack('scripts')
</body>
</html>
