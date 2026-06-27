<x-guest-layout>
<div class="py-6 min-h-screen text-slate-800 bg-gradient-to-tr from-slate-50 to-indigo-50/40">
    <div class="w-full max-w-md mx-auto px-4 space-y-4">

        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-4 rounded-2xl shadow-xl shadow-indigo-600/10 flex items-center justify-between text-white">
            <span class="font-extrabold text-base tracking-wide flex items-center gap-2">
                <span>🚛</span> EcoRastreo Vecino
            </span>
        </div>

        <div class="grid grid-cols-3 gap-1 bg-white p-1.5 rounded-2xl border border-slate-100 shadow-lg">
            <a href="{{ route('vecino.index') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl bg-indigo-600 text-white text-center flex flex-col items-center gap-1">
                <span>📋</span> Rutas Activas
            </a>
            <a href="{{ route('vecino.monitoreo') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl text-slate-500 hover:bg-slate-50 text-center flex flex-col items-center gap-1">
                <span>🗺️</span> Ver Mapa
            </a>
            <a href="{{ route('vecino.horarios') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl text-slate-500 hover:bg-slate-50 text-center flex flex-col items-center gap-1">
                <span>⏰</span> Horarios
            </a>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">📍 Tu Residencia</h4>
                <p id="casa-status-text" class="text-xs text-slate-500">Sin registrar en este celular.</p>
            </div>
            <div class="flex gap-2">
                <button id="btn-detectar-gps" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-2 rounded-xl border border-emerald-100 transition">📍 Ubicar</button>
                <button id="btn-borrar-casa" class="text-rose-500 text-xs font-bold px-2 py-2">🗑️</button>
            </div>
        </div>
        
        <button id="btn-confirmar-pin" class="hidden w-full bg-emerald-600 text-white p-3 rounded-xl text-sm font-bold transition shadow-md">💾 Guardar Ubicación de Casa</button>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 space-y-3">
            <h3 class="text-xs font-black text-indigo-950 uppercase tracking-wider">Unidades Operando Hoy</h3>
            <div class="space-y-2.5">
                
                @forelse($rutasActivas as $ruta)
                    <a href="{{ route('vecino.monitoreo') }}?placa={{ $ruta->vehicle->placa ?? $ruta->id }}" 
                       class="p-4 bg-gradient-to-r from-slate-50 to-indigo-50/20 border border-slate-100 rounded-xl flex items-center justify-between hover:border-indigo-400 transition shadow-sm active:scale-95 block">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🚛</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Ruta #{{ $ruta->id }}</h4>
                                <p class="text-xs text-slate-500 font-medium">
                                    Placa: <span class="text-indigo-950 font-bold">{{ $ruta->vehicle->placa ?? 'S/N' }}</span>
                                </p>
                                <p class="text-[10px] text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Transmitiendo
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 bg-white shadow-sm border px-2.5 py-1.5 rounded-lg flex items-center gap-1">
                            Seguir 🗺️
                        </span>
                    </a>
                @empty
                    <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <span class="text-2xl">💤</span>
                        <p class="text-xs text-slate-400 font-medium mt-1">No hay camiones recolectores operando en este momento.</p>
                    </div>
                @endforelse

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Verificar si el vecino ya guardó su casa previamente
    const lat = localStorage.getItem("home_lat");
    if (lat) {
        document.getElementById('casa-status-text').innerText = "Casa registrada en este dispositivo.";
    }

    // Captura de GPS móvil express para fijar la casa
    document.getElementById('btn-detectar-gps').onclick = () => {
        if (!navigator.geolocation) return alert("Tu smartphone no soporta geolocalización.");
        navigator.geolocation.getCurrentPosition(pos => {
            window.tempLat = pos.coords.latitude;
            window.tempLng = pos.coords.longitude;
            document.getElementById('btn-confirmar-pin').classList.remove('hidden');
            alert("🏡 ¡Ubicación obtenida! Presiona el botón verde de abajo para registrar tu casa.");
        }, () => alert("Por favor, concede permisos de ubicación en tu teléfono."), { enableHighAccuracy: true });
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