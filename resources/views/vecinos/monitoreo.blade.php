<x-guest-layout>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    /* Evita conflictos de capas con menús globales */
    .leaflet-container { z-index: 1 !important; }

    /* 🚚 LIMPIEZA TOTAL DEL MARCADOR */
    .custom-clean-truck-icon {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        transition: transform 1.2s cubic-bezier(0.25, 1, 0.5, 1) !important;
    }

    /* Etiqueta flotante del Camión Recolector */
    .truck-tag-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .truck-tag-badge {
        background: #ffffff;
        border: 2px solid #4f46e5;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        padding: 4px 10px;
        border-radius: 20px;
        font-family: ui-sans-serif, system-ui, sans-serif;
        font-size: 11px;
        color: #1e1b4b;
        white-space: nowrap;
        margin-bottom: 2px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
    }
    .truck-emoji {
        font-size: 32px;
        filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3));
        animation: bounce-truck 0.8s ease infinite alternate;
    }
    @keyframes bounce-truck {
        0% { transform: translateY(0); }
        100% { transform: translateY(-5px); }
    }

    /* Pin de la Casa del Vecino (Onda de Radar en Azul Eléctrico) */
    .home-pulse {
        width: 16px; 
        height: 16px;
        background: #2563eb;
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
        animation: pulse-home 1.8s infinite;
    }
    @keyframes pulse-home {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
        70% { box-shadow: 0 0 0 14px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
</style>

<div class="py-6 min-h-screen text-slate-800 bg-gradient-to-tr from-slate-50 to-indigo-50/40">
    <div class="w-full max-w-md mx-auto px-4 space-y-4">

        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-4 rounded-2xl shadow-xl shadow-indigo-600/10 flex items-center justify-between text-white">
            <span class="font-extrabold text-base tracking-wide flex items-center gap-2">
                <span>🚛</span> EcoRastreo Vecino
            </span>
            <div id="status-indicador" class="hidden items-center gap-1.5 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-white">
                <span id="status-dot" class="w-2 h-2 rounded-full bg-green-400"></span>
                <span id="status-text" class="text-[10px] uppercase tracking-wider font-black">Localizando...</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 space-y-4">
            <div>
                <label class="block text-xs font-bold text-indigo-950 uppercase tracking-wider mb-1">Buscar Unidad de Recolección</label>
                <input id="input-buscador"
                       class="w-full p-3 bg-slate-50 text-slate-900 text-sm font-medium rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                       placeholder="Ingresa placa o nombre de ruta...">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button id="btn-buscar-manual"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl text-sm font-bold transition shadow-lg shadow-indigo-600/30">
                    Buscar Camión
                </button>

                <button id="btn-detectar-gps"
                        class="w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 p-3 rounded-xl text-sm font-bold transition border border-emerald-200/60 flex items-center justify-center gap-1">
                    📍 Ubicar Mi Casa
                </button>
            </div>

            <button id="btn-confirmar-pin"
                    class="hidden w-full bg-emerald-600 hover:bg-emerald-700 text-white p-3 rounded-xl text-sm font-bold transition shadow-lg shadow-emerald-600/20">
                💾 Confirmar y Guardar Ubicación
            </button>

            <div class="pt-1 flex justify-center">
                <button id="btn-borrar-casa"
                        class="text-xs font-semibold text-slate-400 hover:text-rose-600 transition">
                    Borrar Registro de Casa
                </button>
            </div>
        </div>

        <div class="relative bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xl shadow-slate-200/60"
             style="height:450px;">

            <div id="map-vecino-view" style="width:100%;height:100%; background: #e5e7eb;"></div>

            <div id="map-placeholder"
                 class="absolute inset-0 bg-slate-50/95 backdrop-blur-sm flex flex-col items-center justify-center text-sm text-slate-600 p-6 text-center space-y-2 pointer-events-none transition-opacity duration-300">
                <span class="text-3xl animate-bounce">🗺️</span>
                <p id="placeholder-text" class="font-medium text-slate-700">Ingresa una placa o usa el GPS para activar el mapa urbano.</p>
            </div>
        </div>

    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let map = L.map('map-vecino-view', { zoomControl: false }).setView([-13.53, -71.97], 13);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let truckMarker = null;
    let homeMarker = null;
    let routeLayer = null;
    let interval = null;

    let placaGlobal = "S/N";

    function calcularTiempoRelativo(dateString) {
        if (!dateString) return { texto: "En vivo", segundos: 0 };
        const ahora = new Date();
        const fechaGps = new Date(dateString);
        const difSegundos = Math.floor((ahora - fechaGps) / 1000);

        if (difSegundos < 15 || isNaN(difSegundos)) return { texto: "En vivo", segundos: difSegundos };
        if (difSegundos < 60) return { texto: `Hace ${difSegundos}s`, segundos: difSegundos };
        const difMinutos = Math.floor(difSegundos / 60);
        if (difMinutos < 60) return { texto: `Hace ${difMinutos}min`, segundos: difSegundos };
        const difHoras = Math.floor(difMinutos / 60);
        return { texto: `Hace ${difHoras}h`, segundos: difSegundos };
    }

    function loadHome() {
        const lat = localStorage.getItem("home_lat");
        const lng = localStorage.getItem("home_lng");

        if (lat && lng) {
            const icon = L.divIcon({ 
                className: "home-pulse",
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            if (homeMarker) map.removeLayer(homeMarker);
            homeMarker = L.marker([parseFloat(lat), parseFloat(lng)], { icon }).addTo(map);
            map.setView([parseFloat(lat), parseFloat(lng)], 15);
            
            const placeholderText = document.getElementById('placeholder-text');
            if (placeholderText) {
                placeholderText.innerText = "Casa detectada. Busca la unidad de recolección asignada a tu barrio.";
            }
        }
    }
    loadHome();

    document.getElementById('btn-buscar-manual').onclick = () => {
        const q = document.getElementById('input-buscador').value.trim();
        if (!q) {
            alert("Por favor, ingresa un término o código de placa válido.");
            return;
        }

        const placeholderText = document.getElementById('placeholder-text');
        if (placeholderText) {
            placeholderText.innerText = "Conectando con los servidores satelitales...";
        }
        const placeholder = document.getElementById('map-placeholder');
        if (placeholder) {
            placeholder.style.display = "flex";
            placeholder.style.opacity = "1";
        }

        fetch('/api/vecino/buscar-texto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: q })
        })
        .then(r => r.json())
        .then(data => {
            if (data) {
                startTracking(data);
            } else {
                alert("No se obtuvieron datos válidos del servidor.");
            }
        })
        .catch(err => {
            console.error(err);
            if (placeholderText) placeholderText.innerText = "Error de conexión de red.";
        });
    };

    function startTracking(data) {
        const placeholder = document.getElementById('map-placeholder');
        if (placeholder) {
            placeholder.style.opacity = "0";
            setTimeout(() => {
                if (placeholder.style.opacity === "0") placeholder.style.display = "none";
            }, 300);
        }

        document.getElementById('status-indicador').classList.replace('hidden', 'flex');
        placaGlobal = data.placa;

        if (routeLayer) map.removeLayer(routeLayer);

        if (data.geojson) {
            try {
                let geojsonData = data.geojson;
                while (typeof geojsonData === 'string') {
                    geojsonData = JSON.parse(geojsonData);
                }
                
                routeLayer = L.geoJSON(geojsonData, {
                    style: () => ({ color: "#ff3b30", weight: 7, opacity: 0.9, lineJoin: "round" })
                }).addTo(map);

                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
            } catch (e) {
                console.error("❌ Falló el GeoJSON:", e);
            }
        }

        updateTruck(data.latitude, data.longitude, data);
        if (!data.geojson) {
            map.setView([parseFloat(data.latitude), parseFloat(data.longitude)], 16);
        }

        if (interval) clearInterval(interval);

        interval = setInterval(() => {
            fetch(`/api/rutas/${data.route_id}/ultima-posicion`)
            .then(r => r.json())
            .then(pos => {
                const targetData = (pos && pos.latitude) ? pos : data;
                const tiempoObj = calcularTiempoRelativo(targetData.recorded_at);

                if (pos && pos.latitude && tiempoObj.segundos < 75) {
                    document.getElementById('status-dot').className = "w-2 h-2 rounded-full bg-green-500 animate-ping";
                    document.getElementById('status-text').innerText = "En Vivo";

                    updateTruck(pos.latitude, pos.longitude, {
                        route_id: data.route_id,
                        placa: data.placa || placaGlobal,
                        recorded_at: pos.recorded_at
                    });
                } else {
                    document.getElementById('status-dot').className = "w-2 h-2 rounded-full bg-rose-500";
                    document.getElementById('status-text').innerText = "Señal Perdida";
                    
                    if(pos && pos.latitude) {
                        updateTruck(pos.latitude, pos.longitude, {
                            route_id: data.route_id,
                            placa: data.placa || placaGlobal,
                            recorded_at: pos.recorded_at
                        });
                    }
                }
            })
            .catch(err => console.warn("Error de ráfagas GPS:", err));
        }, 4000);
    }

    function updateTruck(lat, lng, data) {
        if (!lat || !lng) return;
        const tiempoObj = calcularTiempoRelativo(data.recorded_at);

        const iconhtml = `
            <div class="truck-tag-container">
                <div class="truck-tag-badge">
                    <span class="font-bold text-indigo-950">Ruta ${data.route_id || "?"} • ${data.placa || placaGlobal}</span>
                    <span class="text-[9px] text-indigo-600 font-extrabold uppercase tracking-wider">${tiempoObj.texto}</span>
                </div>
                <div class="truck-emoji">🚛</div>
            </div>
        `;

        const icon = L.divIcon({
            html: iconhtml,
            className: 'custom-clean-truck-icon',
            iconSize: [120, 80],
            iconAnchor: [60, 65]
        });

        if (!truckMarker) {
            truckMarker = L.marker([parseFloat(lat), parseFloat(lng)], { icon }).addTo(map);
        } else {
            truckMarker.setLatLng([parseFloat(lat), parseFloat(lng)]);
            truckMarker.setIcon(icon);
        }
    }

    // ================= 📍 ASISTENTE GPS REPARADO Y SEGURO =================
    document.getElementById('btn-detectar-gps').onclick = () => {
        if (!navigator.geolocation) {
            alert("Tu navegador o dispositivo no soporta geolocalización.");
            return;
        }

        // 🚨 CORRECCIÓN: Comprobación segura del elemento antes de modificarlo
        const placeholderText = document.getElementById('placeholder-text');
        if (placeholderText) {
            placeholderText.innerText = "Accediendo al GPS de tu smartphone...";
        }
        
        const placeholder = document.getElementById('map-placeholder');
        if (placeholder) {
            placeholder.style.display = "flex";
            placeholder.style.opacity = "1";
        }

        navigator.geolocation.getCurrentPosition(
            pos => {
                // Éxito: Ocultamos el cargador
                if (placeholder) placeholder.style.display = "none";

                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                const icon = L.divIcon({ 
                    className: "home-pulse",
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                if (homeMarker) map.removeLayer(homeMarker);

                homeMarker = L.marker([lat, lng], {
                    icon,
                    draggable: true
                }).addTo(map);

                homeMarker.bindPopup("<b style='color:#000;'>🏡 Mueve el punto azul exactamente hasta tu puerta y dale Guardar.</b>").openPopup();

                window.tempLat = lat;
                window.tempLng = lng;

                document.getElementById('btn-confirmar-pin').classList.remove('hidden');
                map.setView([lat, lng], 17);

                homeMarker.on('dragend', e => {
                    const position = e.target.getLatLng();
                    window.tempLat = position.lat;
                    window.tempLng = position.lng;
                });
            }, 
            err => {
                // Error: Restauramos el texto inicial e informamos la causa exacta
                if (placeholderText) placeholderText.innerText = "Ingresa una placa o usa el GPS.";
                
                if (err.code === 1) {
                    alert("Permiso denegado. Debes dar autorización de ubicación a esta página en la barra de direcciones de tu navegador.");
                } else if (err.code === 2) {
                    alert("Posición no disponible. Asegúrate de tener activado el GPS o la ubicación de tu celular/computadora.");
                } else if (err.code === 3) {
                    alert("Se agotó el tiempo de espera al intentar obtener tu ubicación.");
                } else {
                    alert("Error desconocido al leer el GPS: " + err.message);
                }
            }, 
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };

    document.getElementById('btn-confirmar-pin').onclick = () => {
        if (window.tempLat && window.tempLng) {
            localStorage.setItem("home_lat", window.tempLat);
            localStorage.setItem("home_lng", window.tempLng);
            location.reload();
        }
    };

    document.getElementById('btn-borrar-casa').onclick = () => {
        localStorage.removeItem("home_lat");
        localStorage.removeItem("home_lng");
        location.reload();
    };

});
</script>
</x-guest-layout>