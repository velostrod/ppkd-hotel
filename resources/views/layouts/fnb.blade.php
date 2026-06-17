<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Food & Beverage - {{ config('app.name', 'Hotel Kejora') }}</title>

        <!-- Theme Initialization Script to Prevent Ficker -->
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
        
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>
    <body class="bg-[#f8fafc] text-slate-800 antialiased transition-colors duration-250">
        <!-- Sidebar Backdrop (Mobile only) -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>

        <div class="flex min-h-screen">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar-fnb')

            <!-- Main Content Area -->
            <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
                <!-- Header -->
                <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10 shadow-sm">
                    <div class="flex items-center">
                        <!-- Hamburger Toggle -->
                        <button onclick="toggleSidebar()" class="p-2 text-slate-500 hover:text-slate-700 lg:hidden rounded-lg hover:bg-slate-100 mr-2 focus:outline-none dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800">
                            @yield('header-title', 'F&B Kitchen Dashboard')
                        </h2>
                    </div>
                    
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <!-- Night Mode Toggle -->
                        <button onclick="toggleTheme()" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 focus:outline-none dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors" title="Toggle Night Mode">
                            <!-- Sun Icon -->
                            <svg id="theme-icon-sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z"/></svg>
                            <!-- Moon Icon -->
                            <svg id="theme-icon-moon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                        <span class="text-xs sm:text-sm text-slate-500 hidden sm:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-8">
                    <!-- Notifications -->
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg flex items-center shadow-sm">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg flex items-center shadow-sm">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        <!-- Theme and Drawer Toggles Script -->
        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar-nav');
                const backdrop = document.getElementById('sidebar-backdrop');
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }

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
