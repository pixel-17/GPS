<x-guest-layout>
<div class="py-6 min-h-screen text-slate-800 bg-gradient-to-tr from-slate-50 to-indigo-50/40">
    <div class="w-full max-w-md mx-auto px-4 space-y-4">

        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-4 rounded-2xl shadow-xl text-white">
            <span class="font-extrabold text-base tracking-wide flex items-center gap-2">
                <span>⏰</span> Horarios Municipales
            </span>
        </div>

        <div class="grid grid-cols-3 gap-1 bg-white p-1.5 rounded-2xl border border-slate-100 shadow-lg">
            <a href="{{ route('vecino.index') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl text-slate-500 hover:bg-slate-50 text-center flex flex-col items-center gap-1">
                <span>📋</span> Rutas Activas
            </a>
            <a href="{{ route('vecino.monitoreo') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl text-slate-500 hover:bg-slate-50 text-center flex flex-col items-center gap-1">
                <span>🗺️</span> Ver Mapa
            </a>
            <a href="{{ route('vecino.horarios') }}" class="py-2.5 px-2 text-xs font-bold rounded-xl bg-indigo-600 text-white text-center flex flex-col items-center gap-1">
                <span>⏰</span> Horarios
            </a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xl space-y-4">
            <div>
                <h3 class="text-sm font-black text-indigo-950 uppercase tracking-wider">Cronograma por Sectores</h3>
                <p class="text-xs text-slate-500">Planifica la salida de tus residuos evitando acumulación pública.</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-indigo-50 text-indigo-950 font-bold">
                            <th class="p-3">Sector / Barrio</th>
                            <th class="p-3">Días</th>
                            <th class="p-3">Turno</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-3 font-semibold text-slate-900">Sector Centro</td>
                            <td class="p-3">Lun - Mié - Vie</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold text-[10px]">MAÑANA</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-3 font-semibold text-slate-900">Barrio San Pedro</td>
                            <td class="p-3">Mar - Jue - Sáb</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">TARDE</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-3 font-semibold text-slate-900">Urb. El Sol</td>
                            <td class="p-3">Lunes a Sábado</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-slate-800 text-white rounded font-bold text-[10px]">NOCHE</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-indigo-50/50 rounded-xl text-[11px] text-indigo-950 leading-relaxed">
                📢 <b>Recomendación municipal:</b> Deposita la basura en bolsas herméticas bien cerradas únicamente en los rangos establecidos de tu sector.
            </div>
        </div>

    </div>
</div>
</x-guest-layout>