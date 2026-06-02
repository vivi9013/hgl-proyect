<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaAlmacenController extends Controller
{
    /**
     * Muestra el listado de áreas de almacén y el formulario de alta.
     */
    public function index()
    {
        $areas = AreaAlmacen::orderBy('id_area_almacen', 'desc')->paginate(10);

        return view('inventario.areas_almacen.index', compact('areas'));
    }

    /**
     * Guarda una nueva área de almacén en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        // Verificar duplicados (insensible a mayúsculas/minúsculas)
        $existe = AreaAlmacen::whereRaw('LOWER(nombre) = ?', [strtolower(trim($request->nombre))])->exists();
        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Esta área de almacén ya se encuentra registrada.']);
        }

        AreaAlmacen::create([
            'nombre'         => trim($request->nombre),
            'activo'         => 1,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('areas_almacen.index')
            ->with('exitog', 'El área de almacén se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un área.
     */
    public function editar($id)
    {
        $area = AreaAlmacen::findOrFail($id);

        return view('inventario.areas_almacen.editar', compact('area'));
    }

    /**
     * Actualiza los datos de un área de almacén.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        $area = AreaAlmacen::findOrFail($id);

        // Verificar duplicados excluyendo el registro actual
        $existe = AreaAlmacen::whereRaw('LOWER(nombre) = ?', [strtolower(trim($request->nombre))])
            ->where('id_area_almacen', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ya existe otra área de almacén registrada con ese nombre.']);
        }

        $area->update([
            'nombre'         => trim($request->nombre),
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('areas_almacen.index')
            ->with('exito', 'El área de almacén se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un área de almacén.
     */
    public function cambiarStatus($id)
    {
        $area = AreaAlmacen::findOrFail($id);
        $area->activo         = $area->activo == 1 ? 0 : 1;
        $area->fecha_registro = now()->toDateString();
        $area->hora_registro  = now()->toTimeString();
        $area->id_usuario     = Auth::id() ?? 1;
        $area->save();

        return redirect()
            ->route('areas_almacen.index')
            ->with('exito', 'El estado del área de almacén se ha actualizado.');
    }

    /**
     * Verifica por AJAX si el nombre ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('nombre');

        if (!$nombre) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $existe = AreaAlmacen::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))])->exists();

        return response()->json(['disponible' => !$existe]);
    }

    /**
     * Genera el reporte/impresión de las áreas de almacén.
     */
    public function imprimir()
    {
        $areas = AreaAlmacen::orderBy('nombre', 'asc')->get();

        return view('inventario.areas_almacen.reporte_impresion', compact('areas'));
    }
}
