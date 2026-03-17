<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MCHost') }} — @yield('title', 'Autenticação')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="mc-auth-page">
            <div class="mc-auth-container">
                <a href="{{ route('home') }}" class="mc-auth-logo">
                    <svg width="48" height="48" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="8" fill="url(#logo-gradient-auth)"/>
                        <path d="M8 12h4v4H8v-4zm4 4h4v4h-4v-4zm4-4h4v4h-4v-4zm4 4h4v4h-4v-4zm-8-8h4v4h-4V8zm4 16h4v4h-4v-4z" fill="rgba(255,255,255,0.9)"/>
                        <defs>
                            <linearGradient id="logo-gradient-auth" x1="0" y1="0" x2="32" y2="32">
                                <stop stop-color="#6c5ce7"/>
                                <stop offset="1" stop-color="#a29bfe"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>MCHost</span>
                </a>
                <div class="mc-auth-card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
