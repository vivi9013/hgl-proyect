<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Motivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MotivoController extends Controller
{
    /**
     * Muestra el listado paginado de motivos de devolución.
     * Aplica filtro de búsqueda si se envía el parámetro 'buscar'.
     */
    public function index(Request $request)
    {
        $buscar  = $request->get('buscar', '');
        $motivos = Motivo::filtradoPor($buscar)
            ->orderBy('id_motivo', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('inventario.motivos.index', compact('motivos', 'buscar'));
    }

    /**
     * Valida y persiste un nuevo motivo de devolución.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'modificar'   => 'required|in:Si,No',
        ], [
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'modificar.required'   => 'El campo Modificar Stock es obligatorio.',
            'modificar.in'         => 'El valor de Modificar Stock debe ser Si o No.',
        ]);

        if (Motivo::existeDescripcion($request->descripcion)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['descripcion' => 'Este motivo ya se encuentra registrado en el sistema.']);
        }

        Motivo::create([
            'descripcion'    => trim($request->descripcion),
            'modificar'      => $request->modificar,
            'activo'         => 1,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()->route('motivos.index')
            ->with('exitog', 'El motivo se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición para un motivo existente.
     */
    public function editar($id)
    {
        $motivo = Motivo::findOrFail($id);

        return view('inventario.motivos.editar', compact('motivo'));
    }

    /**
     * Valida y actualiza un motivo de devolución existente.
     */
    public function actualizar(Request $request, $id)
    {
        $motivo = Motivo::findOrFail($id);

        $request->validate([
            'descripcion' => 'required|string|max:255',
            'modificar'   => 'required|in:Si,No',
        ], [
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'modificar.required'   => 'El campo Modificar Stock es obligatorio.',
            'modificar.in'         => 'El valor de Modificar Stock debe ser Si o No.',
        ]);

        // Excluir el registro actual de la verificación de duplicados
        if (Motivo::existeDescripcion($request->descripcion, (int) $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['descripcion' => 'Este motivo ya se encuentra registrado en el sistema.']);
        }

        $motivo->update([
            'descripcion'    => trim($request->descripcion),
            'modificar'      => $request->modificar,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()->route('motivos.index')
            ->with('exitog', 'El motivo se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un motivo.
     */
    public function cambiarStatus($id)
    {
        $motivo = Motivo::findOrFail($id);

        $motivo->activo         = $motivo->activo == 1 ? 0 : 1;
        $motivo->fecha_registro = now()->toDateString();
        $motivo->hora_registro  = now()->toTimeString();
        $motivo->id_usuario     = Auth::id() ?? 1;
        $motivo->save();

        return redirect()->route('motivos.index')
            ->with('exitog', 'El estado del motivo se ha actualizado.');
    }

    /**
     * AJAX: verifica si una descripción ya existe en el catálogo.
     * Devuelve JSON { disponible: bool }.
     */
    public function verificar(Request $request)
    {
        $descripcion = $request->query('descripcion');

        if (!$descripcion) {
            return response()->json(['disponible' => false, 'error' => 'Parámetros ausentes']);
        }

        return response()->json([
            'disponible' => !Motivo::existeDescripcion($descripcion),
        ]);
    }

    /**
     * Genera la vista de impresión del catálogo de motivos.
     */
    public function imprimir(Request $request)
    {
        $buscar  = $request->get('buscar', '');
        $motivos = Motivo::filtradoPor($buscar)
            ->orderBy('id_motivo', 'desc')
            ->get();

        return view('inventario.motivos.reporte_impresion', compact('motivos', 'buscar'));
    }
}
