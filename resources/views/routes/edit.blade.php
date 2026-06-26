<x-app-layout>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Editar Ruta</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('routes.update', $route) }}">
            @csrf
            @method('PUT')

            <div class="mb-4"a<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">✏️ Editar y Reprogramar Ruta: {{ $route->name }}</h1>

        <form method="POST" action="{{ route('routes.update', $route->id) }}" id="routeForm">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow border border-gray-100 space-y-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Nombre de la Ruta</label>
                        <input type="text" name="name" value="{{ old('name', $route->name) }}" class="w-full px-3 py-2 border rounded-xl" required>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Fecha</label>
                            <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $route->scheduled_date) }}" class="w-full px-3 py-2 border rounded-xl" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Hora Inicio</label>
                            <input type="time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($route->start_time)->format('H:i')) }}" class="w-full px-3 py-2 border rounded-xl" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Vehículo Asignado</label>
                        <select name="vehicle_id" class="w-full px-3 py-2 border rounded-xl" required>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $route->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate }} - {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Chofer Asignado</label>
                        <select name="driver_id" class="w-full px-3 py-2 border rounded-xl" required>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ $route->driver_id == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border rounded-xl" required>
                            <option value="programada" {{ $route->status === 'programada' ? 'selected' : '' }}>Programada</option>
                            <option value="en_progreso" {{ $route->status === 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                            <option value="completada" {{ $route->status === 'completada' ? 'selected' : '' }}>Completada</option>
                            <option value="cancelada" {{ $route->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>

                    <input type="hidden" id="geojson_input" name="geojson" value="{{ old('geojson', $route->geojson) }}">

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow transition">
                        Actualizar Ruta Municipal
                    </button>
                </div>

                <div class="lg:col-span-2 bg-white p-4 rounded-2xl shadow border border-gray-100">
                    <div class="mb-2 flex justify-between items-center">
                        <label class="block text-gray-700 font-bold">🗺️ Modificar Trazado Geográfico:</label>
                        <span id="status-badge" class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded">Ruta Precargada</span>
                    </div>
                    
                    <div id="map-edit" style="width: 100%; height: 530px; min-height: 530px; border-radius: 0.75rem; border: 1px solid #cbd5e1; position: relative; z-index: 10;"></div>
                    
                    <div class="mt-3 text-xs text-gray-600 bg-yellow-50 p-3 rounded-xl border border-yellow-100 space-y-1">
                        <p><strong>🛠️ Opciones de edición:</strong></p>
                        <p>• <strong>Mover o ajustar puntos:</strong> Haz clic en el botón de la barra lateral del mapa que tiene un <strong>Lápiz (Edit Layers)</strong>. Verás que aparecen pequeños cuadrados en la ruta; arrástralos con el ratón para cambiar las esquinas de las calles.</p>
                        <p>• <strong>Borrar todo y rehacer:</strong> Usa el icono del <strong>Bote de basura (Removal Mode)</strong>, haz clic sobre la línea azul para eliminarla, y luego vuelve a seleccionar la herramienta de <strong>Línea</strong> para dibujarla desde cero.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Inicializamos mapa base temporal
            const map = L.map('map-edit').setView([-13.5319, -71.9675], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // 2. Activar los controles de Geoman para editar o borrar
            map.pm.addControls({
                position: 'topleft',
                drawMarker: false,
                drawCircleMarker: false,
                drawPolyline: true,
                drawRectangle: false,
                drawPolygon: false,
                drawCircle: false,
                editMode: true,
                dragMode: false,
                removalMode: true
            });
            map.pm.setLang('es');

            // 3. 🔥 CARGAR LA RUTA EXISTENTE DE LA BASE DE DATOS
            const jsonInicial = document.getElementById('geojson_input').value;

            if (jsonInicial && jsonInicial.trim() !== '') {
                try {
                    const geojsonData = JSON.parse(jsonInicial);
                    
                    // Pintamos la ruta guardada
                    const geojsonLayer = L.geoJSON(geojsonData, {
                        style: function() {
                            return { color: '#2563eb', weight: 5, opacity: 0.85 };
                        }
                    }).addTo(map);

                    // Auto-ajustar la cámara del mapa a la ruta cargada
                    map.fitBounds(geojsonLayer.getBounds());

                    // Hacemos que las líneas viejas también escuchen cambios si el Admin las edita
                    geojsonLayer.eachLayer(function(layer) {
                        layer.on('pm:edit', function() {
                            guardarCoordenadas();
                        });
                    });

                } catch (e) {
                    console.error("Error al leer el GeoJSON original", e);
                }
            }

            // 4. Escuchar si se dibuja una NUEVA línea extra
            map.on('pm:create', function(e) {
                const layer = e.layer;
                guardarCoordenadas();

                document.getElementById('status-badge').className = "bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded";
                document.getElementById('status-badge').innerText = "Ruta Modificada";

                layer.on('pm:edit', function() {
                    guardarCoordenadas();
                });
            });

            // Escuchar si se elimina alguna línea
            map.on('pm:remove', function(e) {
                guardarCoordenadas();
            });

            // 5. Función unificada para guardar el estado del mapa en el formulario
            function guardarCoordenadas() {
                const features = [];
                map.eachLayer(function(layer) {
                    // Recorremos todas las líneas válidas que queden en el mapa
                    if (layer instanceof L.Polyline && layer.pm && !layer._url) {
                        features.push(layer.toGeoJSON());
                    }
                });

                if (features.length > 0) {
                    const geojsonStructure = {
                        type: "FeatureCollection",
                        features: features
                    };
                    document.getElementById('geojson_input').value = JSON.stringify(geojsonStructure);
                    document.getElementById('status-badge').className = "bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded";
                    document.getElementById('status-badge').innerText = "Ruta Modificada";
                } else {
                    document.getElementById('geojson_input').value = '';
                    document.getElementById('status-badge').className = "bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded";
                    document.getElementById('status-badge').innerText = "Sin trazar (Vacío)";
                }
            }

            // Refrescar tamaño por retrasos de layouts
            setTimeout(() => map.invalidateSize(), 250);
        });
    </script>
</x-app-layout>>
                <label class="block text-gray-700 font-bold mb-2">Nombre de la Ruta</label>
                <input type="text" name="name" value="{{ old('name', $route->name) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Descripción</label>
                <textarea name="description" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">{{ old('description', $route->description) }}</textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Fecha Programada</label>
                <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $route->scheduled_date->format('Y-m-d')) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('scheduled_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Hora Inicio</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $route->start_time) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required>
                    @error('start_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Hora Fin Estimada</label>
                    <input type="time" name="estimated_end_time" value="{{ old('estimated_end_time', $route->estimated_end_time) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    @error('estimated_end_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Vehículo</label>
                    <select name="vehicle_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Seleccionar --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $route->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->plate }} - {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Chofer</label>
                    <select name="driver_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Seleccionar --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id', $route->driver_id) == $driver->id ? 'selected' : '' }}>
                                {{ $driver->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="programada" {{ old('status', $route->status) === 'programada' ? 'selected' : '' }}>Programada</option>
                    <option value="en_progreso" {{ old('status', $route->status) === 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="completada" {{ old('status', $route->status) === 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="cancelada" {{ old('status', $route->status) === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Guardar Cambios
                </button>
                <a href="{{ route('routes.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
