<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login - {{ config('app.name', 'Hotel Kejora') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Outfit', sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            }
        </style>
    </head>
    <body class="text-slate-200 antialiased flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/10 p-8 rounded-2xl shadow-2xl">
            <!-- Branding -->
            <div class="text-center mb-8">
                <span class="text-3xl font-bold tracking-widest text-amber-500">KEJORA</span>
                <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest">Hotel Management System</p>
            </div>

            <!-- Content Slot -->
            <div>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
