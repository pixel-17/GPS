<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Contenedor principal que divide el Sidebar del Contenido */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background-color: #f8fafc;
        }
        /* Sidebar Fijo Izquierdo estilo Bootstrap Dark */
        .sidebar-admin {
            width: 260px;
            background-color: #0f172a;
            color: #94a3b8;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 100;
        }
        /* Enlaces del menú laterales */
        .sidebar-admin .nav-link {
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        .sidebar-admin .nav-link:hover {
            background-color: #1e293b;
            color: #ffffff;
        }
        .sidebar-admin .nav-link.active {
            background-color: #4f46e5;
            color: #ffffff;
        }
        /* Área de contenido a la derecha */
        .content-admin {
            flex: 1;
            padding: 2rem;
        }
        .card-bootstrap {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .icon-circle {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.25rem;
        }
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            .sidebar-admin {
                width: 100%;
                height: auto;
                position: static;
            }
        }
    </style>

    <div class="dashboard-container">
        
        <aside class="sidebar-admin d-flex flex-column p-3">
            <div class="d-flex align-items-center gap-2 pb-3 mb-4 border-bottom border-secondary border-opacity-25">
                <span class="fs-3">⚡</span>
                <div>
                    <h1 class="h6 fw-bold mb-0 text-white text-uppercase tracking-wider">GPS Control</h1>
                    <small class="text-success d-flex align-items-center gap-1" style="font-size: 10px;">
                        <span class="d-inline-block rounded-circle bg-success" style="width: 6px; height: 6px;"></span> Panel Activo
                    </small>
                </div>
            </div>

            <div class="nav flex-column gap-1 flex-grow-1">
                <span class="text-muted text-uppercase tracking-widest px-2 mb-2" style="font-size: 10px; font-weight: 700;">Módulos</span>
                
                <a href="{{ route('dashboard') }}" class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                    <span>📊</span> Resumen General
                </a>
                <a href="{{ route('vehicles.index') }}" class="nav-link {{ Route::is('vehicles.*') ? 'active' : '' }}">
                    <span>🚛</span> Control de Vehículos
                </a>
                <a href="{{ route('drivers.index') }}" class="nav-link {{ Route::is('drivers.*') ? 'active' : '' }}">
                    <span>🪪</span> Gestión de Choferes
                </a>
                <a href="{{ route('routes.index') }}" class="nav-link {{ Route::is('routes.*') ? 'active' : '' }}">
                    <span>🗺️</span> Despacho de Rutas
                </a>
            </div>

            @if (app()->environment('local'))
                <div class="pt-3 border-top border-secondary border-opacity-25 text-center">
                    <a href="/correo" target="_blank" class="btn btn-sm btn-outline-secondary text-light w-100 border-secondary" style="font-size: 11px;">
                        🧪 Probar Correo
                    </a>
                </div>
            @endif
        </aside>

        <main class="content-admin">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center pb-3 mb-4 border-bottom gap-3">
                <div>
                    <h2 class="h4 fw-bold text-dark mb-1">Consola de Operaciones</h2>
                    <p class="text-muted small mb-0">Información en tiempo real sincronizada desde la base de datos.</p>
                </div>
                <a href="{{ route('routes.create') }}" class="btn btn-primary btn-sm px-3 py-2 fw-bold" style="background-color: #4f46e5; border: 0; border-radius: 8px;">
                    + Crear Nueva Ruta GPS
                </a>
            </div>

            <div class="row g-3 mb-4">
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-bootstrap p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 11px; font-weight: 700;">Rutas Hoy</span>
                                <span class="h3 fw-bold text-dark mb-0">{{ $rutasHoy }}</span>
                            </div>
                            <div class="icon-circle" style="background-color: #eef2ff; color: #4f46e5;">📋</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-bootstrap p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 11px; font-weight: 700;">En Tránsito</span>
                                <span class="h3 fw-bold text-success mb-0 d-flex align-items-center gap-2">
                                    {{ $rutasEnProgreso }}
                                    @if($rutasEnProgreso > 0)
                                        <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                                    @endif
                                </span>
                            </div>
                            <div class="icon-circle" style="background-color: #ecfdf5; color: #10b981;">🚚</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-bootstrap p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 11px; font-weight: 700;">Flota Operativa</span>
                                <span class="h3 fw-bold text-dark mb-0">{{ $vehiculosActivos }} <span class="text-muted fs-6 fw-normal">/ {{ $totalVehiculos }}</span></span>
                            </div>
                            <div class="icon-circle" style="background-color: #fffbeb; color: #f59e0b;">🚛</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-bootstrap p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 11px; font-weight: 700;">Choferes</span>
                                <span class="h3 fw-bold text-dark mb-0">{{ $totalChoferes }}</span>
                            </div>
                            <div class="icon-circle" style="background-color: #f0fdfa; color: #0d9488;">🪪</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card card-bootstrap bg-white overflow-hidden">
                <div class="p-3 border-bottom bg-light bg-opacity-50">
                    <h3 class="h6 fw-bold mb-0 text-muted text-uppercase tracking-wider" style="font-size: 11px;">Últimos Despachos Generados</h3>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light text-muted fw-bold">
                            <tr>
                                <th class="p-3">ID Ruta</th>
                                <th class="p-3">Vehículo / Placa</th>
                                <th class="p-3">Operador Asignado</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-dark">
                            @forelse($ultimasRutas as $rutaItem)
                                <tr>
                                    <td class="p-3 fw-bold">#{{ $rutaItem->id }}</td>
                                    <td class="p-3">🚗 {{ $rutaItem->vehicle->placa ?? 'S/P' }}</td>
                                    <td class="p-3">{{ $rutaItem->driver->user->name ?? 'No asignado' }}</td>
                                    <td class="p-3 text-center">
                                        @if($rutaItem->status === 'en_progreso')
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success border-opacity-25 px-2.5 py-1" style="font-size: 10px;">EN PROGRESO</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary border-opacity-25 px-2.5 py-1" style="font-size: 10px;">{{ strtoupper($rutaItem->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-end">
                                        <a href="{{ route('routes.show', $rutaItem->id) }}" class="btn btn-sm btn-link text-primary fw-bold p-0 me-3 text-decoration-none">Detalle</a>
                                        <a href="{{ route('routes.monitor', $rutaItem->id) }}" class="btn btn-sm btn-link text-success fw-bold p-0 text-decoration-none">Rastrear 📡</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-muted">No se registran despachos logísticos en el sistema.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</x-app-layout>