@php
    $c = function($cls) {
        return 'md:' . $cls;
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin Panel - {{ config('app.name', 'PPKD Hotel') }}</title>

        <!-- Theme Initialization Script to Prevent Flicker -->
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Cleopatra Theme Styles & Font Awesome -->
        <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v5.12.1/css/pro.min.css">
        <link rel="stylesheet" type="text/css" href="{{ asset('template/dist/css/style.css') }}">
        
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>
    <body class="bg-gray-100 text-slate-800 antialiased transition-colors duration-250">
        
        <!-- start wrapper -->
        <div class="min-h-screen flex flex-row">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')

            <!-- Main Content Container -->
            <div class="flex-grow flex flex-col min-h-screen overflow-x-hidden">
                <!-- start navbar -->
                <div class="relative {{ $c('fixed') }} {{ $c('w-full') }} {{ $c('top-0') }} {{ $c('left-0') }} {{ $c('z-20') }} flex flex-row items-center justify-between bg-white px-6 border-b border-gray-300 h-16 shadow-sm">

                    <!-- Left side: Hamburger button (mobile only) and Brand (desktop only) -->
                    <div class="flex items-center space-x-4">
                        <!-- Hamburger Menu Button: Mobile Only -->
                        <button id="sliderBtn" class="text-gray-900 focus:outline-none hidden {{ $c('block') }}">
                            <i class="fad fa-bars text-lg"></i>
                        </button>

                        <!-- Logo & Brand: Desktop Only -->
                        <div class="flex items-center space-x-2 block {{ $c('hidden') }}">
                            <img src="{{ asset('template/dist/img/logo.png') }}" class="w-8 h-8">
                            <strong class="capitalize text-teal-600 font-bold tracking-wider text-base sm:text-lg">PPKD</strong>
                        </div>
                    </div>

                    <!-- Date Display -->
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <span class="text-xs sm:text-sm text-slate-500 hidden sm:inline {{ $c('hidden') }}">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                </div>
                <!-- end navbar -->

                <!-- Main Content Area -->
                <div class="bg-gray-100 flex-1 p-6 {{ $c('mt-16') }} min-h-screen">
                
                <!-- Page Title -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        @yield('header-title', 'Admin Dashboard')
                    </h1>
                </div>
                
                <!-- Notifications -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg flex items-center shadow-sm alert alert-teal">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button class="alert-btn-close ml-4 text-emerald-500 hover:text-emerald-700 font-bold focus:outline-none">×</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg flex items-center shadow-sm alert alert-rose">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button class="alert-btn-close ml-4 text-rose-500 hover:text-rose-700 font-bold focus:outline-none">×</button>
                    </div>
                @endif

                <!-- Content Slot -->
                @yield('content')

                <!-- footer -->
                <div class="mt-10 pt-4 border-t border-gray-200 text-center text-xs text-gray-600">
                    Copyright <span id="tw-current-year"></span> ©
                    <a href="#" class="text-teal-600 hover:text-teal-700 hover:underline">
                        {{ config('app.name', 'PPKD Hotel') }}
                    </a>.
                    All Right Reserved.
                </div>
                <!-- end footer -->
            </div>
            <!-- end content -->
            </div>
            <!-- end main content container -->
        </div>
        <!-- end wrapper -->

        <!-- Cleopatra Theme Scripts & Custom Toggles -->
        <script src="{{ asset('template/dist/js/scripts.js') }}"></script>
        
        <script>
            function toggleTheme() {
                const html = document.documentElement;
                const sunIcon = document.getElementById('theme-icon-sun');
                const moonIcon = document.getElementById('theme-icon-moon');
                
                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    if(sunIcon) sunIcon.classList.add('hidden');
                    if(moonIcon) moonIcon.classList.remove('hidden');
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    if(sunIcon) sunIcon.classList.remove('hidden');
                    if(moonIcon) moonIcon.classList.add('hidden');
                }
            }

            // Sync theme UI elements on initial page load
            (function() {
                const html = document.documentElement;
                const sunIcon = document.getElementById('theme-icon-sun');
                const moonIcon = document.getElementById('theme-icon-moon');
                if (html.classList.contains('dark')) {
                    if(sunIcon) sunIcon.classList.remove('hidden');
                    if(moonIcon) moonIcon.classList.add('hidden');
                } else {
                    if(sunIcon) sunIcon.classList.add('hidden');
                    if(moonIcon) moonIcon.classList.remove('hidden');
                }
            })();

            // Auto-update copyright year
            (function () {
                var el = document.getElementById('tw-current-year');
                if (el) el.textContent = new Date().getFullYear();
            })();
        </script>

        @auth
        <script>
            if (!sessionStorage.getItem('tab_session_active')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('logout') }}";
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        </script>
        @endauth
    </body>
</html>
