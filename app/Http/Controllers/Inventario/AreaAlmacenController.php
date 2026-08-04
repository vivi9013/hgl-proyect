<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaAlmacen;
use App\Traits\GestionaCatalogoSimple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaAlmacenController extends Controller
{
    use GestionaCatalogoSimple;

    /**
     * Muestra el listado paginado de áreas de almacén.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $query  = AreaAlmacen::filtradoPor($buscar)->orderBy('id_area_almacen', 'desc');

        if ($request->ajax()) {
            $sugerencias = $query->select('id_area_almacen', 'nombre', 'activo')
                ->limit(10)
                ->get()
                ->map(fn($a) => [
                    'id'     => $a->id_area_almacen,
                    'nombre' => $a->nombre,
                    'activo' => $a->activo,
                ]);
            return response()->json($sugerencias);
        }

        $areas = $query->paginate(10)->withQueryString();

        return view('inventario.areas_almacen.index', compact('areas', 'buscar'));
    }

    /**
     * Guarda una nueva área de almacén.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        if (AreaAlmacen::existeNombre($request->nombre)) {
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

        return redirect()->route('areas_almacen.index')
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

        if (AreaAlmacen::existeNombre($request->nombre, (int) $id)) {
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

        return redirect()->route('areas_almacen.index')
            ->with('exito', 'El área de almacén se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un área de almacén.
     */
    public function cambiarStatus($id)
    {
        $area = AreaAlmacen::findOrFail($id);
        return $this->alternarEstadoCatalogo($area, 'areas_almacen.index', 'El estado del área de almacén se ha actualizado.');
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

        return response()->json([
            'disponible' => !AreaAlmacen::existeNombre($nombre),
        ]);
    }

    /**
     * Genera el reporte/impresión de las áreas de almacén.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $areas  = AreaAlmacen::filtradoPor($buscar)->orderBy('nombre', 'asc')->get();

        return view('inventario.areas_almacen.reporte_impresion', compact('areas', 'buscar'));
    }
}