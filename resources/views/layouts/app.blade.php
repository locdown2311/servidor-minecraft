<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Hospedagem premium de servidores Minecraft. Crie seu servidor em segundos com acesso FTP, suporte a mods e painel completo.">

        <title>{{ config('app.name', 'MCHost') }} — @yield('title', 'Hospedagem Minecraft')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="mc-app">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="mc-page-header">
                    <div class="mc-container">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mc-container" style="padding-top: 1rem;">
                    <div class="mc-alert mc-alert-success">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mc-container" style="padding-top: 1rem;">
                    <div class="mc-alert mc-alert-error">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="mc-footer">
                <div class="mc-container">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'MCHost') }}. Todos os direitos reservados.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
