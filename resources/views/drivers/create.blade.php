<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Registrar Nuevo Chofer</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('drivers.store') }}">
                @csrf

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Nombre Completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">DNI</label>
                        <input type="text" name="dni" value="{{ old('dni') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('dni') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Número de Licencia</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('license_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Estado del Chofer</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-bold">
                        Guardar Chofer
                    </button>
                    <a href="{{ route('drivers.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>