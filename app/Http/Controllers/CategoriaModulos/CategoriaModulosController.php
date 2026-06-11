<?php

namespace App\Http\Controllers\CategoriaModulos;

use App\Http\Controllers\Controller;
use App\Models\CategoriaModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaModulosController extends Controller
{
    /**
     * Muestra la lista de categorías de módulos.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = CategoriaModulo::orderBy('orden', 'asc')
            ->orderBy('id_CategoriaModulo', 'desc');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('categoria', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('proyecto', 'like', '%' . $buscarLimpiado . '%');
            });
        }

        $categorias = $query->paginate(10);

        // Si la petición es AJAX, retornamos JSON para actualizar la UI dinámicamente
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin_sistema.categoria_modulos.partials.tabla', compact('categorias'))->render(),
                'links' => $categorias->links('pagination::bootstrap-4')->render(),
                'total' => $categorias->total(),
                'info' => "Mostrando " . ($categorias->firstItem() ?? 0) . " a " . ($categorias->lastItem() ?? 0) . " de " . $categorias->total() . " registros"
            ]);
        }
        // Calcular el siguiente orden sugerido para el formulario de alta
        $siguienteOrden = (CategoriaModulo::max('orden') ?? 0) + 1;

        return view('admin_sistema.categoria_modulos.index', compact('categorias', 'siguienteOrden'));
    }

    /**
     * Guarda una nueva categoría de módulo en el catálogo.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string|max:255',
            'proyecto'  => 'required|string|max:255',
            'colapsado' => 'required|in:si,no',
            'orden'     => 'required|integer|min:1',
        ]);

        $existe = CategoriaModulo::whereRaw('LOWER(categoria) = ?', [strtolower(trim($request->categoria))])->exists();
        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categoria' => 'Esta categoría de módulos ya se encuentra registrada.']);
        }

        $nuevoOrden = intval($request->orden);

        \DB::transaction(function () use ($nuevoOrden, $request) {
            // Asegurar que no hay gaps inicialmente
            $this->compactarOrdenes();

            $maxOrden = CategoriaModulo::max('orden') ?? 0;
            // Si el orden solicitado es mayor al máximo + 1, lo dejamos al final
            if ($nuevoOrden > $maxOrden + 1) {
                $nuevoOrden = $maxOrden + 1;
            }

            // Desplazar (shift) todas las categorías desde $nuevoOrden hacia arriba
            CategoriaModulo::where('orden', '>=', $nuevoOrden)->increment('orden');

            CategoriaModulo::create([
                'categoria'      => trim($request->categoria),
                'proyecto'       => trim($request->proyecto),
                'colapsado'      => $request->colapsado,
                'orden'          => $nuevoOrden,
                'fecha_registro' => now()->toDateString(),
                'hora_registro'  => now()->toTimeString(),
                'id_usuario'     => Auth::id() ?? 1,
                'activo'         => 1,
            ]);

            // Compactar al final para garantizar coherencia
            $this->compactarOrdenes();
        });

        return redirect()
            ->route('categoria_modulos.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar una categoría de módulo.
     */
    public function editar($id)
    {
        $categoria = CategoriaModulo::findOrFail($id);
        return view('admin_sistema.categoria_modulos.editar', compact('categoria'));
    }

    /**
     * Actualiza la categoría de módulo en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'categoria' => 'required|string|max:255',
            'proyecto'  => 'required|string|max:255',
            'colapsado' => 'required|in:si,no',
            'orden'     => 'required|integer|min:1',
        ]);

        $categoria = CategoriaModulo::findOrFail($id);

        $existe = CategoriaModulo::whereRaw('LOWER(categoria) = ?', [strtolower(trim($request->categoria))])
            ->where('id_CategoriaModulo', '!=', $id)
            ->exists();
        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categoria' => 'Esta categoría ya se encuentra registrada con otra clave.']);
        }

        $nuevoOrden = intval($request->orden);
        $ordenActual = intval($categoria->orden);

        \DB::transaction(function () use ($categoria, $nuevoOrden, $ordenActual, $request) {
            // Asegurar que no hay gaps inicialmente
            $this->compactarOrdenes();

            // Recargar el orden actual por si cambió al compactar
            $categoria->refresh();
            $ordenActual = intval($categoria->orden);

            // Ajustar nuevo orden si excede el máximo
            $maxOrden = CategoriaModulo::max('orden') ?? 0;
            if ($nuevoOrden > $maxOrden) {
                $nuevoOrden = $maxOrden;
            }

            if ($nuevoOrden !== $ordenActual) {
                if ($nuevoOrden < $ordenActual) {
                    CategoriaModulo::where('orden', '>=', $nuevoOrden)
                        ->where('orden', '<', $ordenActual)
                        ->increment('orden');
                } else {
                    CategoriaModulo::where('orden', '>', $ordenActual)
                        ->where('orden', '<=', $nuevoOrden)
                        ->decrement('orden');
                }
            }

            $categoria->update([
                'categoria'      => trim($request->categoria),
                'proyecto'       => trim($request->proyecto),
                'colapsado'      => $request->colapsado,
                'orden'          => $nuevoOrden,
                'fecha_registro' => now()->toDateString(),
                'hora_registro'  => now()->toTimeString(),
                'id_usuario'     => Auth::id() ?? 1,
            ]);

            // Compactar al final para garantizar coherencia
            $this->compactarOrdenes();
        });

        return redirect()
            ->route('categoria_modulos.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo (AJAX).
     */
    public function cambiarStatus($id)
    {
        $categoria = CategoriaModulo::findOrFail($id);
        $categoria->activo = ($categoria->activo == 1) ? 0 : 1;
        $categoria->fecha_registro = now()->toDateString();
        $categoria->hora_registro = now()->toTimeString();
        $categoria->id_usuario = Auth::id() ?? 1;
        $categoria->save();

        return response()->json([
            'success' => true,
            'activo'  => $categoria->activo,
            'message' => 'El estado se ha actualizado correctamente.'
        ]);
    }

    /**
     * Alterna el estado colapsado del panel inicial (AJAX).
     */
    public function cambiarColapsar($id)
    {
        $categoria = CategoriaModulo::findOrFail($id);
        $categoria->colapsado = ($categoria->colapsado === 'si') ? 'no' : 'si';
        $categoria->fecha_registro = now()->toDateString();
        $categoria->hora_registro = now()->toTimeString();
        $categoria->id_usuario = Auth::id() ?? 1;
        $categoria->save();

        return response()->json([
            'success'   => true,
            'colapsado' => $categoria->colapsado,
            'message'   => 'La configuración del panel se ha actualizado.'
        ]);
    }

    /**
     * AJAX: Verifica si el nombre de la categoría ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('categoria');

        if (!$nombre) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $existe = CategoriaModulo::whereRaw('LOWER(categoria) = ?', [strtolower(trim($nombre))])->exists();

        return response()->json(['disponible' => !$existe]);
    }

    /**
     * Muestra la interfaz de reportes.
     */
    public function reportes()
    {
        return view('admin_sistema.categoria_modulos.analitica.reportes');
    }

    /**
     * Genera la lista completa de impresión (vista imprimible premium).
     */
    public function imprimir()
    {
        $categorias = CategoriaModulo::orderBy('categoria', 'asc')->get();
        return view('admin_sistema.categoria_modulos.reportes.reporte_impresion', compact('categorias'));
    }

    /**
     * Muestra la interfaz del menú de gráficas.
     */
    public function graficas()
    {
        $dataGrafica = CategoriaModulo::whereHas('modulos')
            ->withCount('modulos as contador')
            ->orderBy('categoria', 'asc')
            ->get();

        return view('admin_sistema.categoria_modulos.analitica.graficas', compact('dataGrafica'));
    }

    /**
     * Compacta el orden de las categorías para evitar gaps (números correlativos 1 a N).
     */
    private function compactarOrdenes()
    {
        $categorias = CategoriaModulo::orderBy('orden', 'asc')
            ->orderBy('id_CategoriaModulo', 'asc')
            ->get();

        foreach ($categorias as $index => $cat) {
            $nuevoOrden = $index + 1;
            if ($cat->orden != $nuevoOrden) {
                $cat->orden = $nuevoOrden;
                $cat->save();
            }
        }
    }
}
