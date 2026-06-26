<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index()
    {
        // Traemos los conductores paginados optimizando la carga del usuario relacional
        $drivers = Driver::with('user')->paginate(15);
        return view('drivers.index', compact('drivers'));
    }

    public function create()
    {
        // CORREGIDO: role_id 2 corresponde a 'operador' en nuestra configuración
        $users = User::where('role_id', 2)->get(); 
        return view('drivers.create', compact('users'));
    }

 public function store(Request $request)
{
    $request->validate([
        'name'           => ['required', 'string', 'max:255'],
        'dni'            => ['required', 'string', 'max:20', 'unique:users,dni'],
        'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'phone'          => ['nullable', 'string', 'max:15'],
        'license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number'],
        'status'         => ['required', 'in:activo,inactivo,suspendido'],
    ]);

    DB::transaction(function () use ($request) {
        // El Admin crea al usuario. Contraseña por defecto = DNI
        $user = User::create([
            'name'     => $request->name,
            'dni'      => $request->dni,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->dni), // <-- Credencial inicial para el chofer
            'role_id'  => 2,                         // <-- Forzado a Operador fijo
        ]);

        Driver::create([
            'user_id'        => $user->id,
            'license_number' => $request->license_number,
            'status'         => $request->status,
        ]);
    });

    return redirect()->route('drivers.index')
        ->with('success', 'Operador creado. Entréguele su DNI como usuario y contraseña.');
}

    public function show(Driver $driver)
    {
        $driver->load('user'); // Forzamos carga limpia de la relación
        return view('drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        // CORREGIDO: role_id 2 corresponde a 'operador'
        $users = User::where('role_id', 2)->get();
        return view('drivers.edit', compact('driver', 'users'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:drivers,user_id,'.$driver->id],
            'dni' => ['required', 'unique:drivers,dni,'.$driver->id, 'string', 'max:20'],
            'license_number' => ['required', 'unique:drivers,license_number,'.$driver->id, 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:15'],
            'status' => ['required', 'in:activo,inactivo,suspendido'],
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.index')
            ->with('success', 'Chofer actualizado exitosamente.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('drivers.index')
            ->with('success', 'Chofer eliminado exitosamente.');
    }
}