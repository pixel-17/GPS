<x-guest-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .leaflet-container { z-index: 1 !important; }
        
        /* Radar azul para el camión recolector */
        .truck-pulse { position: relative; }
        .truck-pulse::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            border: 2px solid #3b82f6; border-radius: 50%; animation: radar-eco 2s infinite ease-out; opacity: 0;
        }
        @keyframes radar-eco {
            0% { transform: scale(0.2); opacity: 0.8; }
            100% { transform: scale(1.2); opacity: 0; }
        }

        /* Marcador para la casa del vecino */
        .home-pulse {
            width: 16px; height: 16px; background-color: #10b981;
            border: 3px solid #ffffff; border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); animation: pulse-home 1.5s infinite;
        }
        @keyframes pulse-home {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>

    <div class="py-4 min-h-screen text-slate-100 flex items-start justify-center" style="background-color: #0b1329;">
        <div class="w-full max-w-md px-4 space-y-4">
            
            <div class="bg-slate-900/90 backdrop-blur-md p-4 rounded-2xl border border-slate-800 shadow-xl flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-xl shadow-inner border border-indigo-500/20">
                    🚛
                </div>
                <div>
                    <h1 class="text-sm font-black text-white uppercase tracking-wide">EcoRastreo Satelital</h1>
                    <p class="text-[10px] text-slate-400">Busca tu recolector por placa o ubicación</p>
                </div>
            </div>

            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 shadow-xl space-y-4">
                
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-indigo-400 px-1">Opción 1: Buscar por Placa o Barrio</label>
                    <div class="relative">
                        <input type="text" id="input-buscador" placeholder="Ej: ABC-123 o Centro Histórico..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs font-bold text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                        <button id="btn-buscar-manual" class="absolute right-2 top-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black px-3 py-1.5 rounded-lg transition uppercase cursor-pointer">
                            Buscar
                        </button>
                    </div>
                </div>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-slate-800"></div>
                    <span class="flex-shrink mx-4 text-[9px] text-slate-500 font-bold uppercase">O también</span>
                    <div class="flex-grow border-t border-slate-800"></div>
                </div>

                <div id="panel-gps-config" class="space-y-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-emerald-400 px-1 text-center">Opción 2: Autodetección Satelital</label>
                    <button id="btn-detectar-gps" class="w-full cursor-pointer bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                        📍 Ubicar Mi Casa con GPS
                    </button>
                </div>

                <div id="panel-casa-guardada" class="hidden flex items-center justify-between bg-slate-950 p-3 rounded-xl border border-slate-800/60 text-left">
                    <div>
                        <p class="text-[10px] text-emerald-400 font-black uppercase tracking-wider">🏠 Casa Recordada</p>
                        <p class="text-xs font-bold text-white mt-0.5">Cargando sector automático</p>
                    </div>
                    <button id="btn-borrar-casa" class="cursor-pointer bg-slate-900 text-slate-400 hover:text-white px-2.5 py-1.5 rounded-lg text-[9px] font-black border border-slate-700 transition uppercase">
                        Cambiar
                    </button>
                </div>

                <button id="btn-confirmar-pin" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold text-xs uppercase hidden transition shadow-md">
                    💾 Guardar Ubicación de Casa
                </button>
            </div>

            <div class="bg-slate-900 rounded-3xl p-3 border border-slate-800 shadow-xl space-y-3 relative">
                <div id="status-pill" class="absolute top-6 right-6 z-10 bg-slate-950/90 border border-slate-800 px-2.5 py-1 rounded-full text-[9px] font-black tracking-widest text-slate-400 uppercase hidden items-center gap-1.5 backdrop-blur-sm">
                    <span id="status-dot" class="w-2 h-2 rounded-full bg-slate-500"></span>
                    <span id="status-text">Buscando...</span>
                </div>

                <div class="w-full bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 relative" style="height: 380px;">
                    <div id="map-vecino-view" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; height: 100%; width: 100%;"></div>
                    
                    <div id="map-placeholder" class="absolute inset-0 bg-slate-950/95 z-20 flex flex-col items-center justify-center text-center p-6 space-y-3">
                        <span class="text-4xl animate-pulse">📡</span>
                        <div class="space-y-1">
                            <h4 class="font-black text-xs text-white uppercase tracking-wide">Buscador Listo</h4>
                            <p class="text-[11px] text-slate-400 max-w-[220px] mx-auto">Ingresa una placa arriba o activa tu GPS para ver la unidad en tiempo real.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            let mapVecino = null;
            let homeMarker = null;
            let truckMarker = null;
            let routeLayer = null;
            let trackingInterval = null;
            let placaGlobal = "Cargando...";

            const inputBuscador = document.getElementById('input-buscador');
            const btnBuscarManual = document.getElementById('btn-buscar-manual');
            const btnDetectarGps = document.getElementById('btn-detectar-gps');
            const btnConfirmarPin = document.getElementById('btn-confirmar-pin');
            const btnBorrarCasa = document.getElementById('btn-borrar-casa');
            
            const panelGpsConfig = document.getElementById('panel-gps-config');
            const panelCasaGuardada = document.getElementById('panel-casa-guardada');
            const placeholder = document.getElementById('map-placeholder');
            const statusPill = document.getElementById('status-pill');
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            // Inicializar Mapa
            mapVecino = L.map('map-vecino-view', { zoomControl: true, attributionControl: false }).setView([-13.5319, -71.9675], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapVecino);

            // Revisar si hay casa guardada de visitas previas
            revisarMemoriaLocal();

            function revisarMemoriaLocal() {
                const cLat = localStorage.getItem('ecorastreo_casa_lat');
                const cLng = localStorage.getItem('ecorastreo_casa_lng');

                if (cLat && cLng) {
                    panelGpsConfig.classList.add('hidden');
                    panelCasaGuardada.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    statusPill.classList.replace('hidden', 'flex');
                    mapVecino.invalidateSize();

                    const homeIcon = L.divIcon({ className: 'home-pulse', iconSize: [16, 16] });
                    homeMarker = L.marker([cLat, cLng], { icon: homeIcon }).addTo(mapVecino);

                    conectarConCamionPorCoordenadas(cLat, cLng);
                }
            }

            // 🔍 ACCIÓN 1: BÚSQUEDA MANUAL (Escribiendo placa o nombre de ruta)
           btnBuscarManual.addEventListener('click', function() {
    const termino = inputBuscador.value.trim();
    if (!termino) return alert("Por favor escribe una placa o el nombre de una ruta.");

    btnBuscarManual.innerText = "🔍...";
    
    fetch('/api/vecino/buscar-texto', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token de seguridad indispensable
        },
        body: JSON.stringify({ query: termino })
    })
    .then(res => res.json())
    .then(data => {
        btnBuscarManual.innerText = "Buscar";
        if (!data.success) return alert(data.message);

        activarMonitoreoDeCamion(data);
    })
    .catch((error) => {
        btnBuscarManual.innerText = "Buscar";
        console.error("Detalle del error:", error);
        alert("Error al conectar con el servidor. Revisa la consola (F12).");
    });
});
            // 📍 ACCIÓN 2: AGREGAR CASA POR GPS DE FORMA MANUAL
            btnDetectarGps.addEventListener('click', function() {
                if (!navigator.geolocation) return alert("Tu dispositivo no soporta GPS satelital.");
                btnDetectarGps.innerText = "🛰️ Buscando coordenadas...";

                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    placeholder.classList.add('hidden');
                    mapVecino.invalidateSize();
                    mapVecino.setView([lat, lng], 16);

                    const homeIcon = L.divIcon({ className: 'home-pulse', iconSize: [16, 16] });
                    homeMarker = L.marker([lat, lng], { icon: homeIcon, draggable: true }).addTo(mapVecino)
                        .bindPopup("<b class='text-slate-900 text-xs'>Arrastra el punto hasta tu casa</b><br><span class='text-[10px] text-slate-500'>Y presiona el botón verde de arriba.</span>").openPopup();

                    btnDetectarGps.classList.add('hidden');
                    btnConfirmarPin.classList.remove('hidden');

                    homeMarker.on('dragend', function(e) {
                        const pos = e.target.getLatLng();
                        latTmp = pos.lat; lngTmp = pos.lng;
                    });
                    latTmp = lat; lngTmp = lng;

                }, function() {
                    alert("No pudimos obtener tu GPS. Usa el buscador por texto de arriba.");
                    btnDetectarGps.innerText = "📍 Ubicar Mi Casa con GPS";
                });
            });

            let latTmp, lngTmp;
            btnConfirmarPin.addEventListener('click', function() {
                localStorage.setItem('ecorastreo_casa_lat', latTmp);
                localStorage.setItem('ecorastreo_casa_lng', lngTmp);
                btnConfirmarPin.classList.add('hidden');
                btnDetectarGps.classList.remove('hidden');
                btnDetectarGps.innerText = "📍 Ubicar Mi Casa con GPS";
                revisarMemoriaLocal();
            });

            btnBorrarCasa.addEventListener('click', function() {
                localStorage.removeItem('ecorastreo_casa_lat');
                localStorage.removeItem('ecorastreo_casa_lng');
                location.reload();
            });

            // Funciones de conexión API
            function conectarConCamionPorCoordenadas(lat, lng) {
                fetch('/api/vecino/buscar-cercano', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ latitude: lat, longitude: lng })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) activarMonitoreoDeCamion(data);
                });
            }

            function activarMonitoreoDeCamion(data) {
    // 1. Limpiamos pantallas de carga y redimensionamos el mapa
    placeholder.classList.add('hidden');
    statusPill.classList.replace('hidden', 'flex');
    mapVecino.invalidateSize();

    placaGlobal = data.placa;

    // 2. Intentar pintar el GeoJSON de las calles de forma aislada
    if (routeLayer) mapVecino.removeLayer(routeLayer);

    if (data.geojson && data.geojson !== "[]" && data.geojson !== "null" && data.geojson !== "") {
        try {
            // Aseguramos que sea un objeto
            const geojsonData = (typeof data.geojson === 'string') ? JSON.parse(data.geojson) : data.geojson;
            
            // Validamos si cumple el estándar mínimo de Leaflet
            if (geojsonData && typeof geojsonData === 'object') {
                routeLayer = L.geoJSON(geojsonData, { 
                    style: () => ({ color: '#10b981', weight: 4, opacity: 0.4 }) 
                }).addTo(mapVecino);
                console.log("🛣️ Trazado de calles cargado con éxito.");
            }
        } catch (e) {
            // Si el GeoJSON está roto, lo atrapamos aquí para que NO congele el resto del código
            console.warn("⚠️ El GeoJSON tiene un formato incompatible con Leaflet, se omitirá el dibujo de las calles.", e);
        }
    } else {
        console.log("ℹ️ Esta ruta no cuenta con calles GeoJSON asignadas.");
    }

    // 3. 🚀 SECCIÓN CRÍTICA: Renderizar el camión pase lo que pase con las calles
    if (data.latitude && data.longitude) {
        renderizarCamion(parseFloat(data.latitude), parseFloat(data.longitude));
    } else {
        console.error("No se recibieron coordenadas válidas para posicionar el camión.");
    }

    // 4. Bucle de actualización en tiempo real cada 4 segundos
    if (trackingInterval) clearInterval(trackingInterval);
    trackingInterval = setInterval(() => {
        fetch(`/api/rutas/${data.route_id}/ultima-posicion`)
            .then(r => r.json())
            .then(pos => {
                if (pos && pos.latitude && pos.longitude) {
                    statusDot.className = "w-2 h-2 rounded-full bg-emerald-500 animate-ping";
                    statusText.innerText = "En Vivo";
                    renderizarCamion(parseFloat(pos.latitude), parseFloat(pos.longitude));
                } else {
                    statusDot.className = "w-2 h-2 rounded-full bg-rose-500";
                    statusText.innerText = "Desconectado";
                }
            })
            .catch(err => console.log("Error consultando actualizaciones:", err));
    }, 4000);
}
            function renderizarCamion(lat, lng) {
                const htmlIcono = `<div class="relative flex flex-col items-center"><span class="bg-slate-950 text-[9px] text-emerald-400 font-black px-1.5 py-0.5 rounded border border-slate-700 shadow-md whitespace-nowrap mb-1">🆔 ${placaGlobal}</span><div class="truck-pulse text-2xl">%0A🚛</div></div>`;
                const icon = L.divIcon({ html: htmlIcono, iconSize: [60, 40], className: 'custom-truck' });

                if (!truckMarker) {
                    truckMarker = L.marker([lat, lng], { icon: icon }).addTo(mapVecino);
                } else {
                    truckMarker.setLatLng([lat, lng]);
                    truckMarker.setIcon(icon);
                }

                if (homeMarker) {
                    const group = new L.featureGroup([homeMarker, truckMarker]);
                    mapVecino.fitBounds(group.getBounds(), { padding: [60, 60] });
                } else {
                    mapVecino.setView([lat, lng], 15);
                }
            }
        });
    </script>
</x-guest-layout>