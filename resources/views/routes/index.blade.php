<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Rutas</h1>
            <a href="{{ route('routes.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                ➕ Crear Ruta
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vehículo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Chofer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($routes as $route)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $route->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $route->scheduled_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $route->start_time }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                <span class="bg-slate-100 px-2 py-1 rounded font-mono text-xs text-slate-700">
                                    {{ $route->vehicle->plate ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $route->driver->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    @if($route->status === 'programada') bg-blue-100 text-blue-800
                                    @elseif($route->status === 'en_progreso') bg-yellow-100 text-yellow-800
                                    @elseif($route->status === 'completada') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $route->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2 text-sm">
                                <a href="{{ route('routes.show', $route) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalle">👁️</a>
                                <a href="{{ route('routes.edit', $route) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">✏️</a>
                                <form method="POST" action="{{ route('routes.destroy', $route) }}" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Seguro que deseas eliminar esta ruta municipal?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 font-medium">
                                🚛 No hay rutas recolectoras registradas para hoy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $routes->links() }}
        </div>
    </div>
</x-app-layout>
