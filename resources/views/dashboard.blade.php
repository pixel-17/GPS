<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container { z-index: 1 !important; }
        /* Animación suave para las barras de progreso */
        .progress-bar-shine {
            position: relative;
            overflow: hidden;
        }
        .progress-bar-shine::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shine 2.5s infinite;
        }
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 150%; }
        }
    </style>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-3xl shadow-sm border border-slate-100 gap-4">
                <div>
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full">Consola de Mando</span>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight mt-1">¡Bienvenido al Panel, Admin! 👋</h1>
                    <p class="text-sm text-slate-500 font-medium">Estado operativo del sistema de recojo de residuos municipales.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-100 w-full md:w-auto justify-between md:justify-start">
                    <span id="global-status" class="px-3 py-2 text-xs font-bold rounded-xl bg-slate-100 text-slate-600 border border-slate-200">
                        🔴 Radar Flota Apagado
                    </span>
                    <button type="button" id="btn-global-radar" class="cursor-pointer bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 hover:scale-[1.02]">
                        📡 Activar Radar Vivo
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Flota Vehicular</span>
                        <span class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-lg">🚛</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-black text-slate-800">{{ $totalVehiculos }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1">
                            <span class="text-emerald-600 font-bold">● {{ $vehiculosActivos }}</span> unidades aptas en campo
                        </p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Operadores</span>
                        <span class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-lg">👷‍♂️</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-black text-slate-800">{{ $totalChoferes }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1">Choferes registrados y asignables</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rutas Hoy</span>
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-lg">📍</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-black text-slate-800">{{ $rutasHoy }}</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1">Programadas para la jornada de hoy</p>
                    </div>
                </div>

                @php
                    // Cálculo matemático dinámico del avance del día
                    $porcentajeAvance = $rutasHoy > 0 ? round(($rutasEnProgreso / $rutasHoy) * 100) : 0;
                @endphp
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-3xl shadow-md text-white flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avance del Día</span>
                        <span class="text-xs bg-white/10 text-white px-2 py-0.5 rounded-full font-bold">{{ $porcentajeAvance }}%</span>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between items-baseline">
                            <h3 class="text-2xl font-black">{{ $rutasEnProgreso }} / {{ $rutasHoy }}</h3>
                            <span class="text-[11px] text-slate-400">Rutas en curso</span>
                        </div>
                        <div class="w-full bg-white/10 h-3 rounded-full overflow-hidden progress-bar-shine">
                            <div class="bg-gradient-to-r from-indigo-400 to-emerald-400 h-full rounded-full transition-all duration-1000" style="width: {{ $porcentajeAvance }}%"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 bg-white p-4 rounded-3xl shadow-sm border border-slate-100 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Mapa de Red Integral</h3>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">Líneas de recojo GeoJSON indexadas por color único</p>
                        </div>
                        <span class="text-[10px] bg-slate-100 text-slate-700 px-2 py-1 rounded-md font-bold">Live GPS Tracker</span>
                    </div>
                    
                    <div class="w-full bg-slate-50 rounded-2xl overflow-hidden shadow-inner border border-slate-100" style="height: 480px; position: relative;">
                        <div id="map-dashboard-global" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; height: 100%; width: 100%;"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                            <div>
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Últimas Asignaciones</h3>
                                p class="text-[11px] text-slate-400 font-medium">Control de despacho reciente</p>
                            </div>
                            <a href="{{ route('routes.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Ver todo →</a>
                        </div>

                        <div class="space-y-3 overflow-y-auto max-h-[380px] pr-1">
                            @forelse($ultimasRutas as $index => $ruta)
                                @php $colorIndex = $ruta->id % 8; @endphp
                                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between hover:bg-slate-100/70 transition-all">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2.5 h-10 rounded-full" style="background-color: {{ ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#06b6d4', '#8b5cf6', '#f97316'][$colorIndex] }}"></span>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-700 truncate max-w-[140px]">{{ $ruta->name }}</h4>
                                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">🚛 {{ $ruta->vehicle->plate ?? 'Sin unidad' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-lg font-bold text-[10px] 
                                            {{ $ruta->status === 'en_progreso' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                            {{ $ruta->status === 'completado' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                            {{ $ruta->status === 'pendiente' ? 'bg-slate-200/60 text-slate-600' : '' }}
                                        ">
                                            {{ strtoupper($ruta->status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-slate-400 text-xs font-medium">
                                    No hay rutas registradas para hoy.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 mt-4">
                        <a href="{{ route('routes.create') }}" class="text-center bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2 px-3 rounded-xl text-xs transition">
                            ➕ Nueva Ruta
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-3 rounded-xl text-xs transition">
                            ⚙️ Ver Flota
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const listaColores = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#06b6d4', '#8b5cf6', '#f97316'];

            // Inicializamos el mapa centrado en Cusco
            const map = L.map('map-dashboard-global').setView([-13.5319, -71.9675], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Corrección de redibujado instantáneo de Leaflet
            setTimeout(function() {
                map.invalidateSize();
            }, 300);

            const bounds = L.latLngBounds();
            let rutasPintadasCount = 0;

            // 1. 🔀 Pintar trazados GeoJSON con colores únicos por Ruta
            @if(isset($routes) && $routes->count() > 0)
                @foreach($routes as $r)
                    @if($r->geojson)
                        try {
                            const geoData = {!! $r->geojson !!};
                            const routeLayer = L.geoJSON(geoData, {
                                style: function() { 
                                    return { 
                                        color: listaColores[{{ $r->id % 8 }}], 
                                        weight: 5, 
                                        opacity: 0.65 
                                    }; 
                                }
                            }).addTo(map);

                            routeLayer.bindPopup("<b>Ruta: {{ $r->name }}</b><br>Unidad: {{ $r->vehicle->plate ?? 'Sin unidad' }}");
                            bounds.extend(routeLayer.getBounds());
                            rutasPintadasCount++;
                        } catch (e) {
                            console.error("Error procesando geometría de ruta ID: {{ $r->id }}");
                        }
                    @endif
                @endforeach
            @endif

            if (rutasPintadasCount > 0) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }

            // 2. 📡 Control del Radar Dinámico sincronizado por Color
            const btnRadar = document.getElementById('btn-global-radar');
            const badge = document.getElementById('global-status');
            
            let radarInterval = null;
            let isRadarActive = false;
            let markersGroup = L.layerGroup().addTo(map);

            if (btnRadar) {
                btnRadar.onclick = function (e) {
                    e.preventDefault();

                    if (!isRadarActive) {
                        isRadarActive = true;
                        btnRadar.innerText = "🛑 Apagar Radar";
                        btnRadar.setAttribute('class', 'cursor-pointer bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm');
                        badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                        badge.innerText = "🔍 Buscando unidades...";

                        localizarFlota();
                        radarInterval = setInterval(localizarFlota, 5000);
                    } else {
                        isRadarActive = false;
                        clearInterval(radarInterval);
                        radarInterval = null;

                        btnRadar.innerText = "📡 Activar Radar Vivo";
                        btnRadar.setAttribute('class', 'cursor-pointer bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm');
                        badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-slate-100 text-slate-600 border border-slate-200');
                        badge.innerText = "🔴 Radar Flota Apagado";

                        markersGroup.clearLayers();
                    }
                };
            }

            function localizarFlota() {
                if (!isRadarActive) return;

                fetch('/admin/api/all-last-gps')
                    .then(res => res.json())
                    .then(data => {
                        if (!isRadarActive) return;

                        markersGroup.clearLayers(); 

                        if (data.success && data.trucks.length > 0) {
                            data.trucks.forEach(truck => {
                                const marker = L.circleMarker([truck.latitude, truck.longitude], {
                                    radius: 9,              
                                    fillColor: truck.color, 
                                    color: '#ffffff',       
                                    weight: 2,
                                    opacity: 1,
                                    fillOpacity: 0.9        
                                }).bindPopup(`
                                    <div class="text-xs space-y-1">
                                        <p class="font-black text-slate-800 text-sm" style="color: ${truck.color}">🚛 ${truck.routeName}</p>
                                        <p><b>Placa:</b> ${truck.plate}</p>
                                        <p><b>Operador:</b> ${truck.driver}</p>
                                        <span class="inline-block px-2 py-0.5 text-white rounded font-bold text-[10px]" style="background-color: ${truck.color}">EN RUTA</span>
                                    </div>
                                `);
                                
                                markersGroup.addLayer(marker);
                            });

                            badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200');
                            badge.innerText = `🟢 Activos: ${data.trucks.length} Camión(es)`;
                        } else {
                            badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                            badge.innerText = "🔍 Escaneando... (Flota fuera de línea)";
                        }
                    })
                    .catch(err => console.error("Error en enlace satelital:", err));
            }
        });
    </script>
</x-app-layout>