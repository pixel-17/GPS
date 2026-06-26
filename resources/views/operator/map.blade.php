<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-md mx-auto px-4 py-6 space-y-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('operator.dashboard') }}" class="text-gray-500 text-xl">⬅️</a>
            <h1 class="text-xl font-bold text-gray-800">Mapa de Recojo</h1>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div id="map-operator" style="width: 100%; height: 380px; min-height: 380px; border-radius: 1rem; border: 1px solid #e2e8f0; position: relative; z-index: 10;"></div>

            @if($ruta->status === 'programada')
                <form method="POST" action="{{ route('operator.start-route', $ruta->id) }}">
                    @csrf
                    <button type="submit" class="w-full bg-green-500 text-white font-black py-4 rounded-xl shadow-md text-center text-base">
                        ▶️ Iniciar Recorrido Ahora
                    </button>
                </form>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-center text-sm font-bold">
                    ⚡ Ruta en ejecución. Transmitiendo señal GPS...
                </div>
            @endif
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('map-operator').setView([-13.5319, -71.9675], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            const geojsonData = {!! $ruta->geojson ?? 'null' !!};
            if (geojsonData) {
                const layer = L.geoJSON(geojsonData, {
                    style: function() { return { color: '#2563eb', weight: 6, opacity: 0.85 }; }
                }).addTo(map);
                map.fitBounds(layer.getBounds());
            }
            setTimeout(() => map.invalidateSize(), 250);
        });
    </script>
</x-app-layout>