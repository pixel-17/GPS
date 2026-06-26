<x-app-layout>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Editar Chofer</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('drivers.update', $driver) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Usuario (Operador)</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $driver->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">DNI</label>
                <input type="text" name="dni" value="{{ old('dni', $driver->dni) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('dni') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Número de Licencia</label>
                <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('license_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Teléfono</label>
                <input type="tel" name="phone" value="{{ old('phone', $driver->phone) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    required>
                @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="activo" {{ old('status', $driver->status) === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('status', $driver->status) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="suspendido" {{ old('status', $driver->status) === 'suspendido' ? 'selected' : '' }}>Suspendido</option>
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Guardar Cambios
                </button>
                <a href="{{ route('drivers.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>