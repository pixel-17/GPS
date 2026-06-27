<?php

namespace App\Http\Controllers;

use App\Models\Route; 
use Illuminate\Http\Request;

class VecinoController extends Controller
{
    // 📋 Vista Principal: Lista de rutas activas hoy
    public function index()
    {
        $rutasActivas = Route::whereDate('created_at', today())
            ->whereHas('vehicle') 
            ->with('vehicle') // Cargamos la relación para evitar consultas repetitivas (Eager Loading)
            ->get();

        return view('vecinos.index', compact('rutasActivas'));
    }

    // 🗺️ Vista del Mapa en Vivo
    public function monitoreo()
    {
        return view('vecinos.monitoreo');
    }

    // ⏰ Vista de Horarios Fijos
    public function horarios()
    {
        return view('vecinos.horarios');
    }
}