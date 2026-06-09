<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\DetalleDevolucion;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\Insumo;
use Illuminate\Http\Request;

class DetalleDevolucionController extends Controller
{
    /**
     * Agrega un insumo a una devolución existente.
     * La tabla detalle_devoluciones solo tiene: id_devolucion, id_insumo, cantidad.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_devolucion' => 'required|integer|exists:devoluciones,id_devolucion',
            'id_insumo'     => 'required|integer|exists:insumos,id_insumo',
            'cantidad'      => 'required|integer|min:1',
        ], [
            'id_devolucion.required' => 'La devolución es requerida.',
            'id_devolucion.exists'   => 'La devolución no existe.',
            'id_insumo.required'     => 'Debe seleccionar un insumo.',
            'id_insumo.exists'       => 'El insumo seleccionado no existe.',
            'cantidad.required'      => 'La cantidad es requerida.',
            'cantidad.min'           => 'La cantidad debe ser al menos 1.',
        ]);

        // Verificar que la devolución siga "En proceso"
        $devolucion = Devolucion::findOrFail($request->id_devolucion);
        if ($devolucion->status !== 'En proceso') {
            if ($request->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede agregar insumos a una devolución finalizada.',
                ], 422);
            }
            return redirect()->back()->with('error', 'No se puede agregar insumos a una devolución finalizada.');
        }

        DetalleDevolucion::create([
            'id_devolucion' => $request->id_devolucion,
            'id_insumo'     => $request->id_insumo,
            'cantidad'      => $request->cantidad,
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'mensaje' => 'Insumo agregado correctamente.']);
        }

        return redirect()
            ->route('devoluciones.detalle', $request->id_devolucion)
            ->with('exitog', 'Insumo agregado correctamente a la devolución.');
    }

    /**
     * Actualiza la cantidad de un detalle.
     */
    public function update(Request $request, $id)
    {
        $detalle = DetalleDevolucion::findOrFail($id);

        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $detalle->update([
            'cantidad' => $request->cantidad,
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'mensaje' => 'Detalle actualizado correctamente.']);
        }

        return redirect()
            ->route('devoluciones.detalle', $detalle->id_devolucion)
            ->with('exito', 'Detalle actualizado correctamente.');
    }

    /**
     * Elimina un insumo de la devolución.
     */
    public function destroy($id)
    {
        $detalle    = DetalleDevolucion::findOrFail($id);
        $idDevol    = $detalle->id_devolucion;
        $devolucion = Devolucion::find($idDevol);

        if ($devolucion && $devolucion->status !== 'En proceso') {
            if (request()->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede eliminar insumos de una devolución finalizada.',
                ], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar insumos de una devolución finalizada.');
        }

        $detalle->delete();

        if (request()->ajax()) {
            return response()->json(['ok' => true, 'mensaje' => 'Insumo eliminado de la devolución.']);
        }

        return redirect()
            ->route('devoluciones.detalle', $idDevol)
            ->with('exito', 'Insumo eliminado de la devolución.');
    }
}
