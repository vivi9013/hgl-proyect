<?php

namespace App\Http\Controllers\Trabajadores;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\Trabajador;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\TipoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrabajadorController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de trabajadores con búsqueda, filtros y paginación AJAX.
     */
    public function index(Request $request)
    {
        $buscar          = $request->get('buscar', '');
        $idDepartamento  = $request->get('id_departamento', '');
        $idPuesto        = $request->get('id_puesto', '');
        $idTipoTrabajador= $request->get('id_tipo_trabajador', '');
        $idSede          = $request->get('id_sede', '');
        $status          = $request->get('status', '');

        $query = Trabajador::with(['persona', 'sede', 'departamento', 'puesto', 'tipoTrabajador'])
            ->orderBy('id', 'desc');

        // Búsqueda por número de empleado o datos de la persona
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('num_empleado', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('persona', function ($pq) use ($buscar) {
                      $pq->where('nombre', 'LIKE', "%{$buscar}%")
                         ->orWhere('ap_paterno', 'LIKE', "%{$buscar}%")
                         ->orWhere('ap_materno', 'LIKE', "%{$buscar}%")
                         ->orWhere('curp', 'LIKE', "%{$buscar}%")
                         ->orWhere('rfc', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if (!empty($idDepartamento)) {
            $query->where('id_departamento', $idDepartamento);
        }

        if (!empty($idPuesto)) {
            $query->where('id_puesto', $idPuesto);
        }

        if (!empty($idTipoTrabajador)) {
            $query->where('id_tipo_trabajador', $idTipoTrabajador);
        }

        if (!empty($idSede)) {
            $query->where('id_sede', $idSede);
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts = array_map(function ($val) {
                return $val === 'Activo' ? 1 : 0;
            }, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $trabajadores = $query->paginate(10)->withQueryString();

        // Datasets para selectores en la vista
        $departamentos  = Departamento::where('activo', 1)->orderBy('nombre')->get();
        $puestos        = Puesto::where('activo', 1)->orderBy('puesto')->get();
        $tiposTrabajador= TipoTrabajador::where('activo', 1)->orderBy('tipo')->get();
        $sedes          = Sede::where('activo', 1)->orderBy('nombre')->get();

        // Obtener personas que están activas
        $personas = Persona::where('activo', 1)
            ->orderBy('nombre')
            ->orderBy('ap_paterno')
            ->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $trabajadores,
            'admin_institucional.trabajadores.partials.tabla',
            compact('trabajadores'),
            'trabajadores'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('admin_institucional.trabajadores.index', compact(
            'trabajadores',
            'departamentos',
            'puestos',
            'tiposTrabajador',
            'sedes',
            'personas',
            'buscar',
            'idDepartamento',
            'idPuesto',
            'idTipoTrabajador',
            'idSede'
        ));
    }

    /**
     * Verifica duplicados en tiempo real vía AJAX (número de empleado o persona asignada).
     */
    public function verificar(Request $request)
    {
        $numEmpleado = trim($request->get('num_empleado', ''));
        $idPersona   = $request->get('id_persona', '');
        $ignoreId    = $request->get('ignore_id', null);

        $numQuery = Trabajador::whereRaw('LOWER(num_empleado) = ?', [strtolower($numEmpleado)]);
        if ($ignoreId) {
            $numQuery->where('id', '!=', $ignoreId);
        }
        $existeNum = !empty($numEmpleado) && $numQuery->exists();

        $personaQuery = Trabajador::where('id_persona', $idPersona);
        if ($ignoreId) {
            $personaQuery->where('id', '!=', $ignoreId);
        }
        $existePersona = !empty($idPersona) && $personaQuery->exists();

        return response()->json([
            'existe_num'     => $existeNum,
            'existe_persona' => $existePersona,
        ]);
    }

    /**
     * Guarda un nuevo trabajador.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'num_empleado'       => 'required|string|max:50',
            'id_persona'         => 'required|integer|exists:personas,id',
            'id_sede'            => 'required|integer|exists:sedes,id',
            'id_departamento'    => 'required|integer|exists:departamentos,id',
            'id_puesto'          => 'required|integer|exists:puestos,id',
            'id_tipo_trabajador' => 'required|integer|exists:tipo_trabajador,id',
            'fecha_ingreso'      => 'required|date',
        ], [
            'num_empleado.required'       => 'El número de empleado es obligatorio.',
            'id_persona.required'         => 'Debe seleccionar una persona.',
            'id_sede.required'            => 'Debe seleccionar una sede.',
            'id_departamento.required'    => 'Debe seleccionar un departamento.',
            'id_puesto.required'          => 'Debe seleccionar un puesto.',
            'id_tipo_trabajador.required' => 'Debe seleccionar un tipo de trabajador.',
            'fecha_ingreso.required'      => 'La fecha de ingreso es obligatoria.',
        ]);

        // Verificar duplicado de número de empleado
        $existeNum = Trabajador::whereRaw(
            'LOWER(num_empleado) = ?',
            [strtolower(trim($request->num_empleado))]
        )->exists();

        if ($existeNum) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['num_empleado' => 'Este número de empleado ya está asignado a otro trabajador.'])
                ->with('hasFormErrors', true);
        }

        // Verificar que la persona no tenga ya un registro de trabajador
        $existePersona = Trabajador::where('id_persona', $request->id_persona)->exists();
        if ($existePersona) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['id_persona' => 'Esta persona ya tiene un expediente de trabajador asignado.'])
                ->with('hasFormErrors', true);
        }

        Trabajador::create([
            'num_empleado'       => trim($request->num_empleado),
            'id_persona'         => $request->id_persona,
            'id_sede'            => $request->id_sede,
            'id_departamento'    => $request->id_departamento,
            'id_puesto'          => $request->id_puesto,
            'id_tipo_trabajador' => $request->id_tipo_trabajador,
            'fecha_ingreso'      => $request->fecha_ingreso,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'activo'             => 1,
            'usuario'            => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('trabajadores.index')
            ->with('exitog', 'El trabajador se ha registrado correctamente.');
    }

    /**
     * Retorna datos JSON para el modal de edición.
     */
    public function editar($id)
    {
        $trabajador = Trabajador::with(['persona', 'sede', 'departamento', 'puesto', 'tipoTrabajador'])->findOrFail($id);

        return response()->json([
            'success'    => true,
            'trabajador' => $trabajador,
        ]);
    }

    /**
     * Actualiza los datos de un trabajador.
     */
    public function actualizar(Request $request, $id)
    {
        $trabajador = Trabajador::findOrFail($id);

        $request->validate([
            'num_empleado'       => 'required|string|max:50',
            'id_persona'         => 'required|integer|exists:personas,id',
            'id_sede'            => 'required|integer|exists:sedes,id',
            'id_departamento'    => 'required|integer|exists:departamentos,id',
            'id_puesto'          => 'required|integer|exists:puestos,id',
            'id_tipo_trabajador' => 'required|integer|exists:tipo_trabajador,id',
            'fecha_ingreso'      => 'required|date',
        ], [
            'num_empleado.required'       => 'El número de empleado es obligatorio.',
            'id_persona.required'         => 'Debe seleccionar una persona.',
            'id_sede.required'            => 'Debe seleccionar una sede.',
            'id_departamento.required'    => 'Debe seleccionar un departamento.',
            'id_puesto.required'          => 'Debe seleccionar un puesto.',
            'id_tipo_trabajador.required' => 'Debe seleccionar un tipo de trabajador.',
            'fecha_ingreso.required'      => 'La fecha de ingreso es obligatoria.',
        ]);

        // Verificar duplicado de número de empleado excluyendo el actual
        $existeNum = Trabajador::where('id', '!=', $id)
            ->whereRaw('LOWER(num_empleado) = ?', [strtolower(trim($request->num_empleado))])
            ->exists();

        if ($existeNum) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['num_empleado' => 'Este número de empleado ya pertenece a otro trabajador.'])
                ->with('hasEditFormErrors', $id);
        }

        // Verificar duplicado de persona excluyendo el actual
        $existePersona = Trabajador::where('id', '!=', $id)
            ->where('id_persona', $request->id_persona)
            ->exists();

        if ($existePersona) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['id_persona' => 'Esta persona ya está asignada a otro expediente de trabajador.'])
                ->with('hasEditFormErrors', $id);
        }

        $trabajador->update([
            'num_empleado'       => trim($request->num_empleado),
            'id_persona'         => $request->id_persona,
            'id_sede'            => $request->id_sede,
            'id_departamento'    => $request->id_departamento,
            'id_puesto'          => $request->id_puesto,
            'id_tipo_trabajador' => $request->id_tipo_trabajador,
            'fecha_ingreso'      => $request->fecha_ingreso,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'usuario'            => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('trabajadores.index')
            ->with('exitog', 'La información del trabajador se actualizó correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo mediante petición PATCH AJAX.
     */
    public function cambiarStatus($id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $trabajador->activo = $trabajador->activo == 1 ? 0 : 1;
        $trabajador->fecha = now()->toDateString();
        $trabajador->hora = now()->toTimeString();
        $trabajador->usuario = Auth::id() ?? 1;
        $trabajador->save();

        return response()->json([
            'success' => true,
            'activo'  => $trabajador->activo,
            'mensaje' => 'Estado del trabajador actualizado con éxito.',
        ]);
    }

    /**
     * Muestra la vista de opciones de reportes.
     */
    public function reportes(Request $request)
    {
        $departamentos   = Departamento::where('activo', 1)->orderBy('nombre')->get();
        $puestos         = Puesto::where('activo', 1)->orderBy('puesto')->get();
        $tiposTrabajador = TipoTrabajador::where('activo', 1)->orderBy('tipo')->get();
        $sedes           = Sede::where('activo', 1)->orderBy('nombre')->get();

        return view('admin_institucional.trabajadores.analitica.reportes.index', compact(
            'departamentos',
            'puestos',
            'tiposTrabajador',
            'sedes'
        ));
    }

    /**
     * Genera el reporte de impresión de trabajadores extendiendo reporte_base.
     */
    public function imprimir(Request $request)
    {
        $buscar          = $request->get('buscar', '');
        $idDepartamento  = $request->get('id_departamento', '');
        $idPuesto        = $request->get('id_puesto', '');
        $idTipoTrabajador= $request->get('id_tipo_trabajador', '');
        $idSede          = $request->get('id_sede', '');
        $status          = $request->get('status', '');

        $query = Trabajador::with(['persona', 'sede', 'departamento', 'puesto', 'tipoTrabajador'])
            ->orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('num_empleado', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('persona', function ($pq) use ($buscar) {
                      $pq->where('nombre', 'LIKE', "%{$buscar}%")
                         ->orWhere('ap_paterno', 'LIKE', "%{$buscar}%")
                         ->orWhere('ap_materno', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if (!empty($idDepartamento)) {
            $query->where('id_departamento', $idDepartamento);
        }

        if (!empty($idPuesto)) {
            $query->where('id_puesto', $idPuesto);
        }

        if (!empty($idTipoTrabajador)) {
            $query->where('id_tipo_trabajador', $idTipoTrabajador);
        }

        if (!empty($idSede)) {
            $query->where('id_sede', $idSede);
        }

        if ($status !== '' && $status !== null) {
            $query->where('activo', $status);
        }

        $trabajadores = $query->get();

        return view('admin_institucional.trabajadores.analitica.reportes.impresion', compact('trabajadores'));
    }

    /**
     * Muestra las gráficas estadísticas del módulo de trabajadores con Chart.js.
     */
    public function graficas()
    {
        $totalActivos = Trabajador::where('activo', 1)->count();
        $totalInactivos = Trabajador::where('activo', 0)->count();

        // Top departamentos por cantidad de trabajadores
        $porDepartamento = Trabajador::select('id_departamento', DB::raw('count(*) as total'))
            ->groupBy('id_departamento')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $depto = Departamento::find($item->id_departamento);
                return [
                    'label' => $depto ? $depto->nombre : 'Sin asignación',
                    'total' => $item->total,
                ];
            });

        // Top puestos por cantidad de trabajadores
        $porPuesto = Trabajador::select('id_puesto', DB::raw('count(*) as total'))
            ->groupBy('id_puesto')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $puesto = Puesto::find($item->id_puesto);
                return [
                    'label' => $puesto ? $puesto->puesto : 'Sin asignación',
                    'total' => $item->total,
                ];
            });

        // Distribución por Tipo de Trabajador
        $porTipo = Trabajador::select('id_tipo_trabajador', DB::raw('count(*) as total'))
            ->groupBy('id_tipo_trabajador')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $tipo = TipoTrabajador::find($item->id_tipo_trabajador);
                return [
                    'label' => $tipo ? $tipo->tipo : 'Sin asignación',
                    'total' => $item->total,
                ];
            });

        // Distribución por Sede
        $porSede = Trabajador::select('id_sede', DB::raw('count(*) as total'))
            ->groupBy('id_sede')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $sede = Sede::find($item->id_sede);
                return [
                    'label' => $sede ? $sede->nombre : 'Sin asignación',
                    'total' => $item->total,
                ];
            });

        return view('admin_institucional.trabajadores.analitica.graficas', compact(
            'totalActivos',
            'totalInactivos',
            'porDepartamento',
            'porPuesto',
            'porTipo',
            'porSede'
        ));
    }
}
