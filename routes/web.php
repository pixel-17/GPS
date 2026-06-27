<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

// Controllers importados ordenadamente
use App\Http\Controllers\VecinoController; 
use App\Http\Controllers\PublicRouteController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\DashboardController; // Opcional para limpiar el /dashboard

// ==========================================
// 🌍 RUTAS PÚBLICAS (ACCESO LIBRE)
// ==========================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// 🌐 Vista principal del Vecino (Ciudadano en su celular)
Route::get('/vecino', [VecinoController::class, 'index'])->name('vecino.index');
Route::get('/vecino/monitoreo', [VecinoController::class, 'monitoreo'])->name('vecino.monitoreo');
Route::get('/vecino/horarios', [VecinoController::class, 'horarios'])->name('vecino.horarios');
Route::get('/monitoreo-ciudadano', [VecinoController::class, 'index'])->name('vecinos.index');

// ⚙️ APIs de soporte Técnico (Consumidas por el JavaScript del mapa público)
Route::prefix('api/vecino')->group(function () {
    Route::post('/buscar-texto', [PublicRouteController::class, 'buscarTexto']);
    Route::post('/buscar-cercano', [PublicRouteController::class, 'buscarCercano']);
});

Route::prefix('api/rutas')->group(function () {
    Route::get('/{id}/publico', [PublicRouteController::class, 'obtenerRuta']);
    Route::get('/{id}/ultima-posicion', [PublicRouteController::class, 'ultimaPosicion']);
});


// ==========================================
// 🔒 RUTAS PROTEGIDAS (SOLO USUARIOS AUTENTICADOS)
// ==========================================

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    /**
     * 🧠 Redirección Inteligente según Rol & Carga Analítica del Dashboard
     */
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // 🚚 Si es Chofer / Operador (role_id = 2) va a su panel móvil de inmediato
        if ($user && $user->role_id === 2) {
            return redirect()->route('operator.dashboard');
        }

        // 🛡️ Si es Administrador (role_id = 1), calculamos estadísticas e inyectamos el mapa global
        $totalVehiculos   = \App\Models\Vehicle::count();
        $vehiculosActivos = \App\Models\Vehicle::where('status', 'activo')->count();
        $totalChoferes    = \App\Models\Driver::count();
        
        $rutasHoy         = \App\Models\Route::whereDate('scheduled_date', \Carbon\Carbon::today())->count();
        $rutasEnProgreso  = \App\Models\Route::where('status', 'en_progreso')->count();

        // 🗺️ Oro Puro: Cargamos relaciones ansiosas (Eager Loading) para evitar el problema de consultas N+1
        $routes = \App\Models\Route::with(['vehicle', 'driver.user'])->get();

        // Traemos las últimas 5 rutas creadas para la tabla de control rápido
        $ultimasRutas = \App\Models\Route::with(['vehicle', 'driver.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalVehiculos', 
            'vehiculosActivos', 
            'totalChoferes', 
            'rutasHoy', 
            'rutasEnProgreso',
            'ultimasRutas',
            'routes'
        ));
    })->name('dashboard');


    // ==========================================
    // 🛡️ SUBZONA ADMINISTRADOR (role_id = 1)
    // ==========================================
    Route::middleware('check_role:administrador')->group(function () {
        
        // Catálogos principales
        Route::resource('vehicles', VehicleController::class);
        Route::resource('drivers', DriverController::class);
        
        // 📑 CRUD optimizado de Rutas (Uso de resource modular o nombres estandarizados)
        Route::controller(RouteController::class)->prefix('rutas')->name('routes.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/crear', 'create')->name('create');
            Route::post('/guardar', 'store')->name('store');
            Route::get('/{route}', 'show')->name('show');
            Route::get('/{route}/editar', 'edit')->name('edit');
            Route::put('/{route}/actualizar', 'update')->name('update');
            Route::delete('/{route}/eliminar', 'destroy')->name('destroy');
        });

        // Monitoreo GPS en vivo exclusivo del Admin
        Route::controller(RouteController::class)->prefix('admin')->group(function () {
            Route::get('/routes/{id}/monitor', 'monitor')->name('routes.monitor');
            Route::get('/admin/routes/{id}/last-gps', 'lastGps')->name('routes.last-gps');
            Route::get('/api/all-last-gps', 'allLastGps')->name('admin.routes.all-gps');
        });
    });


    // ==========================================
    // 🚚 SUBZONA CHOFER / OPERADOR (role_id = 2)
    // ==========================================
    Route::controller(RouteController::class)->prefix('operador')->name('operator.')->group(function () {
        Route::get('/dashboard', 'operatorIndex')->name('dashboard');
        Route::get('/ruta/{id}/mapa', 'operatorMap')->name('map');
        Route::post('/ruta/{id}/iniciar', 'startRoute')->name('start-route');
        Route::post('/gps/guardar', 'storeGps')->name('storeGps');
    });

});

// ==========================================
// 🧪 ENTORNO DE PRUEBAS
// ==========================================
if (app()->environment('local')) {
    Route::get('/correo', function () {
        Mail::to('pixeliq17@gmail.com')->send(new TestMail());
        return 'Correo de prueba enviado correctamente';
    });
}