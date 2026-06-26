<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Detalles del Chofer</h1>
        <a href="{{ route('drivers.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            ← Volver
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600">Nombre</p>
                <p class="text-2xl font-bold">{{ $driver->user->name }}</p>
            </div>
            <div>
                <p class="text-gray-600">Email</p>
                <p class="text-2xl font-bold">{{ $driver->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-600">DNI</p>
                <p class="text-2xl font-bold">{{ $driver->dni }}</p>
            </div>
            <div>
                <p class="text-gray-600">Número de Licencia</p>
                <p class="text-2xl font-bold">{{ $driver->license_number }}</p>
            </div>
            <div>
                <p class="text-gray-600">Teléfono</p>
                <p class="text-2xl font-bold">{{ $driver->phone }}</p>
            </div>
            <div>
                <p class="text-gray-600">Estado</p>
                <p class="text-xl font-bold">
                    <span class="px-2 py-1 rounded text-sm
                        @if($driver->status === 'activo') bg-green-100 text-green-800
                        @elseif($driver->status === 'inactivo') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($driver->status) }}
                    </span>
                </p>
            </div>
        </div>

        <div class="mt-6 space-x-2">
            <a href="{{ route('drivers.edit', $driver) }}" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700">
                ✏️ Editar
            </a>
            <form method="POST" action="{{ route('drivers.destroy', $driver) }}" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700" 
                    onclick="return confirm('¿Eliminar este chofer?')">
                    🗑️ Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>