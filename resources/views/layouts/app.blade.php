<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EcoRastreo') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased h-full bg-slate-100 text-slate-900 select-none">
        <x-banner />

        <div class="min-h-screen flex flex-col max-w-md mx-auto bg-white shadow-2xl relative border-x border-slate-200">
            
            @auth
                <div class="sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm">
                    @livewire('navigation-menu')
                </div>
            @endauth

            @guest
                <div class="sticky top-0 z-50 bg-gradient-to-r from-indigo-600 to-purple-600 p-4 shadow-md text-white flex items-center justify-between">
                    <span class="font-black text-sm tracking-wide flex items-center gap-2">
                        <span>🚛</span> EcoRastreo Municipal
                    </span>
                    <a href="{{ route('login') }}" class="text-[11px] bg-white/20 hover:bg-white/30 transition backdrop-blur px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                        Ingresar
                    </a>
                </div>
            @endguest

            @if (isset($header))
                <header class="bg-white px-4 py-3 border-b border-slate-100 shadow-sm">
                    <div class="text-xs font-black text-slate-800 uppercase tracking-widest">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1 overflow-y-auto px-4 py-4 pb-28 bg-gradient-to-tr from-slate-50 to-indigo-50/20">
                {{ $slot }}
            </main>

            <div class="absolute bottom-0 inset-x-0 bg-white/90 backdrop-blur-md border-t border-slate-200 grid grid-cols-3 gap-1 p-2 z-40 shadow-inner">
                
                <a href="{{ route('vecino.index') }}" 
                   class="py-2 px-1 text-[11px] font-bold rounded-xl transition flex flex-col items-center justify-center gap-0.5 {{ Route::is('vecino.index') || Route::is('vecinos.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <span class="text-lg">📋</span>
                    <span>Rutas</span>
                </a>

                <a href="{{ route('vecino.monitoreo') }}" 
                   class="py-2 px-1 text-[11px] font-bold rounded-xl transition flex flex-col items-center justify-center gap-0.5 {{ Route::is('vecino.monitoreo') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <span class="text-lg">🗺️</span>
                    <span>Ver Mapa</span>
                </a>

                <a href="{{ route('vecino.horarios') }}" 
                   class="py-2 px-1 text-[11px] font-bold rounded-xl transition flex flex-col items-center justify-center gap-0.5 {{ Route::is('vecino.horarios') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    <span class="text-lg">⏰</span>
                    <span>Horarios</span>
                </a>

            </div>

        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>