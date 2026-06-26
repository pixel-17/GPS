<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800">🗺️ Consola de Monitoreo Flota Global</h1>
                    <p class="text-xs text-gray-500 font-medium mt-1">
                        Visualizando <span class="text-indigo-600 font-bold">{{ $routes->count() }}</span> rutas programadas en el mapa municipal.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <span id="global-status" class="px-3 py-2 text-xs font-bold rounded-xl bg-gray-100 text-gray-600 border border-gray-200">
                        🔴 Radar General Apagado
                    </span>
                    <button type="button" id="btn-global-radar" class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                        📡 Escanear Flota en Vivo
                    </button>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <div id="map-global" style="width: 100%; height: 600px; border-radius: 1rem; border: 1px solid #e2e8f0; z-index: 10;"></div>
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Inicializamos el mapa centrado de forma general
            const map = L.map('map-global').setView([-13.5319, -71.9675], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            const bounds = L.latLngBounds();
            let rutasPintadasCount = 0;

            // 2. 🔀 RECORRER Y DIBUJAR TODAS LAS RUTAS GEOJSON AL MISMO TIEMPO
            @foreach($routes as $r)
                @if($r->geojson)
                    try {
                        const geoData = {!! $r->geojson !!};
                        const routeLayer = L.geoJSON(geoData, {
                            style: function() { 
                                return { 
                                    color: '#4f46e5', // Puedes alternar colores si gustas
                                    weight: 4, 
                                    opacity: 0.5 
                                }; 
                            }
                        }).addTo(map);

                        // Vinculamos un popup a la línea por si el admin hace clic en el camino
                        routeLayer.bindPopup("<b>Ruta: {{ $r->name }}</b><br>Camión: {{ $r->vehicle->plate ?? 'Sin unidad' }}");
                        
                        bounds.extend(routeLayer.getBounds());
                        rutasPintadasCount++;
                    } catch (e) {
                        console.error("Error cargando GeoJSON de ruta ID: {{ $r->id }}");
                    }
                @endif
            @endforeach

            // Si se pintaron rutas, encuadrar el mapa para que entren todas a la vista
            if (rutasPintadasCount > 0) {
                map.fitBounds(bounds);
            }

            // 3. 🚛 CONTROL DEL RADAR MULTI-CAMIONES
            const btnRadar = document.getElementById('btn-global-radar');
            const badge = document.getElementById('global-status');
            
            let radarInterval = null;
            let isRadarActive = false;
            let markersGroup = L.layerGroup().addTo(map); // Grupo para borrar y re-dibujar camiones fácilmente

            if (btnRadar) {
                btnRadar.onclick = function (e) {
                    e.preventDefault();

                    if (!isRadarActive) {
                        isRadarActive = true;
                        btnRadar.innerText = "🛑 Detener Escaneo";
                        btnRadar.setAttribute('class', 'cursor-pointer bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm');
                        badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                        badge.innerText = "🔍 Localizando flota activa...";

                        localizarFlota();
                        radarInterval = setInterval(localizarFlota, 5000); // Consulta global cada 5 segundos
                    } else {
                        isRadarActive = false;
                        clearInterval(radarInterval);
                        radarInterval = null;

                        btnRadar.innerText = "📡 Escanear Flota en Vivo";
                        btnRadar.setAttribute('class', 'cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm');
                        badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-gray-100 text-gray-600 border border-gray-200');
                        badge.innerText = "🔴 Radar General Apagado";

                        markersGroup.clearLayers(); // Limpiamos todos los camiones del mapa al apagar
                    }
                };
            }

            function localizarFlota() {
                if (!isRadarActive) return;

                fetch('/admin/api/all-last-gps')
                    .then(res => res.json())
                    .then(data => {
                        if (!isRadarActive) return;

                        // Limpiamos los marcadores de la vuelta anterior para actualizar posiciones reales
                        markersGroup.clearLayers();

                        if (data.success && data.trucks.length > 0) {
                            data.trucks.forEach(truck => {
                                // Creamos el marcador por cada camión transmitiendo
                                const marker = L.marker([truck.latitude, truck.longitude])
                                    .bindPopup(`
                                        <div class="text-xs">
                                            <p class="font-black text-gray-800 text-sm">🚛 ${truck.routeName}</p>
                                            <p class="mt-1"><b>Placa:</b> ${truck.plate}</p>
                                            <p><b>Chofer:</b> ${truck.driver}</p>
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-800 rounded font-bold text-[10px]">TRANSMITIENDO</span>
                                        </div>
                                    `);
                                
                                markersGroup.addLayer(marker);
                            });

                            badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-green-50 text-green-700 border border-green-200');
                            badge.innerText = `🟢 En Línea: ${data.trucks.length} Unidad(es)`;
                        } else {
                            badge.setAttribute('class', 'px-3 py-2 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 animate-pulse');
                            badge.innerText = "🔍 Escaneando... (Ningún camión transmite)";
                        }
                    })
                    .catch(err => console.error("Error en radar general:", err));
            }
        });
    </script>
</x-app-layout>