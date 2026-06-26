<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Driver; 
use Illuminate\Http\Request;
use Carbon\Carbon;

class RouteController extends Controller
{
    // ==========================================
    // 🚚 MÉTODOS EXCLUSIVOS DEL CHOFER / OPERADOR
    // ==========================================

    public function operatorIndex()
    {
        // Buscamos el perfil del chofer asociado al usuario logueado
        $driver = Driver::where('user_id', auth()->id())->first();

        if (!$driver) {
            return redirect('/')->with('error', 'No cuentas con un perfil de chofer operativo.');
        }

        // 💡 OPTIMIZADO PARA PRUEBAS: Buscamos rutas asignadas que estén activas 
        // sin importar si la fecha de programación quedó fijada en días pasados.
        $rutasDeHoy = Route::with(['vehicle'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['programada', 'en_progreso']) // Filtra solo trabajos vigentes
            ->orderBy('scheduled_date', 'asc')
            ->get();

        // Retorna la vista respetando tus variables originales de Blade ($driver y $rutasDeHoy)
        return view('operator.dashboard', compact('driver', 'rutasDeHoy'));
    }

    public function operatorMap($id)
    {
        $ruta = Route::with(['vehicle'])->findOrFail($id);
        return view('operator.map', compact('ruta'));
    }

    public function startRoute($id)
    {
        $route = Route::findOrFail($id);
        
        // Sincronizado con tus estados de validación ('en_progreso')
        $route->update(['status' => 'en_progreso']);

        return back()->with('success', 'Ruta iniciada con éxito.');
    }

    // ==========================================
    // 🛡️ MÉTODOS DEL CRUD DE ADMINISTRACIÓN (ADMIN)
    // ==========================================

    public function index()
    {
        $routes = Route::with(['vehicle', 'driver'])->paginate(15);
        return view('routes.index', compact('routes'));
    }

    public function create()
    {
        $vehicles = Vehicle::activos()->get();
        $drivers = Driver::activos()->get();
        return view('routes.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string'],
            'geojson'            => ['nullable', 'json'],
            'scheduled_date'     => ['required', 'date'],
            'start_time'         => ['required', 'date_format:H:i'],
            'estimated_end_time' => ['nullable', 'date_format:H:i'],
            'status'             => ['required', 'in:programada,en_progreso,completada,cancelada'],
            'vehicle_id'         => ['nullable', 'exists:vehicles,id'],
            'driver_id'          => ['nullable', 'exists:drivers,id'],
        ]);

        Route::create($validated);

