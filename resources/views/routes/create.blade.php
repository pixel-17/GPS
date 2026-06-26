<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">📌 Programar y Trazar Nueva Ruta</h1>

        <form method="POST" action="{{ route('routes.store') }}" id="routeForm">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow border border-gray-100 space-y-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Nombre de la Ruta</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border rounded-xl focus:ring focus:ring-blue-200" placeholder="Ej. Sector 4 - Recojo Matutino" required>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Fecha</label>
                            <input type="date" name="scheduled_date" class="w-full px-3 py-2 border rounded-xl" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Hora Inicio</label>
                            <input type="time" name="start_time" class="w-full px-3 py-2 border rounded-xl" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Vehículo Asignado</label>
                        <select name="vehicle_id" class="w-full px-3 py-2 border rounded-xl" required>
                            <option value="">Seleccione camión...</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} - {{ $vehicle->model }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Chofer Asignado</label>
                        <select name="driver_id" class="w-full px-3 py-2 border rounded-xl" required>
                            <option value="">Seleccione operador...</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border rounded-xl" required>
                            <option value="programada">Programada</option>
                            <option value="en_progreso">En Progreso</option>
                        </select>
                    </div>

                    <input type="hidden" id="geojson_input" name="geojson">

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow transition">
                        Guardar Ruta Municipal
                    </button>
                </div>

                <div class="lg:col-span-2 bg-white p-4 rounded-2xl shadow border border-gray-100">
                    <div class="mb-2 flex justify-between items-center">
                        <label class="block text-gray-700 font-bold">🗺️ Trazado Geográfico en el Mapa:</label>
                        <span id="status-badge" class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded">Sin trazar</span>
                    </div>
                    
                    <div id="map" style="width: 100%; height: 500px; min-height: 500px; background-color: #eee; border: 2px solid #cbd5e1; border-radius: 0.75rem; position: relative; z-index: 10;"></div>
                    
                    <div class="mt-3 text-sm text-gray-600 bg-blue-50 p-3 rounded-xl border border-blue-100">
                        <strong>¿Cómo dibujar?</strong> Haz clic en el icono de la <strong>Línea (Draw Polyline)</strong> en la barra flotante del mapa. Luego ve haciendo clics en las calles del mapa para armar el camino del camión. Al terminar el camino, haz clic en el último punto otra vez para cerrar el trazo.
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Inicializar mapa (Centrado por defecto en Cusco, Perú. Cambia las coordenadas si tu municipio es otro)
            const map = L.map('map').setView([-13.5319, -71.9675], 14);

            // 2. Cargar diseño de calles (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // 3. Forzar barras de herramientas de dibujo visibles
            map.pm.addControls({
                position: 'topleft',
                drawMarker: false,
                drawCircleMarker: false,
                drawPolyline: true, // ESTA ES LA HERRAMIENTA CLAVE PARA LAS CALLES
                drawRectangle: false,
                drawPolygon: false,
                drawCircle: false,
                editMode: true,
                dragMode: true,
                removalMode: true
            });

            map.pm.setLang('es');

            // 4. Capturar el dibujo cuando el Admin hace clics
            map.on('pm:create', function(e) {
                const layer = e.layer;
                guardarCoordenadas();

                // Cambiar el aviso a verde si ya dibujó algo
                document.getElementById('status-badge').className = "bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded";
                document.getElementById('status-badge').innerText = "Ruta Trazada";

                // Si arrastra o edita los puntos del camino, recalculamos
                layer.on('pm:edit', function() {
                    guardarCoordenadas();
                });
            });

            // Si borra la línea
            map.on('pm:remove', function(e) {
                document.getElementById('geojson_input').value = '';
                document.getElementById('status-badge').className = "bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded";
                document.getElementById('status-badge').innerText = "Sin trazar";
            });

            // 5. Convertir la línea visual a datos de Texto para tu base de datos
            function guardarCoordenadas() {
                const features = [];
                map.eachLayer(function(layer) {
                    // Verificamos que sea una línea dibujada por el admin
                    if (layer instanceof L.Polyline && layer.pm && !layer._url) {
                        features.push(layer.toGeoJSON());
                    }
                });

                if (features.length > 0) {
                    const geojsonStructure = {
                        type: "FeatureCollection",
                        features: features
                    };
                    // Inyectamos el texto JSON en el input oculto del formulario
                    document.getElementById('geojson_input').value = JSON.stringify(geojsonStructure);
                }
            }
        });
    </script>
</x-app-layout>