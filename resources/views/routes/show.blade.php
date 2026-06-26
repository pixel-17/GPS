<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">📋 Detalle de Ruta: {{ $route->name }}</h1>
            <a href="{{ route('routes.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-xl text-sm font-bold">
                Volver al listado
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow border border-gray-100 space-y-4">
                <h3 class="text-lg font-bold text-gray-700 border-b pb-2">📋 Datos Generales</h3>
                <div>
                    <p class="text-sm text-gray-500">Fecha Programada</p>
                    <p class="font-semibold text-gray-800">{{ $route->scheduled_date }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Horario de Ejecución</p>
                    <p class="font-semibold text-gray-800">⏱️ {{ $route->start_time }} hrs</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-bold">Camión Asignado</p>
                    <p class="text-gray-800">🚛 {{ $route->vehicle ? $route->vehicle->plate . ' - ' . $route->vehicle->model : 'No asignado' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-bold">Operador / Chofer</p>
                    <p class="text-gray-800">👤 {{ $route->driver && $route->driver->user ? $route->driver->user->name : 'No asignado' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Estado actual</p>
                    <span class="px-3 py-1 inline-block text-xs font-bold rounded-full mt-1
                        {{ $route->status === 'programada' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $route->status === 'en_progreso' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $route->status === 'completada' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $route->status === 'cancelada' ? 'bg-red-100 text-red-800' : '' }}
                    ">
                        {{ strtoupper($route->status) }}
                    </span>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white p-4 rounded-2xl shadow border border-gray-100">
                <label class="block text-gray-700 font-bold mb-2">🗺️ Recorrido Geográfico Establecido:</label>
                
                <div id="map-show" style="width: 100%; height: 480px; min-height: 480px; border-radius: 0.75rem; border: 1px solid #cbd5e1;"></div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Inicializamos un mapa base (temporalmente centrado)
            const map = L.map('map-show').setView([-13.5319, -71.9675], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // 2. Extraemos de forma segura el texto GeoJSON que Laravel mandó desde la Base de Datos
            const geojsonData = {!! $route->geojson ?? 'null' !!};

            if (geojsonData) {
                // 3. Pintamos la ruta en el mapa con un diseño azul municipal estilizado
                const geojsonLayer = L.geoJSON(geojsonData, {
                    style: function (feature) {
                        return {
                            color: '#2563eb', // Azul intenso
                            weight: 5,        // Grosor de la línea
                            opacity: 0.85
                        };
                    }
                }).addTo(map);

                // 4. 🔥 TRUCO DE ORO: Hace que el mapa se mueva y haga "zoom" automático 
                // para encuadrar perfectamente la ruta dibujada, sin importar en qué calle esté.
                map.fitBounds(geojsonLayer.getBounds());
            } else {
                alert("Esta ruta se creó sin un trazado en el mapa.");
            }
        });
    </script>
</x-app-layout>