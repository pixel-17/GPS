<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Ciudadano - Gestión de Residuos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-50 text-gray-800 font-['Figtree']">

    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🌱</span>
                    <span class="font-black text-xl tracking-tight text-gray-900">Eco<span class="text-green-600">Municipio</span></span>
                </div>

                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition">
                                Ir a mi Panel ➔
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-gray-900 px-3 py-2 transition">
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>

            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
        
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="bg-green-50 text-green-700 text-xs font-black px-3 py-1.5 rounded-full uppercase tracking-wider">Servicio al Ciudadano</span>
            <h1 class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight leading-none">
                Por un municipio más limpio, <br><span class="text-green-600">sostenible y ordenado</span>
            </h1>
            <p class="text-lg text-gray-500 font-medium">
                Consulta los horarios de recojo de basura, el estado de las rutas de los camiones recolectores en tiempo real y reporta incidencias en tu zona.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                <div class="text-3xl">⏱️</div>
                <h3 class="text-lg font-bold text-gray-800">Horarios de Recojo</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">
                    Conoce los días y horas exactas en los que el camión compactador pasará por tu urbanización o sector.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                <div class="text-3xl">🚛</div>
                <h3 class="text-lg font-bold text-gray-800">Camiones en Vivo</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">
                    ¿Viene el camión? Muy pronto podrás visualizar el mapa con el recorrido satelital de las unidades activas.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                <div class="text-3xl">📢</div>
                <h3 class="text-lg font-bold text-gray-800">Reportar Problemas</h3>
                <p class="text-sm text-gray-500 font-medium leading-relaxed">
                    Envía alertas sobre puntos críticos de basura acumulada o contenedores llenos para su atención inmediata.
                </p>
            </div>

        </div>

    </main>

    <footer class="bg-white border-t border-gray-100 mt-20 py-6 text-center text-xs text-gray-400 font-semibold">
        &copy; {{ date('Y') }} EcoMunicipio - Todos los derechos reservados para los ciudadanos.
    </footer>

</body>
</html>