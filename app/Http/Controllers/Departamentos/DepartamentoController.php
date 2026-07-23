<?php

namespace App\Http\Controllers\Departamentos;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartamentoController extends Controller
{
    /**
     * Muestra el listado de departamentos con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Departamento::with('responsable')->orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('abreviatura', 'LIKE', "%{$buscar}%")
                  ->orWhere('extension', 'LIKE', "%{$buscar}%");
            });
        }

        // AJAX: devolver solo la vista parcial de la tabla
        if ($request->ajax()) {
            $departamentos = $query->paginate(10)->withQueryString();
            return view('admin_institucional.departamentos.partials.tabla', compact('departamentos'));
        }

        $departamentos = $query->paginate(10)->withQueryString();
        $personas = Persona::where('activo', 1)->orderBy('nombre')->get();

        return view('admin_institucional.departamentos.index', compact('departamentos', 'buscar', 'personas'));
    }

    /**
     * Guarda un nuevo departamento en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'abreviatura' => 'required|string|max:10',
            'extension'   => 'nullable|string|max:50',
            'id_persona'  => 'nullable|integer',
        ], [
            'nombre.required'      => 'El nombre del departamento es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 255 caracteres.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede superar los 10 caracteres.',
        ]);

        // Verificar duplicado de nombre (insensible a mayúsculas)
        $existeNombre = Departamento::whereRaw(
            'LOWER(nombre) = ?',
            [strtolower(trim($request->nombre))]
        )->exists();

        if ($existeNombre) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Este departamento ya se encuentra registrado.']);
        }

        // Verificar duplicado de abreviatura
        $existeAbrev = Departamento::whereRaw(
            'LOWER(abreviatura) = ?',
            [strtolower(trim($request->abreviatura))]
        )->exists();

        if ($existeAbrev) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['abreviatura' => 'Esta abreviatura ya se encuentra registrada.']);
        }

        Departamento::create([
            'nombre'      => trim($request->nombre),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'extension'   => trim($request->extension) ?: null,
            'id_persona'  => $request->id_persona ?: null,
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'activo'      => 1,
            'usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('departamentos.index')
            ->with('exitog', 'El departamento se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un departamento.
     */
    public function editar($id)
    {
        $departamento = Departamento::findOrFail($id);
        $personas = Persona::where('activo', 1)->orderBy('nombre')->get();

        return view('admin_institucional.departamentos.editar', compact('departamento', 'personas'));
    }

    /**
     * Actualiza los datos de un departamento.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'abreviatura' => 'required|string|max:10',
            'extension'   => 'nullable|string|max:50',
            'id_persona'  => 'nullable|integer',
        ], [
            'nombre.required'      => 'El nombre del departamento es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 255 caracteres.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede superar los 10 caracteres.',
        ]);

        $departamento = Departamento::findOrFail($id);

        // Verificar duplicado de nombre excluyendo el actual
        $existeNombre = Departamento::whereRaw(
            'LOWER(nombre) = ?',
            [strtolower(trim($request->nombre))]
        )->where('id', '!=', $id)->exists();

        if ($existeNombre) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ya existe otro departamento con ese nombre.']);
        }

        // Verificar duplicado de abreviatura excluyendo el actual
        $existeAbrev = Departamento::whereRaw(
            'LOWER(abreviatura) = ?',
            [strtolower(trim($request->abreviatura))]
        )->where('id', '!=', $id)->exists();

        if ($existeAbrev) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['abreviatura' => 'Ya existe otro departamento con esa abreviatura.']);
        }

        $departamento->update([
            'nombre'      => trim($request->nombre),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'extension'   => trim($request->extension) ?: null,
            'id_persona'  => $request->id_persona ?: null,
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('departamentos.index')
            ->with('exito', 'El departamento se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un departamento.
     */
    public function cambiarStatus($id)
    {
        $departamento = Departamento::findOrFail($id);

        $departamento->activo  = $departamento->activo == 1 ? 0 : 1;
        $departamento->fecha   = now()->toDateString();
        $departamento->hora    = now()->toTimeString();
        $departamento->usuario = Auth::id() ?? 1;
        $departamento->save();

        return redirect()
            ->route('departamentos.index')
            ->with('exito', 'El estado del departamento se ha actualizado correctamente.');
    }

    /**
     * Verifica por AJAX si el nombre o la abreviatura ya están registrados.
     */
    public function verificar(Request $request)
    {
        $nombre      = $request->query('nombre');
        $abreviatura = $request->query('abreviatura');
        $excluirId   = $request->query('excluir_id');

        $resultado = ['nombre_disponible' => true, 'abreviatura_disponible' => true];

        if ($nombre) {
            $query = Departamento::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))]);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
            $resultado['nombre_disponible'] = !$query->exists();
        }

        if ($abreviatura) {
            $query = Departamento::whereRaw('LOWER(abreviatura) = ?', [strtolower(trim($abreviatura))]);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
            $resultado['abreviatura_disponible'] = !$query->exists();
        }

        return response()->json($resultado);
    }

    /**
     * Muestra el panel de reportes de departamentos.
     */
    public function reportes()
    {
        return view('admin_institucional.departamentos.analitica.reportes.index');
    }

    /**
     * Genera el reporte de impresión de departamentos.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Departamento::with('responsable')->orderBy('nombre', 'asc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('abreviatura', 'LIKE', "%{$buscar}%");
            });
        }

        $departamentos = $query->get();

        return view(
            'admin_institucional.departamentos.analitica.reportes.impresion',
            compact('departamentos', 'buscar')
        );
    }

    /**
     * Muestra las gráficas analíticas del catálogo de departamentos.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total     = Departamento::count();
        $activos   = Departamento::where('activo', 1)->count();
        $inactivos = Departamento::where('activo', 0)->count();

        // Donut: distribución activos / inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top 10 departamentos con mayor cantidad de mobiliario asignado
        $porMobiliario = DB::table('mobiliario')
            ->join('departamentos', 'mobiliario.id_departamento', '=', 'departamentos.id')
            ->select('departamentos.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('departamentos.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'nombre');

        // Barras: top 10 departamentos con mayor cantidad de trabajadores asignados
        $porTrabajadores = DB::table('trabajadores')
            ->join('departamentos', 'trabajadores.id_departamento', '=', 'departamentos.id')
            ->select('departamentos.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('departamentos.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'nombre');

        $stats = [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $inactivos,
        ];

        return view('admin_institucional.departamentos.analitica.graficas', compact(
            'stats', 'porEstado', 'porMobiliario', 'porTrabajadores'
        ));
    }
}
