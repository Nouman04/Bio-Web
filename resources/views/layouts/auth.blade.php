<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') | EduAdmin LMS</title>

    {{-- Tailwind CSS --}}
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
                        "on-secondary-fixed": "#2a1700",
                        "on-error": "#ffffff",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-tertiary-fixed-variant": "#005236",
                        "surface-container-lowest": "#ffffff",
                        "surface": "#f7f9fb",
                        "on-primary-fixed": "#07006c",
                        "surface-bright": "#f7f9fb",
                        "on-tertiary-fixed": "#002113",
                        "inverse-primary": "#c0c1ff",
                        "background": "#f7f9fb",
                        "outline": "#767586",
                        "surface-container-low": "#f2f4f6",
                        "inverse-on-surface": "#eff1f3",
                        "surface-variant": "#e0e3e5",
                        "on-error-container": "#93000a",
                        "secondary-fixed-dim": "#ffb95f",
                        "outline-variant": "#c7c4d7",
                        "on-secondary-container": "#684000",
                        "tertiary": "#006c49",
                        "surface-container-high": "#e6e8ea",
                        "primary-container": "#6063ee",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#e1e0ff",
                        "on-tertiary-container": "#000703",
                        "secondary-container": "#fea619",
                        "error": "#ba1a1a",
                        "surface-container": "#eceef0",
                        "on-primary-container": "#fffbff",
                        "inverse-surface": "#2d3133",
                        "on-secondary-fixed-variant": "#653e00",
                        "surface-container-highest": "#e0e3e5",
                        "tertiary-container": "#00885d",
                        "tertiary-fixed": "#6ffbbe",
                        "on-primary-fixed-variant": "#2f2ebe",
                        "on-primary": "#ffffff",
                        "on-surface": "#191c1e",
                        "surface-dim": "#d8dadc",
                        "secondary": "#855300",
                        "on-tertiary": "#ffffff",
                        "primary-fixed-dim": "#c0c1ff",
                        "on-surface-variant": "#464554",
                        "surface-tint": "#494bd6",
                        "primary": "#4648d4",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#ffddb8",
                        "on-background": "#191c1e"
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(70, 72, 212, 0.05);
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
            border-color: #4648d4;
            box-shadow: 0 0 0 2px #c0c1ff;
        }
        .mesh-gradient {
            background-color: #4648d4;
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
