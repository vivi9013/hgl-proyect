<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaSurtimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaSurtimientoController extends Controller
{
    /**
     * Muestra el listado de áreas de surtimiento.
     */
    public function index()
    {
        $areas = AreaSurtimiento::orderBy('id_area_surtimiento', 'desc')->paginate(10);

        return view('inventario.areas_surtimiento.index', compact('areas'));
    }

    /**
     * Guarda una nueva área de surtimiento en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo'   => 'required|string|max:100',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
            'tipo.required'   => 'El tipo de área es obligatorio.',
            'tipo.max'        => 'El tipo no puede superar los 100 caracteres.',
        ]);

        // Verificar duplicados por nombre y tipo (insensible a mayúsculas/minúsculas)
        $existe = AreaSurtimiento::whereRaw('LOWER(nombre) = ?', [strtolower(trim($request->nombre))])
            ->where('tipo', $request->tipo)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Esta área de surtimiento ya se encuentra registrada con ese tipo.']);
        }

        AreaSurtimiento::create([
            'nombre'         => trim($request->nombre),
            'tipo'           => $request->tipo,
            'activo'         => 1,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('areas_surtimiento.index')
            ->with('exitog', 'El área de surtimiento se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un área de surtimiento.
     */
    public function editar($id)
    {
        $area = AreaSurtimiento::findOrFail($id);

        return view('inventario.areas_surtimiento.editar', compact('area'));
    }

    /**
     * Actualiza los datos de un área de surtimiento.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo'   => 'required|string|max:100',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
            'tipo.required'   => 'El tipo de área es obligatorio.',
            'tipo.max'        => 'El tipo no puede superar los 100 caracteres.',
        ]);

        $area = AreaSurtimiento::findOrFail($id);

        // Verificar duplicados excluyendo el registro actual
        $existe = AreaSurtimiento::whereRaw('LOWER(nombre) = ?', [strtolower(trim($request->nombre))])
            ->where('tipo', $request->tipo)
            ->where('id_area_surtimiento', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ya existe otra área de surtimiento de ese tipo registrada con ese nombre.']);
        }

        $area->update([
            'nombre'         => trim($request->nombre),
            'tipo'           => $request->tipo,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('areas_surtimiento.index')
            ->with('exito', 'El área de surtimiento se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un área de surtimiento.
     */
    public function cambiarStatus($id)
    {
        $area = AreaSurtimiento::findOrFail($id);
        $area->activo         = $area->activo == 1 ? 0 : 1;
        $area->fecha_registro = now()->toDateString();
        $area->hora_registro  = now()->toTimeString();
        $area->id_usuario     = Auth::id() ?? 1;
        $area->save();

        return redirect()
            ->route('areas_surtimiento.index')
            ->with('exito', 'El estado del área de surtimiento se ha actualizado.');
    }

    /**
     * Verifica por AJAX si el nombre con cierto tipo ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('nombre');
        $tipo   = $request->query('tipo');

        if (!$nombre || !$tipo) {
            return response()->json(['disponible' => false, 'error' => 'Parámetros ausentes']);
        }

        $existe = AreaSurtimiento::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))])
            ->where('tipo', $tipo)
            ->exists();

        return response()->json(['disponible' => !$existe]);
    }

    /**
     * Genera el reporte/impresión de las áreas de surtimiento.
     */
    public function imprimir()
    {
        $areas = AreaSurtimiento::orderBy('nombre', 'asc')->get();

        return view('inventario.areas_surtimiento.reporte_impresion', compact('areas'));
    }
}
