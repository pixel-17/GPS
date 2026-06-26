<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Vehículos</h1>
        <a href="{{ route('vehicles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            ➕ Crear Vehículo
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left">Placa</th>
                    <th class="px-6 py-3 text-left">Modelo</th>
                    <th class="px-6 py-3 text-left">Año</th>
                    <th class="px-6 py-3 text-left">Capacidad</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="px-6 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold">{{ $vehicle->plate }}</td>
                        <td class="px-6 py-4">{{ $vehicle->model }}</td>
                        <td class="px-6 py-4">{{ $vehicle->year }}</td>
                        <td class="px-6 py-4">{{ $vehicle->capacity }} kg</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-sm
                                @if($vehicle->status === 'activo') bg-green-100 text-green-800
                                @elseif($vehicle->status === 'inactivo') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="text-blue-600 hover:text-blue-900">👁️</a>
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-yellow-600 hover:text-yellow-900">✏️</a>
                            <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No hay vehículos registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $vehicles->links() }}
    </div>
</div>
</x-app-layout>