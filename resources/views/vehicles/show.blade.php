<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Detalles del Vehículo</h1>
        <a href="{{ route('vehicles.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            ← Volver
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600">Placa</p>
                <p class="text-2xl font-bold">{{ $vehicle->plate }}</p>
            </div>
            <div>
                <p class="text-gray-600">Modelo</p>
                <p class="text-2xl font-bold">{{ $vehicle->model }}</p>
            </div>
            <div>
                <p class="text-gray-600">Año</p>
                <p class="text-2xl font-bold">{{ $vehicle->year }}</p>
            </div>
            <div>
                <p class="text-gray-600">Capacidad</p>
                <p class="text-2xl font-bold">{{ $vehicle->capacity }} kg</p>
            </div>
            <div>
                <p class="text-gray-600">Estado</p>
                <p class="text-xl font-bold">
                    <span class="px-2 py-1 rounded text-sm
                        @if($vehicle->status === 'activo') bg-green-100 text-green-800
                        @elseif($vehicle->status === 'inactivo') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($vehicle->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-6 space-x-2">
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700">
                ✏️ Editar
            </a>
            <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700" 
                    onclick="return confirm('¿Eliminar este vehículo?')">
                    🗑️ Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>