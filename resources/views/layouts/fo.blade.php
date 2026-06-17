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

        <title>Front Office - {{ config('app.name', 'Hotel Kejora') }}</title>

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
        
        <!-- start navbar -->
        <div class="relative {{ $c('fixed') }} {{ $c('w-full') }} {{ $c('top-0') }} {{ $c('left-0') }} {{ $c('z-20') }} flex flex-row items-center justify-between bg-white px-6 border-b border-gray-300 h-16 shadow-sm">

            <!-- Left side: Hamburger button (mobile only) and Brand / Title (desktop only) -->
            <div class="flex items-center space-x-4">
                <!-- Hamburger Menu Button: Mobile Only -->
                <button id="sliderBtn" class="text-gray-900 focus:outline-none hidden {{ $c('block') }}">
                    <i class="fad fa-bars text-lg"></i>
                </button>

                <!-- Logo & Brand: Desktop Only -->
                <div class="flex items-center space-x-2 block {{ $c('hidden') }}">
                    <img src="{{ asset('template/dist/img/logo.png') }}" class="w-8 h-8">
                    <strong class="capitalize text-teal-600 font-bold tracking-wider text-base sm:text-lg">KEJORA</strong>
                </div>

            </div>

            <!-- navbar content toggle button for mobile -->
            <button id="navbarToggle" class="hidden {{ $c('block') }} {{ $c('fixed') }} right-0 mr-6 focus:outline-none">
                <i class="fad fa-chevron-double-down"></i>
            </button>
            <!-- end navbar content toggle -->

            <!-- navbar content -->
            <div id="navbar" class="animated {{ $c('hidden') }} {{ $c('fixed') }} {{ $c('top-0') }} {{ $c('w-full') }} {{ $c('left-0') }} {{ $c('mt-16') }} {{ $c('border-t') }} {{ $c('border-b') }} {{ $c('border-gray-200') }} {{ $c('p-6') }} {{ $c('bg-white') }} flex flex-row justify-end items-center {{ $c('flex-col') }} {{ $c('items-center') }}">
                <!-- Right-aligned profile and theme toggle -->
                <div class="flex flex-row-reverse items-center justify-end {{ $c('flex-col') }} {{ $c('space-y-4') }} {{ $c('space-x-reverse') }}">
                    <!-- user -->
                    <div class="dropdown relative {{ $c('static') }} ml-4 {{ $c('ml-0') }} {{ $c('w-full') }}">
                        <button class="menu-btn focus:outline-none focus:shadow-outline flex flex-wrap items-center {{ $c('w-full') }} {{ $c('justify-center') }}">
                            <div class="w-8 h-8 overflow-hidden rounded-full border border-gray-300">
                                <img class="w-full h-full object-cover" src="{{ asset('template/dist/img/user.svg') }}" alt="user image">
                            </div>

                            <div class="ml-2 capitalize flex items-center">
                                <h1 class="text-sm text-gray-800 font-semibold m-0 p-0 leading-none">{{ auth()->user()->name }}</h1>
                                <i class="fad fa-chevron-down ml-2 text-xs leading-none"></i>
                            </div>
                        </button>

                        <button class="hidden fixed top-0 left-0 z-10 w-full h-full menu-overflow"></button>

                        <div class="text-gray-500 menu hidden {{ $c('mt-10') }} {{ $c('w-full') }} rounded bg-white shadow-md absolute z-20 right-0 w-48 mt-5 py-2 animated faster border border-gray-100">
                            <!-- role display -->
                            <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100">
                                <i class="fad fa-user-shield mr-1"></i> {{ auth()->user()->role->name }}
                            </div>

                            <!-- logout -->
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 block capitalize font-medium text-sm tracking-wide bg-white hover:bg-gray-100 hover:text-red-600 transition-all duration-300 ease-in-out">
                                    <i class="fad fa-sign-out-alt text-xs mr-2"></i> log out
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- end user -->

                    <!-- Theme Toggle & Date -->
                    <div class="flex items-center space-x-3 sm:space-x-4 border-r border-gray-200 pr-4 {{ $c('border-r-0') }} {{ $c('pr-0') }} {{ $c('w-full') }} {{ $c('justify-center') }}">
                        <button onclick="toggleTheme()" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 focus:outline-none transition-colors" title="Toggle Night Mode">
                            <!-- Sun Icon -->
                            <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z"/></svg>
                            <!-- Moon Icon -->
                            <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                        <span class="text-xs sm:text-sm text-slate-500 hidden sm:inline {{ $c('hidden') }}">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>
            <!-- end navbar content -->

        </div>
        <!-- end navbar -->

        <!-- start wrapper -->
        <div class="min-h-screen flex flex-row flex-wrap">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')

            <!-- Main Content Area -->
            <div class="bg-gray-100 flex-1 p-6 {{ $c('mt-16') }} min-h-screen">
                
                <!-- Page Title -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        @yield('header-title', 'Front Office Dashboard')
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

                @if(session('info'))
                    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-800 rounded-r-lg flex items-center shadow-sm alert alert-info">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="flex-1">{{ session('info') }}</span>
                        <button class="alert-btn-close ml-4 text-blue-500 hover:text-blue-700 font-bold focus:outline-none">×</button>
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
                        {{ config('app.name', 'Hotel Kejora') }}
                    </a>.
                    All Right Reserved.
                </div>
                <!-- end footer -->
            </div>
            <!-- end content -->
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
