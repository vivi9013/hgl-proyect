<?php

namespace App\Http\Controllers\Computadoras;

use App\Http\Controllers\Controller;
use App\Models\Computadora;
use App\Models\Mobiliario;
use App\Models\Area;
use App\Models\Persona;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComputadoraController extends Controller
{
    /**
     * Muestra el listado de computadoras con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $areaFiltro = $request->get('area_id', 'Todos');

        $query = Computadora::with(['mobiliario.area', 'mobiliario.persona', 'mobiliario.departamento'])
            ->orderBy('id_computadora', 'desc');

        // Filtrar por Área si es diferente a "Todos"
        if ($areaFiltro !== 'Todos') {
            $query->whereHas('mobiliario', function ($q) use ($areaFiltro) {
                $q->where('id_area', $areaFiltro);
            });
        }

        // Aplicar término de búsqueda si existe
        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('inventario', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('so', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('nombre_equipo', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('ip', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhereHas('mobiliario', function ($mQ) use ($buscarLimpiado) {
                      $mQ->where('marca', 'LIKE', "%{$buscarLimpiado}%")
                         ->orWhere('modelo', 'LIKE', "%{$buscarLimpiado}%")
                         ->orWhere('serie', 'LIKE', "%{$buscarLimpiado}%");
                  });
            });
        }

        $computadoras = $query->paginate(10);

        // Si la petición viene por AJAX, retornamos exclusivamente la vista parcial de la tabla
        if ($request->ajax()) {
            return view('admin_mobiliario.computadoras.partials.tabla', compact('computadoras'));
        }

        // Obtener catálogos para el alta/edición
        $areas = Area::where('activo', 1)->orderBy('area', 'asc')->get();
        $personas = Persona::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $departamentos = Departamento::where('activo', 1)->orderBy('nombre', 'asc')->get();

        return view('admin_mobiliario.computadoras.index', compact(
            'computadoras',
            'buscar',
            'areas',
            'personas',
            'departamentos'
        ));
    }

    /**
     * Guarda una nueva computadora en la base de datos (mobiliario + computadoras).
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'inventario' => 'required|string|unique:mobiliario,inventario|unique:computadoras,inventario',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'nullable|string|max:255',
            'id_area' => 'required|integer',
            'id_persona' => 'required|integer',
            'id_departamento' => 'required|integer',
            'so' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'disco_duro' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:CPU,Laptop',
            'nombre_equipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Crear el registro general en 'mobiliario'
                Mobiliario::create([
                    'descripcion' => $request->descripcion ?? 'Computadora ' . $request->tipo,
                    'marca' => $request->marca,
                    'modelo' => $request->modelo,
                    'serie' => $request->serie ?? '',
                    'inventario' => $request->inventario,
                    'otros' => '',
                    'id_tipo_mobiliario' => $request->tipo === 'Laptop' ? 18 : 2, // 18=Laptop, 2=CPU/Escritorio
                    'id_area' => $request->id_area,
                    'id_persona' => $request->id_persona,
                    'id_departamento' => $request->id_departamento,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'activo' => 1,
                    'usuario' => Auth::id() ?? 1,
                    'id_factura' => 0 // Reemplazar según lógica legacy
                ]);

                // 2. Crear las especificaciones técnicas en 'computadoras'
                Computadora::create([
                    'inventario' => $request->inventario,
                    'so' => $request->so,
                    'ram' => $request->ram,
                    'disco_duro' => $request->disco_duro,
                    'ip' => $request->ip,
                    'tecnologia' => '',
                    'internet' => '',
                    'tipo' => $request->tipo,
                    'nombre_equipo' => $request->nombre_equipo,
                    'activo' => 1,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);
            });

            return redirect()
                ->route('computadoras.index')
                ->with('exitog', 'La computadora se ha guardado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al guardar el equipo: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario o datos de edición.
     */
    public function editar($id)
    {
        $computadora = Computadora::with('mobiliario')->findOrFail($id);
        
        $areas = Area::where('activo', 1)->orderBy('area', 'asc')->get();
        $personas = Persona::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $departamentos = Departamento::where('activo', 1)->orderBy('nombre', 'asc')->get();

        return view('admin_mobiliario.computadoras.editar', compact(
            'computadora',
            'areas',
            'personas',
            'departamentos'
        ));
    }

    /**
     * Actualiza los datos de la computadora (mobiliario + computadoras).
     */
    public function actualizar(Request $request, $id)
    {
        $computadora = Computadora::findOrFail($id);
        $mobiliario = Mobiliario::where('inventario', $computadora->inventario)->firstOrFail();

        $request->validate([
            'inventario' => "required|string|unique:mobiliario,inventario,{$mobiliario->id}|unique:computadoras,inventario,{$computadora->id_computadora},id_computadora",
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'nullable|string|max:255',
            'id_area' => 'required|integer',
            'id_persona' => 'required|integer',
            'id_departamento' => 'required|integer',
            'so' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'disco_duro' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:CPU,Laptop',
            'nombre_equipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $computadora, $mobiliario) {
                // Guardamos el inventario antiguo para actualizar la relación si cambia
                $oldInventario = $computadora->inventario;
                $newInventario = $request->inventario;

                // 1. Actualizar mobiliario
                $mobiliario->update([
                    'descripcion' => $request->descripcion ?? $mobiliario->descripcion,
                    'marca' => $request->marca,
                    'modelo' => $request->modelo,
                    'serie' => $request->serie ?? '',
                    'inventario' => $newInventario,
                    'id_tipo_mobiliario' => $request->tipo === 'Laptop' ? 18 : 2,
                    'id_area' => $request->id_area,
                    'id_persona' => $request->id_persona,
                    'id_departamento' => $request->id_departamento,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);

                // 2. Actualizar especificaciones técnicas en computadoras
                $computadora->update([
                    'inventario' => $newInventario,
                    'so' => $request->so,
                    'ram' => $request->ram,
                    'disco_duro' => $request->disco_duro,
                    'ip' => $request->ip,
                    'tipo' => $request->tipo,
                    'nombre_equipo' => $request->nombre_equipo,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);
            });

            return redirect()
                ->route('computadoras.index')
                ->with('exito', 'La computadora se ha actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el equipo: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estado activo/inactivo de la computadora.
     */
    public function cambiarStatus($id)
    {
        $computadora = Computadora::findOrFail($id);
        $nuevoEstado = $computadora->activo == 1 ? 0 : 1;

        try {
            DB::transaction(function () use ($computadora, $nuevoEstado) {
                // Actualizar computadora
                $computadora->update([
                    'activo' => $nuevoEstado,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);

                // Actualizar mobiliario correspondiente
                Mobiliario::where('inventario', $computadora->inventario)->update([
                    'activo' => $nuevoEstado,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);
            });

            return redirect()
                ->route('computadoras.index')
                ->with('exito', 'El estado del equipo se ha actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->route('computadoras.index')
                ->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el panel de reportes de computadoras.
     */
    public function reportes()
    {
        return view('admin_mobiliario.computadoras.analitica.reportes.index');
    }

    /**
     * Genera el reporte para impresión.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Computadora::with(['mobiliario.area', 'mobiliario.persona', 'mobiliario.departamento'])
            ->orderBy('id_computadora', 'asc');

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('inventario', 'LIKE', "%{$buscar}%")
                  ->orWhere('so', 'LIKE', "%{$buscar}%")
                  ->orWhere('nombre_equipo', 'LIKE', "%{$buscar}%")
                  ->orWhere('ip', 'LIKE', "%{$buscar}%");
            });
        }

        $computadoras = $query->get();

        return view('admin_mobiliario.computadoras.analitica.reportes.impresion', compact('computadoras', 'buscar'));
    }
}
