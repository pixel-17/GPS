<?php
namespace App\Http\Controllers;

use App\Models\Route; // Ajusta al nombre exacto de tu modelo
use Illuminate\Http\Request;

class VecinoController extends Controller
{
    public function index()
    {
        // Traemos solo las rutas de hoy que tienen un vehículo asociado
        $rutasActivas = Route::whereDate('created_at', today())
            ->whereHas('vehicle') 
            ->get();

        return view('vecinos.index', compact('rutasActivas'));
    }
}