<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Crear Vehículo</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('vehicles.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Placa</label>
                <input type="text" name="plate" value="{{ old('plate') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('plate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Modelo</label>
                <input type="text" name="model" value="{{ old('model') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Año</label>
                    <input type="number" name="year" value="{{ old('year') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required>
                    @error('year') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Capacidad (kg)</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        required>
                    @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="activo" {{ old('status') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('status') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="mantenimiento" {{ old('status') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Guardar
                </button>
                <a href="{{ route('vehicles.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>