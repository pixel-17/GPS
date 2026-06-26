<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .leaflet-container { z-index: 1 !important; }
        .btn-action-trigger {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-action-trigger:active {
            transform: scale(0.96);
            filter: brightness(0.85);
        }
        /* Pulso de transmisión en vivo para el radar GPS */
        .pulse-emerald {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            animation: pulse-gps 1.8s infinite;
        }
        @keyframes pulse-gps {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            70% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>

    <div class="py-6 min-h-screen text-slate-100 selection:bg-indigo-500 flex items-start justify-center" style="background-color: #0b1329;">
        <div class="w-full max-w-md px-4 space-y-5"> 
            
            <div class="flex items-center justify-between bg-slate-900/80 backdrop-blur-md p-4 rounded-2xl border border-slate-800 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-tr from-indigo-600 to-violet-500 text-white rounded-xl flex items-center justify-center font-black shadow-lg border border-indigo-400/30">
                        <span class="text-xl tracking-tight">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-black tracking-widest text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">Operador Activo</span>
                        <h1 class="text-base font-black text-white tracking-tight mt-0.5">¡Hola, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="cursor-pointer bg-rose-950/40 hover:bg-rose-950/70 text-rose-400 p-2.5 rounded-xl transition border border-rose-900/30 text-xs flex items-center justify-center gap-1 font-bold">
                        🚪 Salir
                    </button>
                </form>
            </div>

            <div>
                @if($rutasDeHoy->count() > 0)
                    @php $ruta = $rutasDeHoy->first(); @endphp
                    
                    <div class="space-y-4">
                        <div class="bg-gradient-to-br from-slate-900 to-slate-850 rounded-3xl p-5 border border-slate-800 shadow-xl space-y-4 relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 text-7xl opacity-5 pointer-events-none select-none">📋</div>
                            
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">📋</span>
                                    <div>
                                        <h3 class="font-black text-xs text-slate-200 uppercase tracking-wide">Ficha de Ruta</h3>
                                        <p class="text-[10px] text-slate-400">Datos consolidados del vehículo</p>
                                    </div>
                                </div>
                                <span class="bg-emerald-500/10 text-emerald-400 font-bold text-[10px] px-2 py-0.5 rounded-md border border-emerald-500/20 uppercase">Asignada</span>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-2 bg-slate-950/60 rounded-2xl p-4 border border-slate-900/60 text-xs">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-900/40">
                                    <span class="text-slate-400 font-medium">📍 Nombre:</span>
                                    <span class="font-bold text-white truncate max-w-[180px]">{{ $ruta->name }}</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-900/40">
                                    <span class="text-slate-400 font-medium">⏱️ Horario:</span>
                                    <span class="font-black text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded">{{ $ruta->start_time }} hrs</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-slate-400 font-medium">🚛 Camión:</span>
                                    <span class="font-bold text-indigo-400">🚛 {{ $ruta->vehicle->plate ?? 'Sin camión' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-900 rounded-3xl p-4 border border-slate-800 shadow-xl space-y-3">
                            <div class="flex justify-between items-center px-1">
                                <div>
                                    <h4 class="text-xs font-black text-slate-200 uppercase tracking-wide">🗺️ Inspeccionar Mapa de Recorrido</h4>
                                    <p class="text-[10px] text-slate-400">Trazo GeoJSON asignado a tu unidad</p>
                                </div>
                                <a href="{{ route('operator.map', $ruta->id) }}" class="text-[11px] font-black text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-xl hover:bg-indigo-500/20 transition">
                                    Pantalla Completa ↗
                                </a>
                            </div>

                            <div class="w-full bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 relative shadow-inner" style="height: 240px;">
                                <div id="map-operator-preview" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; height: 100%; width: 100%;"></div>
                            </div>
                        </div>

                        <div class="bg-slate-900 rounded-3xl p-5 border border-slate-800 shadow-xl space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-800/60 pb-2">
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Panel de Transmisión</h4>
                                <span id="tracking-badge" class="h-3 w-3 rounded-full bg-rose-500 shadow-md transition-all duration-300"></span>
                            </div>
                            
                            <div class="space-y-1 py-1">
                                <p class="font-black text-base text-slate-100 tracking-tight" id="tracking-text">Señal GPS: Apagada</p>
                                <p class="text-xs text-slate-400 font-medium" id="tracking-subtext">No estás compartiendo tu ubicación en tiempo real.</p>
                            </div>

                            <button id="btn-toggle-gps" class="btn-action-trigger w-full cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-black text-sm transition-all shadow-lg flex items-center justify-center gap-2 tracking-wide">
                                <span>📡</span> <span id="btn-text">Iniciar Transmisión GPS</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-xl space-y-3">
                        <div class="w-16 h-16 bg-slate-850 rounded-2xl flex items-center justify-center text-3xl mx-auto border border-slate-800">🎉</div>
                        <h3 class="font-black text-white text-lg">¡Estás libre hoy!</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">El administrador central no ha programado ninguna ruta de recojo para ti en esta fecha.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // Definición de variables globales del mapa
            let mapPreview = null;
            let userMarker = null; 

            // ---- SECCIÓN 1: INICIALIZACIÓN DEL MAPA INTEGRADO ----
            @if($rutasDeHoy->count() > 0 && isset($ruta))
                mapPreview = L.map('map-operator-preview', {
                    zoomControl: false, 
                    attributionControl: false 
                }).setView([-13.5319, -71.9675], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(mapPreview);

                setTimeout(function() {
                    if(mapPreview) mapPreview.invalidateSize();
                }, 400);

                const bounds = L.latLngBounds();
                let hasGeometria = false;

                @if($ruta->geojson)
                    try {
                        const geoData = {!! $ruta->geojson !!};
                        const routeLayer = L.geoJSON(geoData, {
                            style: function() { 
                                return { color: '#4f46e5', weight: 5, opacity: 0.85 }; 
                            }
                        }).addTo(mapPreview);

                        bounds.extend(routeLayer.getBounds());
                        hasGeometria = true;
                    } catch (e) {
                        console.error("Error al pintar trazo GeoJSON en mapa de operador.");
                    }
                @endif

                if (hasGeometria && mapPreview) {
                    mapPreview.fitBounds(bounds, { padding: [15, 15] });
                }
            @endif

            // ---- SECCIÓN 2: LÓGICA DE TRANSMISIÓN GPS CON AUTO-SEGUIMIENTO ----
            const btnToggle = document.getElementById('btn-toggle-gps');
            const btnText = document.getElementById('btn-text');
            const trackingText = document.getElementById('tracking-text');
            const trackingSubtext = document.getElementById('tracking-subtext');
            const trackingBadge = document.getElementById('tracking-badge');
            
            let isTracking = false;
            let gpsInterval = null;

            const routeId = "{{ isset($ruta) ? $ruta->id : '' }}";
            console.log("ID de Ruta cargado en el celular:", routeId);

            if(btnToggle) {
                btnToggle.addEventListener('click', function () {
                    if (!routeId) {
                        alert("⚠️ Error: No se pudo detectar el ID de la ruta asignada.");
                        return;
                    }

                    if (!isTracking) {
                        if (!navigator.geolocation) {
                            alert("Este equipo no soporta rastreo GPS.");
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                isTracking = true;
                                encenderTransmision();
                            },
                            function (error) {
                                alert("⚠️ Permiso Denegado: Debes permitir el acceso al GPS para transmitir.");
                                console.error("Error de permisos GPS:", error);
                            },
                            { enableHighAccuracy: true }
                        );
                    } else {
                        isTracking = false;
                        apagarTransmision();
                    }
                });
            }

            function encenderTransmision() {
                btnToggle.className = "btn-action-trigger w-full cursor-pointer bg-rose-600 hover:bg-rose-700 text-white py-3.5 rounded-xl font-black text-sm transition-all shadow-lg flex items-center justify-center gap-2 tracking-wide";
                btnText.innerText = "🛑 Detener Transmisión GPS";
                trackingText.innerText = "Señal GPS: Transmitiendo en Vivo";
                trackingSubtext.innerText = "El panel de administración y tu pantalla están registrando tus movimientos.";
                trackingBadge.className = "h-3 w-3 rounded-full bg-emerald-500 pulse-emerald shadow-md transition-all duration-300";

                enviarUbicacion();
                gpsInterval = setInterval(enviarUbicacion, 4000);
                console.log("📡 Servidor de rastreo inicializado.");
            }

            function apagarTransmision() {
                if (gpsInterval) {
                    clearInterval(gpsInterval);
                    gpsInterval = null;
                }
                
                btnToggle.className = "btn-action-trigger w-full cursor-pointer bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-black text-sm transition-all shadow-lg flex items-center justify-center gap-2 tracking-wide";
                btnText.innerText = "📡 Iniciar Transmisión GPS";
                trackingText.innerText = "Señal GPS: Apagada";
                trackingSubtext.innerText = "No estás compartiendo tu ubicación en tiempo real.";
                trackingBadge.className = "h-3 w-3 rounded-full bg-rose-500 shadow-md transition-all duration-300";
                
                // Limpiar marcador del operador al apagar rastreo
                if (userMarker && mapPreview) {
                    mapPreview.removeLayer(userMarker);
                    userMarker = null;
                }
                
                console.log("🛑 Rastreador apagado. Peticiones HTTP bloqueadas.");
            }

            function enviarUbicacion() {
                if (!isTracking || !routeId) return;

                navigator.geolocation.getCurrentPosition(function (position) {
                    if (!isTracking) return;

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Renderizado local e inmediato del chofer
                    actualizarMarcadorUsuario(lat, lng);
                    procesarEnvioGps(lat, lng);
                }, function(error) {
                    if (!isTracking) return;

                    console.warn("GPS físico omitido en entorno local. Enviando coordenadas simuladas.");
                    
                    const latSimulada = -13.522 + (Math.random() - 0.5) * 0.002;
                    const lngSimulada = -71.967 + (Math.random() - 0.5) * 0.002;
                    
                    actualizarMarcadorUsuario(latSimulada, lngSimulada);
                    procesarEnvioGps(latSimulada, lngSimulada);
                }, { 
                    enableHighAccuracy: true,
                    timeout: 3500 
                });
            }

            // Dibuja, actualiza y auto-centra la vista en la posición actual del conductor
            function actualizarMarcadorUsuario(lat, lng) {
                if (!mapPreview) return;

                if (!userMarker) {
                    userMarker = L.circleMarker([lat, lng], {
                        radius: 8,
                        fillColor: '#3b82f6', // Color azul nativo de GPS profesional
                        color: '#ffffff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    }).addTo(mapPreview);
                } else {
                    userMarker.setLatLng([lat, lng]);
                }

                // Centra suavemente el minimapa sobre el camión en movimiento
                mapPreview.panTo([lat, lng]);
            }

            function procesarEnvioGps(lat, lng) {
                if (!isTracking) return;

                fetch('/operador/gps/guardar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        route_id: parseInt(routeId),
                        latitude: parseFloat(lat),
                        longitude: parseFloat(lng)
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (isTracking) console.log("Señal registrada en BD:", data);
                })
                .catch(err => console.error("Error de red satelital:", err));
            }
        });
    </script>
</x-app-layout>