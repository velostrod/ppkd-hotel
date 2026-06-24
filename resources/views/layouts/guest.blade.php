<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login - {{ config('app.name', 'PPKD Hotel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Outfit', sans-serif; }
        </style>
    </head>
    <body class="bg-slate-950 min-h-screen flex antialiased text-slate-200">

        <!-- Left Panel: Hotel Image -->
        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden">
            <img src="/template/image/kejora-hotel.jpg" alt="PPKD Hotel" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-slate-950/40 to-amber-900/30"></div>

            <div class="relative z-10 flex flex-col justify-between p-14 w-full">
                <!-- Top logo -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center p-1 shadow-lg">
                        <img src="/template/image/PPKDJP.png" alt="PPKD Jakarta Pusat" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-widest text-amber-400">PPKD</span>
                        <span class="text-slate-400 text-xs ml-2 uppercase tracking-widest">Hotel & Resort</span>
                    </div>
                </div>

                <!-- Center tagline -->
                <div>
                    <h1 class="text-5xl font-light text-white leading-tight mb-4">
                        Kelola Hotel<br>
                        <span class="font-bold text-amber-400">dengan Lebih Mudah</span>
                    </h1>
                    <p class="text-slate-300 text-base leading-relaxed max-w-sm">
                        Platform manajemen terpadu untuk reservasi, kamar, housekeeping, dan layanan tamu PPKD Hotel.
                    </p>
                </div>

                <!-- Bottom -->
                <p class="text-slate-600 text-xs">© {{ date('Y') }} PPKD Hotel. All rights reserved.</p>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-slate-950">
            <div class="w-full max-w-sm">

                <!-- Mobile logo -->
                <div class="lg:hidden text-center mb-10">
                    <div class="inline-flex items-center gap-2 mb-1">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center p-1 shadow-lg">
                            <img src="/template/image/PPKDJP.png" alt="PPKD Jakarta Pusat" class="w-full h-full object-contain">
                        </div>
                        <span class="text-2xl font-bold tracking-widest text-amber-400">PPKD</span>
                    </div>
                    <p class="text-xs text-slate-500 uppercase tracking-widest">Hotel Management System</p>
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-white">Selamat Datang</h2>
                    <p class="text-slate-400 text-sm mt-1">Masuk untuk mengakses sistem manajemen</p>
                </div>

                <!-- Slot -->
                {{ $slot }}

                <p class="text-center text-slate-700 text-xs mt-10">PPKD Hotel Management System &middot; v1.0</p>
            </div>
        </div>

    </body>
</html>
