<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-black text-gray-800">📡 Centro de Monitoreo Satelital</h1>
                    <p class="text-xs text-gray-500 font-medium mt-1">
                        Seguimiento en vivo de la ruta: <span class="text-indigo-600 font-bold">{{ $route->name }}</span>
                    </p>
                </div>
                <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Volver al Panel
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-4 h-fit">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b pb-2">Detalles del Servicio</h3>
                    
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase">Compactador</p>
                        <p class="text-sm font-bold text-gray-800">{{ $route->vehicle->plate ?? 'Sin Placa' }}</p>
                        <p class="text-xs text-gray-500">{{ $route->vehicle->model ?? 'Modelo no registrado' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase">Operador</p>
                        <p class="text-sm font-bold text-gray-800">{{ $route->driver->user->name ?? 'No asignado' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase">Horario Estimado</p>
                        <p class="text-sm font-bold text-gray-800">Salida: {{ $route->start_time }} hrs</p>
                    </div>

                    <div class="pt-2 border-t space-y-2">
                        <span id="gps-status" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold rounded-xl bg-gray-100 text-gray-600 border border-gray-200">
                            🔴 Radar Desconectado (0% Recursos)
                        </span>
                        
                        <button type="button" id="btn-radar" class="w-full cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1">
                            📡 Conectar Radar en Vivo
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-3 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div id="map-monitor" style="width: 100%; height: 550px; min-height: 550px; border-radius: 1rem; border: 1px solid #e2e8f0; position: relative; z-index: 10;"></div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar mapa centrado por defecto
            const map = L.map('map-monitor').setView([-13.5319, -71.9675], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            // Cargar y dibujar el GeoJSON real de tu base de datos
            const geojsonData = {!! $route->geojson ?? 'null' !!};
            if (geojsonData) {
                const routeLayer = L.geoJSON(geojsonData, {
                    style: function() { return { color: '#4f46e5', weight: 6, opacity: 0.7 }; }
                }).addTo(map);
                
                map.fitBounds(routeLayer.getBounds());
            }

            // CONTROLES DEL RADAR DE ALTO RENDIMIENTO
            const routeId = {{ $route->id }};
            const btnRadar = document.getElementById('btn-radar');
            const badge = document.getElementById('gps-status');
            
            let radarInterval = null; 
            let isRadarActive = false;
            let truckMarker = null;

            if (btnRadar) {
                btnRadar.onclick = function (e) {
                    e.preventDefault();

                    if (!isRadarActive) {
                        // 🟢 ENCIENDE EL RADAR: Comienza el consumo de red bajo demanda
                        isRadarActive = true;
                        btnRadar.innerText = "🛑 Detener Radar";
                        btnRadar.setAttribute('class', 'w-full cursor-pointer bg-red-600 hover:bg-red-700 text-white text-center py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1');
                        badge.setAttribute('class', 'w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                        badge.innerText = "🔍 Buscando Camión...";

                        rastrearCamion(); // Ejecución inicial inmediata
                        radarInterval = setInterval(rastrearCamion, 4000); // Rastreo rápido en vivo cada 4 segundos
                    } else {
                        // 🛑 DESTRUYE EL BUCLE POR COMPLETO: Consumo baja instantáneamente a cero
                        apagarRadar();
                    }
                };
            }

            function apagarRadar() {
                isRadarActive = false;
                if (radarInterval) {
                    clearInterval(radarInterval);
                    radarInterval = null;
                }

                btnRadar.innerText = "📡 Conectar Radar en Vivo";
                btnRadar.setAttribute('class', 'w-full cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1');
                badge.setAttribute('class', 'w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold rounded-xl bg-gray-100 text-gray-600 border border-gray-200');
                badge.innerText = "🔴 Radar Desconectado (0% Recursos)";

                // Borramos el camión si deja de transmitir para limpiar el mapa
                if (truckMarker) {
                    map.removeLayer(truckMarker);
                    truckMarker = null;
                }
            }

            function rastrearCamion() {
                if (!isRadarActive) return;

                fetch(`/admin/routes/${routeId}/last-gps`)
                    .then(response => response.json())
                    .then(data => {
                        if (!isRadarActive) return;

                        if (data.success && data.latitude && data.longitude) {
                            const lat = parseFloat(data.latitude);
                            const lng = parseFloat(data.longitude);

                            if (truckMarker) {
                                truckMarker.setLatLng([lat, lng]);
                            } else {
                                truckMarker = L.marker([lat, lng]).addTo(map)
                                    .bindPopup("<b>静 Ubicación Actual del Camión</b>")
                                    .openPopup();
                            }

                            badge.setAttribute('class', 'w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold rounded-xl bg-green-50 text-green-700 border border-green-200');
                            badge.innerText = "🟢 Transmitiendo En Vivo";
                        } else {
                            // Si el servidor responde que ya no hay señal activa, removemos el camión del mapa
                            if (truckMarker) {
                                map.removeLayer(truckMarker);
                                truckMarker = null;
                            }
                            badge.setAttribute('class', 'w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                            badge.innerText = "🔍 Buscando... (Sin señal)";
                        }
                    })
                    .catch(error => {
                        console.error("Error obteniendo el GPS:", error);
                        apagarRadar(); // Apaga el flujo si hay un error de red crítico
                    });
            }
        });
    </script>
</x-app-layout>