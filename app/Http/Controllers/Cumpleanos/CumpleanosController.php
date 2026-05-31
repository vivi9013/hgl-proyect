<?php

namespace App\Http\Controllers\Cumpleanos;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use Carbon\Carbon;

class CumpleanosController extends Controller
{
    public function index()
    {
        $mesActual = Carbon::now()->month;
        $nombreMes = Carbon::now()->locale('es')->monthName;

        // Traer personas activas que cumplen años en el mes actual,
        // con sus trabajadores, departamentos y sedes
        $cumpleaneros = Persona::where('activo', 1)
            ->whereMonth('fecha_nac', $mesActual)
            ->whereHas('trabajador')
            ->with([
                'trabajador.departamento',
                'sede',
            ])
            ->orderByRaw('DAY(fecha_nac) ASC')
            ->get();

        // Paleta de colores para las tarjetas
        $colores = [
            '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#1abc9c',
            '#3498db', '#2980b9', '#9b59b6', '#8e44ad', '#16a085',
            '#27ae60', '#d35400', '#c0392b', '#7f8c8d', '#2c3e50',
            '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
            '#8c564b', '#e377c2', '#bcbd22', '#17becf', '#aec7e8',
            '#ffbb78', '#98df8a', '#ff9896', '#c5b0d5', '#c49c94',
        ];

        return view('cumpleanos.index', compact('cumpleaneros', 'nombreMes', 'colores'));
    }
}
