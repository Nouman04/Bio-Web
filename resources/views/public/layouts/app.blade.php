<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield("title", "Your Biology")</title>
<link rel="stylesheet" href="{{ asset('css/public.css') }}">
<link rel="stylesheet" href="{{ asset('fonts/symbols.css') }}">
<link rel="stylesheet" href="{{ asset('fonts/geist.css') }}">
<style>
        body { font-family: 'Geist', sans-serif; background-color: #f7f9fb; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 24px -2px rgba(0, 19, 48, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
        }
        .glass-nav {
            background: rgba(0, 19, 48, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.09);
        }
        .hero-glow-1 {
            position: absolute;
            top: -150px;
            right: -100px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 19, 48, 0.15) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: -1;
            pointer-events: none;
        }
        .hero-glow-2 {
            position: absolute;
            bottom: -200px;
            left: -200px;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(72,188,212,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: -1;
            pointer-events: none;
        }
        .btn-glow:hover {
            box-shadow: 0 0 20px 0 rgba(0, 19, 48, 0.4);
        }
    </style>@stack('styles')
</head>
<body class="bg-background text-on-surface antialiased overflow-x-hidden selection:bg-primary-container selection:text-white">

    @include('public.partials.header')

    @yield('content')

    @include('public.partials.footer')

    @stack('scripts')
</body>
</html>
