<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsumoController extends Controller
{
    /**
     * Muestra el listado de insumos con opción de búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Insumo::orderBy('id_insumo', 'desc');

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('clave', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        // Responder por AJAX si se requiere para el buscador local o dinámico
        if ($request->ajax()) {
            $sugerencias = $query->select('id_insumo', 'clave', 'descripcion', 'tipo', 'activo')
                ->limit(10)
                ->get()
                ->map(fn($item) => [
                    'id'          => $item->id_insumo,
                    'clave'       => $item->clave,
                    'descripcion' => $item->descripcion,
                    'tipo'        => $item->tipo,
                    'activo'      => $item->activo,
                ]);
            return response()->json($sugerencias);
        }

        $insumos = $query->paginate(10)->withQueryString();

        return view('inventario.insumos.index', compact('insumos', 'buscar'));
    }

    /**
     * Guarda un nuevo insumo en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'clave'       => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo'        => 'required|string|in:Material de curación,Medicamento',
        ], [
            'clave.required'       => 'La clave del insumo es obligatoria.',
            'clave.max'            => 'La clave no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción del insumo es obligatoria.',
            'tipo.required'        => 'El tipo de insumo es obligatorio.',
            'tipo.in'              => 'El tipo seleccionado no es válido.',
        ]);

        // Verificar duplicidad de clave (insensible a mayúsculas/minúsculas)
        $existe = Insumo::whereRaw(
            'LOWER(clave) = ?',
            [strtolower(trim($request->clave))]
        )->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['clave' => 'Esta clave de insumo ya se encuentra registrada.']);
        }

        Insumo::create([
            'clave'          => trim($request->clave),
            'descripcion'    => trim($request->descripcion),
            'tipo'           => $request->tipo,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'activo'         => 1,
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('insumos.index')
            ->with('exitog', 'El insumo se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar un insumo.
     */
    public function editar($id)
    {
        $insumo = Insumo::findOrFail($id);

        return view('inventario.insumos.editar', compact('insumo'));
    }

    /**
     * Actualiza un insumo en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'clave'       => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo'        => 'required|string|in:Material de curación,Medicamento',
        ], [
            'clave.required'       => 'La clave del insumo es obligatoria.',
            'clave.max'            => 'La clave no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción del insumo es obligatoria.',
            'tipo.required'        => 'El tipo de insumo es obligatorio.',
            'tipo.in'              => 'El tipo seleccionado no es válido.',
        ]);

        $insumo = Insumo::findOrFail($id);

        // Verificar si existe otra clave igual que no sea el registro actual
        $existe = Insumo::whereRaw(
            'LOWER(clave) = ?',
            [strtolower(trim($request->clave))]
        )
        ->where('id_insumo', '!=', $id)
        ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['clave' => 'Esta clave ya se encuentra registrada por otro insumo.']);
        }

        $insumo->update([
            'clave'          => trim($request->clave),
            'descripcion'    => trim($request->descripcion),
            'tipo'           => $request->tipo,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('insumos.index')
            ->with('exito', 'El insumo se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un insumo.
     */
    public function cambiarStatus($id)
    {
        $insumo = Insumo::findOrFail($id);

        $insumo->activo = $insumo->activo == 1 ? 0 : 1;
        $insumo->fecha_registro = now()->toDateString();
        $insumo->hora_registro  = now()->toTimeString();
        $insumo->id_usuario     = Auth::id() ?? 1;
        $insumo->save();

        return redirect()
            ->route('insumos.index')
            ->with('exito', 'El estado del insumo se ha actualizado correctamente.');
    }

    /**
     * Verifica dinámicamente si una clave de insumo ya está registrada.
     */
    public function verificar(Request $request)
    {
        $clave = $request->query('clave');
        $id    = $request->query('id');

        if (!$clave) {
            return response()->json([
                'disponible' => false,
                'error'      => 'Parámetro ausente'
            ]);
        }

        $query = Insumo::whereRaw(
            'LOWER(clave) = ?',
            [strtolower(trim($clave))]
        );

        if ($id) {
            $query->where('id_insumo', '!=', $id);
        }

        $existe = $query->exists();

        return response()->json([
            'disponible' => !$existe
        ]);
    }

    /**
     * Genera la vista de impresión del listado de insumos activos.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Insumo::where('activo', 1)->orderBy('clave', 'asc');

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('clave', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $insumos = $query->get();

        return view('inventario.insumos.reporte_impresion', compact('insumos', 'buscar'));
    }
}
