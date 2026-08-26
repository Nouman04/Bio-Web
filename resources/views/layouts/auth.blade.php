<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') | EduAdmin LMS</title>

    {{-- Tailwind CSS --}}
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>

    {{-- Tailwind Config (shared design tokens) --}}
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-secondary-fixed": "rgb(var(--c-on-secondary-fixed) / <alpha-value>)",
                        "on-error": "rgb(var(--c-on-error) / <alpha-value>)",
                        "tertiary-fixed-dim": "rgb(var(--c-tertiary-fixed-dim) / <alpha-value>)",
                        "on-tertiary-fixed-variant": "rgb(var(--c-on-tertiary-fixed-variant) / <alpha-value>)",
                        "surface-container-lowest": "rgb(var(--c-surface-container-lowest) / <alpha-value>)",
                        "surface": "rgb(var(--c-surface) / <alpha-value>)",
                        "on-primary-fixed": "rgb(var(--c-on-primary-fixed) / <alpha-value>)",
                        "surface-bright": "rgb(var(--c-surface-bright) / <alpha-value>)",
                        "on-tertiary-fixed": "rgb(var(--c-on-tertiary-fixed) / <alpha-value>)",
                        "inverse-primary": "rgb(var(--c-inverse-primary) / <alpha-value>)",
                        "background": "rgb(var(--c-background) / <alpha-value>)",
                        "outline": "rgb(var(--c-outline) / <alpha-value>)",
                        "surface-container-low": "rgb(var(--c-surface-container-low) / <alpha-value>)",
                        "inverse-on-surface": "rgb(var(--c-inverse-on-surface) / <alpha-value>)",
                        "surface-variant": "rgb(var(--c-surface-variant) / <alpha-value>)",
                        "on-error-container": "rgb(var(--c-on-error-container) / <alpha-value>)",
                        "secondary-fixed-dim": "rgb(var(--c-secondary-fixed-dim) / <alpha-value>)",
                        "outline-variant": "rgb(var(--c-outline-variant) / <alpha-value>)",
                        "on-secondary-container": "rgb(var(--c-on-secondary-container) / <alpha-value>)",
                        "tertiary": "rgb(var(--c-tertiary) / <alpha-value>)",
                        "surface-container-high": "rgb(var(--c-surface-container-high) / <alpha-value>)",
                        "primary-container": "rgb(var(--c-primary-container) / <alpha-value>)",
                        "error-container": "rgb(var(--c-error-container) / <alpha-value>)",
                        "primary-fixed": "rgb(var(--c-primary-fixed) / <alpha-value>)",
                        "on-tertiary-container": "rgb(var(--c-on-tertiary-container) / <alpha-value>)",
                        "secondary-container": "rgb(var(--c-secondary-container) / <alpha-value>)",
                        "error": "rgb(var(--c-error) / <alpha-value>)",
                        "surface-container": "rgb(var(--c-surface-container) / <alpha-value>)",
                        "on-primary-container": "rgb(var(--c-on-primary-container) / <alpha-value>)",
                        "inverse-surface": "rgb(var(--c-inverse-surface) / <alpha-value>)",
                        "on-secondary-fixed-variant": "rgb(var(--c-on-secondary-fixed-variant) / <alpha-value>)",
                        "surface-container-highest": "rgb(var(--c-surface-container-highest) / <alpha-value>)",
                        "tertiary-container": "rgb(var(--c-tertiary-container) / <alpha-value>)",
                        "tertiary-fixed": "rgb(var(--c-tertiary-fixed) / <alpha-value>)",
                        "on-primary-fixed-variant": "rgb(var(--c-on-primary-fixed-variant) / <alpha-value>)",
                        "on-primary": "rgb(var(--c-on-primary) / <alpha-value>)",
                        "on-surface": "rgb(var(--c-on-surface) / <alpha-value>)",
                        "surface-dim": "rgb(var(--c-surface-dim) / <alpha-value>)",
                        "secondary": "rgb(var(--c-secondary) / <alpha-value>)",
                        "on-tertiary": "rgb(var(--c-on-tertiary) / <alpha-value>)",
                        "primary-fixed-dim": "rgb(var(--c-primary-fixed-dim) / <alpha-value>)",
                        "on-surface-variant": "rgb(var(--c-on-surface-variant) / <alpha-value>)",
                        "surface-tint": "rgb(var(--c-surface-tint) / <alpha-value>)",
                        "primary": "rgb(var(--c-primary) / <alpha-value>)",
                        "on-secondary": "rgb(var(--c-on-secondary) / <alpha-value>)",
                        "secondary-fixed": "rgb(var(--c-secondary-fixed) / <alpha-value>)",
                        "on-background": "rgb(var(--c-on-background) / <alpha-value>)"
                    },
                    borderRadius: {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "md": "24px",
                        "sm": "12px",
                        "xl": "80px",
                        "container-max": "1280px",
                        "auth-card-width": "440px",
                        "lg": "48px",
                        "base": "8px",
                        "xs": "4px"
                    },
                    fontFamily: {
                        "sans": ["Geist", "sans-serif"]
                    }
                }
            }
        }
    </script>

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
                radial-gradient(at 40% 20%, hsla(240,100%,74%,1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(189,100%,56%,1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(355,100%,93%,1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, hsla(340,100%,76%,1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(22,100%,77%,1) 0px, transparent 50%),
                radial-gradient(at 80% 100%, hsla(242,100%,70%,1) 0px, transparent 50%),
                radial-gradient(at 0% 0%, hsla(343,100%,76%,1) 0px, transparent 50%);
            opacity: 0.9;
        }
    </style>

    @stack('styles')
</head>
<body class="@yield('body-class', 'bg-pattern min-h-screen flex items-center justify-center p-sm md:p-lg text-on-surface antialiased')">
    @yield('content')

    @stack('scripts')
</body>
</html>
