<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\DetalleEntradaCendis;
use App\Models\Inventario\EntradaCendis;
use Illuminate\Http\Request;

class DetalleEntradaCendisController extends Controller
{
    /**
     * Agrega un insumo al detalle de la entrada.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_entrada' => 'required|integer|exists:entradas_cendis,id_entrada',
            'id_insumo'  => 'required|integer|exists:insumos,id_insumo',
            'solicitado' => 'required|integer|min:0',
            'cantidad'   => 'required|integer|min:0',
            'faltante'   => 'required|integer|min:0',
        ], [
            'id_entrada.required' => 'La entrada es requerida.',
            'id_entrada.exists'   => 'La entrada no existe.',
            'id_insumo.required'  => 'Debe seleccionar un insumo.',
            'id_insumo.exists'    => 'El insumo seleccionado no existe.',
            'solicitado.required' => 'La cantidad solicitada es requerida.',
            'solicitado.min'      => 'La cantidad solicitada debe ser al menos 0.',
            'cantidad.required'   => 'La cantidad entregada es requerida.',
            'cantidad.min'        => 'La cantidad entregada debe ser al menos 0.',
            'faltante.required'   => 'La cantidad faltante es requerida.',
            'faltante.min'        => 'La cantidad faltante debe ser al menos 0.',
        ]);

        $entrada = EntradaCendis::findOrFail($request->id_entrada);

        if ($entrada->status !== 'En proceso') {
            if ($request->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede agregar insumos a una entrada finalizada.',
                ], 422);
            }
            return redirect()->back()->with('error', 'No se puede agregar insumos a una entrada finalizada.');
        }

        // Buscar si ya existe este insumo en la entrada
        $detalleExistente = DetalleEntradaCendis::where('id_entrada', $request->id_entrada)
            ->where('id_insumo', $request->id_insumo)
            ->first();

        if ($detalleExistente) {
            $nuevoSolicitado = (int)$detalleExistente->solicitado + (int)$request->solicitado;
            $nuevoCantidad   = (int)$detalleExistente->cantidad + (int)$request->cantidad;
            $nuevoFaltante   = $nuevoSolicitado - $nuevoCantidad;
            if ($nuevoFaltante < 0) {
                $nuevoFaltante = 0;
            }

            $detalleExistente->update([
                'solicitado' => $nuevoSolicitado,
                'cantidad'   => $nuevoCantidad,
                'faltante'   => $nuevoFaltante,
            ]);
        } else {
            DetalleEntradaCendis::create([
                'id_entrada' => $request->id_entrada,
                'id_insumo'  => $request->id_insumo,
                'solicitado' => $request->solicitado,
                'cantidad'   => $request->cantidad,
                'faltante'   => $request->faltante,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => 'Insumo agregado correctamente.'
            ]);
        }

        return redirect()
            ->route('entradas_cendis.detalle', $request->id_entrada)
            ->with('exitog', 'Insumo agregado correctamente.');
    }

    /**
     * Modifica la cantidad y recalcula el faltante de un detalle de entrada.
     */
    public function update(Request $request, $id)
    {
        $detalle = DetalleEntradaCendis::findOrFail($id);

        $request->validate([
            'cantidad' => 'required|integer|min:0',
        ]);

        $solicitado = (int)$detalle->solicitado;
        $cantidad   = (int)$request->cantidad;
        $faltante   = $solicitado - $cantidad;
        if ($faltante < 0) {
            $faltante = 0;
        }

        $detalle->update([
            'cantidad' => $cantidad,
            'faltante' => $faltante,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => 'Cantidad actualizada correctamente.'
            ]);
        }

        return redirect()
            ->route('entradas_cendis.detalle', $detalle->id_entrada)
            ->with('exito', 'Detalle actualizado correctamente.');
    }

    /**
     * Elimina una línea de detalle de la entrada.
     */
    public function destroy($id)
    {
        $detalle = DetalleEntradaCendis::findOrFail($id);
        $entrada = EntradaCendis::find($detalle->id_entrada);

        if ($entrada && $entrada->status !== 'En proceso') {
            if (request()->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede eliminar insumos de una entrada finalizada.',
                ], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar insumos de una entrada finalizada.');
        }

        $idEntrada = $detalle->id_entrada;
        $detalle->delete();

        if (request()->ajax()) {
            return response()->json([
                'ok'      => true,
                'mensaje' => 'Insumo eliminado de la entrada.'
            ]);
        }

        return redirect()
            ->route('entradas_cendis.detalle', $idEntrada)
            ->with('exito', 'Insumo eliminado de la entrada.');
    }
}
