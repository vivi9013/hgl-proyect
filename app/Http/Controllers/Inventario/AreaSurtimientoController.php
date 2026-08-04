<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaSurtimiento;
use App\Traits\GestionaCatalogoSimple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaSurtimientoController extends Controller
{
    use GestionaCatalogoSimple;

    /**
     * Muestra el listado paginado de áreas de surtimiento.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $query  = AreaSurtimiento::filtradoPor($buscar)->orderBy('id_area_surtimiento', 'desc');

        if ($request->ajax()) {
            $sugerencias = $query->select('id_area_surtimiento', 'nombre', 'tipo', 'activo')
                ->limit(10)
                ->get()
                ->map(fn($a) => [
                    'id'     => $a->id_area_surtimiento,
                    'nombre' => $a->nombre,
                    'tipo'   => $a->tipo,
                    'activo' => $a->activo,
                ]);
            return response()->json($sugerencias);
        }

        $areas = $query->paginate(10)->withQueryString();

        return view('inventario.areas_surtimiento.index', compact('areas', 'buscar'));
    }

    /**
     * Guarda una nueva área de surtimiento.
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

        if (AreaSurtimiento::existeNombreYTipo($request->nombre, $request->tipo)) {
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

        return redirect()->route('areas_surtimiento.index')
            ->with('exitog', 'El área de surtimiento se ha guardado correctamente.');
    }

    /**
     * Actualiza los datos de un área de surtimiento existente.
     */
    public function actualizar(Request $request, $id)
    {
        $area = AreaSurtimiento::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo'   => 'required|string|max:100',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
            'tipo.required'   => 'El tipo de área es obligatorio.',
            'tipo.max'        => 'El tipo no puede superar los 100 caracteres.',
        ]);

        if (AreaSurtimiento::existeNombreYTipo($request->nombre, $request->tipo, (int) $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Esta área de surtimiento ya se encuentra registrada con ese tipo.']);
        }

        $area->update([
            'nombre'         => trim($request->nombre),
            'tipo'           => $request->tipo,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()->route('areas_surtimiento.index')
            ->with('exito', 'El área de surtimiento se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un área de surtimiento.
     */
    public function cambiarStatus($id)
    {
        $area = AreaSurtimiento::findOrFail($id);
        return $this->alternarEstadoCatalogo($area, 'areas_surtimiento.index', 'El estado del área de surtimiento se ha actualizado.');
    }

    /**
     * Verifica por AJAX si el nombre y tipo ya están registrados.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('nombre');
        $tipo   = $request->query('tipo');

        if (!$nombre || !$tipo) {
            return response()->json(['disponible' => false, 'error' => 'Parámetros ausentes']);
        }

        return response()->json([
            'disponible' => !AreaSurtimiento::existeNombreYTipo($nombre, $tipo),
        ]);
    }
}