        return redirect()->route('routes.index')
            ->with('success', 'Ruta creada exitosamente.');
    }

    public function show(Route $route)
    {
        return view('routes.show', compact('route'));
    }

    public function edit(Route $route)
    {
        $vehicles = Vehicle::activos()->get();
        $drivers = Driver::activos()->get();
        return view('routes.edit', compact('route', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string'],
            'geojson'            => ['nullable', 'json'],
            'scheduled_date'     => ['required', 'date'],
            'start_time'         => ['required', 'date_format:H:i'],
            'estimated_end_time' => ['nullable', 'date_format:H:i'],
            'status'             => ['required', 'in:programada,en_progreso,completada,cancelada'],
            'vehicle_id'         => ['nullable', 'exists:vehicles,id'],
            'driver_id'          => ['nullable', 'exists:drivers,id'],
        ]);

        $route->update($validated);

        return redirect()->route('routes.index')
            ->with('success', 'Ruta actualizada exitosamente.');
    }

    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->route('routes.index')
            ->with('success', 'Ruta eliminada exitosamente.');
    }

    // ==========================================
    // 📡 MÉTODOS DE MONITOREO EN VIVO (ADMIN)
    // ==========================================

    /**
     * Carga la pantalla principal del mapa de monitoreo para el Administrador
     */
    public function monitor($id)
    {
        // Buscamos la ruta con su camión y el usuario del chofer asignado
        $route = Route::with(['vehicle', 'driver.user'])->findOrFail($id);
        
        return view('routes.monitor', compact('route'));
    }

    /**
     * Devuelve las coordenadas más recientes de la tabla gps_locations en formato JSON al Admin
     */
    /**
     * Guarda la geolocalización adaptada a la estructura real de la BD
     */
    public function storeGps(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'route_id'  => 'required|integer',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // 🔍 Buscamos el vehículo asociado a esta ruta para cumplir con la clave foránea
        $route = \DB::table('routes')->where('id', $request->route_id)->first();
        $vehicleId = $route ? $route->vehicle_id : null;

        // Insertamos usando exactamente los nombres de tus columnas
        \DB::table('gps_locations')->insert([
            'route_id'    => $request->route_id,
            'vehicle_id'  => $vehicleId, // 🔑 Requerido por tu clave foránea
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'speed'       => 0,          // Opcional, por ahora en 0
            'accuracy'    => 0,          // Opcional, por ahora en 0
            'recorded_at' => now(),      // ⏱️ Tu columna real de tiempo
        ]);

        return response()->json(['success' => true, 'message' => 'Coordenadas registradas con éxito.']);
    }

    /**
     * Devuelve las coordenadas ordenadas por tu columna real 'recorded_at'
     */
    public function lastGps($id)
    {
        // Buscamos la última ubicación registrada de la ruta
        $lastLocation = \DB::table('gps_locations')
            ->where('route_id', $id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($lastLocation && $lastLocation->latitude && $lastLocation->longitude) {
            
            // ⏱️ VALIDACIÓN ESTRICTA: Si el último registro fue hace más de 15 segundos, 
            // asumimos que el chofer apagó la transmisión o cerró la app.
            $tiempoRegistro = \Carbon\Carbon::parse($lastLocation->recorded_at);
            if ($tiempoRegistro->diffInSeconds(now()) > 15) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Transmisión detenida por el operador.'
                ]);
            }

            return response()->json([
                'success'   => true,
                'latitude'  => $lastLocation->latitude,
                'longitude' => $lastLocation->longitude,
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'Sin señal activa.'
        ]);
    }
    // Renderiza la vista del mapa general
public function monitorGlobal()
{
    // Jalamos todas las rutas que tengan un vehículo asignado
    $routes = Route::with(['vehicle', 'driver.user'])->get();
    
    return view('admin.routes.global-monitor', compact('routes'));
}

// Devuelve las últimas ubicaciones de todos los camiones activos
public function allLastGps()
{
    $latestLocations = \DB::table('gps_locations as g1')
        ->select('g1.route_id', 'g1.latitude', 'g1.longitude', 'g1.recorded_at')
        ->whereRaw('g1.id = (SELECT max(g2.id) FROM gps_locations as g2 WHERE g2.route_id = g1.route_id)')
        ->get();

    // 🎨 Paleta de colores de alta visibilidad
    $colores = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#06b6d4', '#8b5cf6', '#f97316'];

    $activeTrucks = [];

    foreach ($latestLocations ?? [] as $loc) {
        // Filtro de 15 segundos para determinar si está en vivo
        if (\Carbon\Carbon::parse($loc->recorded_at)->diffInSeconds(now()) <= 15) {
            
            $route = \App\Models\Route::with(['vehicle', 'driver.user'])->find($loc->route_id);
            
            if ($route) {
                // Asignamos un color fijo basado en el ID de la ruta para que no cambie al parpadear
                $colorIndex = $route->id % count($colores);

                $activeTrucks[] = [
                    'route_id'  => $loc->route_id,
                    'routeName' => $route->name,
                    'plate'     => $route->vehicle->plate ?? 'Sin Placa',
                    'driver'    => $route->driver->user->name ?? 'Asignado',
                    'latitude'  => floatval($loc->latitude),
                    'longitude' => floatval($loc->longitude),
                    'color'     => $colores[$colorIndex] // 👈 Enviamos el color al mapa
                ];
            }
        }
    }

    return response()->json([
        'success' => true,
        'trucks'  => $activeTrucks
    ]);
}
}