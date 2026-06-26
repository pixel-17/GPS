<?php

namespace App\Http\Controllers;

use App\Models\Route; // Apuntando a tu modelo en inglés
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicRouteController extends Controller
{
    /**
     * Retorna el trazo fijo de calles (GeoJSON) del barrio seleccionado
     */
    public function obtenerRuta($id)
    {
        $ruta = Route::findOrFail($id);
        
        return response()->json([
            'name' => $ruta->name,
            'geojson' => $ruta->geojson 
        ]);
    }

    /**
     * Retorna la última coordenada enviada por el conductor en los últimos 15 minutos
     */
    public function ultimaPosicion($id)
    {
        // 🟢 Corregido: Usando 'recorded_at' según la estructura de tu tabla
        $ultimaPosicion = DB::table('gps_locations') 
            ->where('route_id', $id)
            ->where('recorded_at', '>=', now()->subMinutes(15))
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaPosicion) {
            // Buscamos la placa asociada relacionando con la tabla vehicles
            $vehiculo = DB::table('routes')
                ->leftJoin('vehicles', 'routes.vehicle_id', '=', 'vehicles.id')
                ->where('routes.id', $id)
                ->select('vehicles.plate as placa')
                ->first();

            $ultimaPosicion->placa = $vehiculo && $vehiculo->placa ? $vehiculo->placa : 'En Servicio';
        }

        return response()->json($ultimaPosicion);
    }

    /**
     * Busca el camión activo cuya ruta pasa por la ubicación actual del vecino
     */
    public function buscarCercano(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $vecinoLat = $request->latitude;
        $vecinoLng = $request->longitude;

        // 🟢 Corregido: Usando 'recorded_at' en el select y en la cláusula where
        $camionMasCercano = DB::table('gps_locations')
            ->select('route_id', 'latitude', 'longitude', 'recorded_at')
            ->where('recorded_at', '>=', now()->subMinutes(20))
            ->whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('gps_locations')
                      ->groupBy('route_id');
            })
            ->selectRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distancia', [$vecinoLat, $vecinoLng, $vecinoLat])
            ->having('distancia', '<', 5) 
            ->orderBy('distancia', 'asc')
            ->first();

        if (!$camionMasCercano) {
            return response()->json(['success' => false, 'message' => 'No hay unidades operando cerca de tu casa registrada.']);
        }

        $rutaData = DB::table('routes')
            ->leftJoin('vehicles', 'routes.vehicle_id', '=', 'vehicles.id') 
            ->where('routes.id', $camionMasCercano->route_id)
            ->select('routes.geojson', 'routes.name as ruta_nombre', 'vehicles.plate as placa') 
            ->first();

        return response()->json([
            'success' => true,
            'route_id' => $camionMasCercano->route_id,
            'latitude' => $camionMasCercano->latitude,
            'longitude' => $camionMasCercano->longitude,
            'ruta_nombre' => $rutaData ? $rutaData->ruta_nombre : 'Ruta Asignada',
            'placa' => $rutaData && $rutaData->placa ? $rutaData->placa : 'CON INTERNO', 
            'geojson' => $rutaData ? $rutaData->geojson : null
        ]);
    }

    /**
     * Busca el camión activo por texto (Placa o Barrio)
     */
    public function buscarTexto(Request $request)
{
    try {
        $query = $request->input('query'); 

        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Por favor, escribe una placa o ruta válida.']);
        }

        // 1. Buscamos la ruta. Usamos select explícito para evitar fallos si las columnas cambian.
        $rutaData = DB::table('routes')
            ->leftJoin('vehicles', 'routes.vehicle_id', '=', 'vehicles.id') 
            ->where(function($q) use ($query) {
                // Ajustamos para buscar tanto en el nombre de la ruta como en la placa
                $q->where('vehicles.plate', 'LIKE', "%{$query}%")
                  ->orWhere('routes.name', 'LIKE', "%{$query}%");
            })
            ->select('routes.id as route_id', 'routes.geojson', 'vehicles.plate as placa')
            ->first();

        if (!$rutaData) {
            return response()->json([
                'success' => false, 
                'message' => 'No se encontró ningún camión en servicio activo con ese nombre o placa.'
            ]);
        }

        // 2. Buscamos la última posición en la tabla gps_locations (Verificamos rango de tiempo amplio para pruebas)
        $ultimaPosicion = DB::table('gps_locations')
            ->where('route_id', $rutaData->route_id)
            ->where('recorded_at', '>=', now()->subMinutes(240)) // 4 horas para asegurar capturar datos de prueba
            ->orderBy('id', 'desc')
            ->first();

        if (!$ultimaPosicion) {
            return response()->json([
                'success' => false,
                'message' => 'El camión existe, pero no ha registrado transmisiones GPS en las últimas 4 horas.'
            ]);
        }

        // 3. Validación y limpieza estricta del GeoJSON para evitar congelar Leaflet
        $geojsonLimpio = null;
        if (!empty($rutaData->geojson)) {
            // Si ya viene como string, verificamos que sea un JSON válido
            if (is_string($rutaData->geojson)) {
                $testJson = json_decode($rutaData->geojson);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $geojsonLimpio = $rutaData->geojson;
                }
            } else {
                $geojsonLimpio = json_encode($rutaData->geojson);
            }
        }

        return response()->json([
            'success' => true,
            'route_id' => $rutaData->route_id,
            'latitude' => $ultimaPosicion->latitude,
            'longitude' => $ultimaPosicion->longitude,
            'placa' => $rutaData->placa ?? 'S/P',
            'geojson' => $geojsonLimpio
        ]);

    } catch (\Exception $e) {
        // 🔥 Si algo falla internamente, capturamos el error y lo enviamos como JSON limpio
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor (500): ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 200); // Forzamos estado 200 para que JavaScript pueda leer el mensaje de error real
    }
}
}