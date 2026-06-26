<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Choferes</h1>
        <a href="{{ route('drivers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            ➕ Crear Chofer
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
                    <th class="px-6 py-3 text-left">Nombre</th>
                    <th class="px-6 py-3 text-left">DNI</th>
                    <th class="px-6 py-3 text-left">Licencia</th>
                    <th class="px-6 py-3 text-left">Teléfono</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="px-6 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold">{{ $driver->user->name }}</td>
                        <td class="px-6 py-4">{{ $driver->dni }}</td>
                        <td class="px-6 py-4">{{ $driver->license_number }}</td>
                        <td class="px-6 py-4">{{ $driver->phone }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-sm
                                @if($driver->status === 'activo') bg-green-100 text-green-800
                                @elseif($driver->status === 'inactivo') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($driver->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="{{ route('drivers.show', $driver) }}" class="text-blue-600 hover:text-blue-900">👁️</a>
                            <a href="{{ route('drivers.edit', $driver) }}" class="text-yellow-600 hover:text-yellow-900">✏️</a>
                            <form method="POST" action="{{ route('drivers.destroy', $driver) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No hay choferes registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $drivers->links() }}
    </div>
</div>
</x-app-layout>