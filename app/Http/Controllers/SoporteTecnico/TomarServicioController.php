<?php

namespace App\Http\Controllers\SoporteTecnico;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Mobiliario;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\SoporteArea;
use App\Models\SoporteTecnico\Servicio;
use App\Models\SoporteTecnico\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TomarServicioController extends Controller
{
    /**
     * Obtiene los IDs de áreas asignadas al técnico autenticado.
     * Si es Administrador (perfil 1), tiene acceso a todas las áreas activas.
     */
    private function getAreasAsignadas(): array
    {
        $user = Auth::user();
        $personaId = $user->id_persona;

        // Si es perfil de administrador
        if ($user->id_perfil == 1) {
            return Area::where('activo', 1)->pluck('id')->toArray();
        }

        $areas = SoporteArea::where('id_persona', $personaId)
            ->where('activo', 1)
            ->pluck('id_area')
            ->toArray();

        return $areas;
    }

    /**
     * Obtiene el nombre completo y datos del técnico autenticado.
     */
    private function getDatosTecnico(): array
    {
        $personaId = Auth::user()->id_persona;
        $persona = Persona::find($personaId);

        if (!$persona) {
            return [
                'id_persona'      => $personaId,
                'nombre_completo' => Auth::user()->nombre_usuario,
                'sexo'            => 'M',
            ];
        }

        return [
            'id_persona'      => $persona->id,
            'nombre_completo' => trim($persona->nombre . ' ' . $persona->ap_paterno . ' ' . $persona->ap_materno),
            'sexo'            => $persona->sexo ?? 'M',
        ];
    }

    // ─── 1. BANDEJA DE PENDIENTES — Servicios solicitados por tomar ─────────────
    public function index(Request $request)
    {
        $areasAsignadas = $this->getAreasAsignadas();
        $buscar         = trim($request->get('buscar', ''));
        $areaFiltro     = $request->get('id_area');

        $query = Servicio::with(['area', 'solicitante'])
            ->where('pendiente', 1)
            ->where('proceso', 0)
            ->where('liberado', 0)
            ->where(function ($q) {
                $q->whereNull('estatus_final')
                  ->orWhere('estatus_final', '!=', 'Cancelado');
            });

        // Filtrar por áreas asignadas
        if (empty($areasAsignadas)) {
            $query->whereRaw('1 = 0'); // No tiene áreas asignadas
        } else {
            $query->whereIn('id_area', $areasAsignadas);
        }

        if ($areaFiltro) {
            $query->where('id_area', $areaFiltro);
        }

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('departamento', 'like', "%{$buscar}%")
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"));
            });
        }

        $query->orderByDesc('id');

        $areas = Area::whereIn('id', $areasAsignadas)->orderBy('area')->get();

        // Conteo general para badges
        $totalPendientes = Servicio::where('pendiente', 1)->where('proceso', 0)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();
        $totalEnProceso  = Servicio::where('proceso', 1)->where('terminado', 0)->where('liberado', 0)->where('id_personaServidor', Auth::user()->id_persona)->count();
        $totalPorLiberar = Servicio::where('terminado', 1)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.tomar_servicios.partials.tabla_pendientes', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' solicitudes',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.tomar_servicios.index', compact(
            'servicios', 'areas', 'areasAsignadas', 'totalPendientes', 'totalEnProceso', 'totalPorLiberar'
        ));
    }

    // ─── 2. TOMAR SERVICIO — Asignar servicio al técnico autenticado ────────────
    public function tomar(Request $request, int $id)
    {
        $request->validate([
            'clasificacion_servicio' => 'required|string|max:100',
        ], [
            'clasificacion_servicio.required' => 'Debes clasificar la prioridad o tipo de solicitud.',
        ]);

        $areasAsignadas = $this->getAreasAsignadas();
        $tecnico = $this->getDatosTecnico();
        $ahora = now();

        return DB::transaction(function () use ($id, $areasAsignadas, $tecnico, $request, $ahora) {
            $servicio = Servicio::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar si el técnico tiene permiso en el área
            if (!in_array($servicio->id_area, $areasAsignadas)) {
                return response()->json(['error' => 'No tienes permisos asignados para atender servicios de esta área.'], 403);
            }

            // Verificar si ya fue tomado
            if ($servicio->proceso == 1 || $servicio->terminado == 1 || $servicio->liberado == 1) {
                return response()->json(['error' => 'Este servicio ya fue tomado por otro técnico o ha cambiado de estado.'], 422);
            }

            $servicio->update([
                'proceso'                => 1,
                'id_personaServidor'     => $tecnico['id_persona'],
                'nombre_servidor'        => $tecnico['nombre_completo'],
                'sexo_servidor'          => $tecnico['sexo'],
                'fecha_tomado'           => $ahora->toDateString(),
                'hora_tomado'            => $ahora->toTimeString(),
                'clasificacion_servicio' => $request->clasificacion_servicio,
                'id_uss'                 => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Has tomado el servicio #' . $servicio->id . ' correctamente.',
            ]);
        });
    }

    // ─── 3. MIS SERVICIOS EN PROCESO — Servicios asignados al técnico ───────────
    public function misServicios(Request $request)
    {
        $personaId = Auth::user()->id_persona;
        $buscar    = trim($request->get('buscar', ''));

        $query = Servicio::with(['area', 'solicitante'])
            ->where('id_personaServidor', $personaId)
            ->where('proceso', 1)
            ->where('terminado', 0)
            ->where('liberado', 0)
            ->orderByDesc('id');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('departamento', 'like', "%{$buscar}%")
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"));
            });
        }

        $areasAsignadas = $this->getAreasAsignadas();
        $totalPendientes = Servicio::where('pendiente', 1)->where('proceso', 0)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();
        $totalEnProceso  = Servicio::where('proceso', 1)->where('terminado', 0)->where('liberado', 0)->where('id_personaServidor', $personaId)->count();
        $totalPorLiberar = Servicio::where('terminado', 1)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();

        // Tipos de servicio para el modal de concluir
        $tiposServicio = TipoServicio::where('activo', 1)->orderBy('servicio')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.tomar_servicios.partials.tabla_en_proceso', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' servicios',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.tomar_servicios.mis_servicios', compact(
            'servicios', 'totalPendientes', 'totalEnProceso', 'totalPorLiberar', 'tiposServicio'
        ));
    }

    // ─── 4. OBTENER MOBILIARIO POR ÁREA (AJAX) ──────────────────────────────────
    public function obtenerMobiliarioArea(int $idArea)
    {
        $mobiliario = Mobiliario::with(['tipoMobiliario', 'persona'])
            ->where('id_area', $idArea)
            ->where('activo', 1)
            ->orderBy('inventario')
            ->get()
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'inventario'  => $item->inventario,
                    'descripcion' => $item->descripcion,
                    'marca'       => $item->marca ?? '—',
                    'modelo'      => $item->modelo ?? '—',
                    'serie'       => $item->serie ?? '—',
                    'tipo'        => $item->tipoMobiliario ? $item->tipoMobiliario->tipo : 'Mobiliario/Equipo',
                    'responsable' => $item->persona
                                     ? trim($item->persona->nombre . ' ' . $item->persona->ap_paterno)
                                     : 'Sin responsable asignado',
                ];
            });

        return response()->json($mobiliario);
    }

    // ─── 5. CONCLUIR SERVICIO — Finalizar atención y registrar solución ─────────
    public function concluir(Request $request, int $id)
    {
        $request->validate([
            'accion_realizada' => 'required|string|min:10|max:3000',
            'id_tipo_servicio' => 'nullable|integer',
            'tipo_servicio'    => 'nullable|string|max:150',
            'id_mobiliario'    => 'nullable|integer',
        ], [
            'accion_realizada.required' => 'Debes detallar la acción o solución realizada.',
            'accion_realizada.min'      => 'La descripción de la acción debe tener al menos 10 caracteres.',
        ]);

        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::where('id', $id)->firstOrFail();

        // Validar que pertenezca al técnico o sea admin
        if ($servicio->id_personaServidor != $personaId && Auth::user()->id_perfil != 1) {
            return response()->json(['error' => 'No estás asignado como servidor de este servicio.'], 403);
        }

        if ($servicio->terminado == 1) {
            return response()->json(['error' => 'Este servicio ya se encuentra marcado como terminado.'], 422);
        }

        $ahora = now();

        // Manejo de mobiliario opcional
        $idMob      = null;
        $invMob     = 'Sin equipo específico';
        $descMob    = 'Servicio general';

        if ($request->filled('id_mobiliario') && $request->id_mobiliario > 0) {
            $mob = Mobiliario::find($request->id_mobiliario);
            if ($mob) {
                $idMob   = $mob->id;
                $invMob  = $mob->inventario;
                $descMob = $mob->descripcion . ' - ' . ($mob->marca ?? '') . ' ' . ($mob->modelo ?? '');
            }
        }

        // Tipo de servicio
        $idTipoServ = $request->id_tipo_servicio;
        $nomTipoServ = $request->tipo_servicio;
        if ($idTipoServ && !$nomTipoServ) {
            $tipoObj = TipoServicio::find($idTipoServ);
            $nomTipoServ = $tipoObj ? $tipoObj->servicio : 'Soporte General';
        } elseif (!$idTipoServ && $nomTipoServ) {
            $tipoObj = TipoServicio::where('servicio', 'like', $nomTipoServ)->first();
            $idTipoServ = $tipoObj ? $tipoObj->id : null;
        }

        $servicio->update([
            'accion_realizada'       => trim($request->accion_realizada),
            'id_mobiliario'          => $idMob,
            'inventario'             => $invMob,
            'descripcion_mobiliario' => $descMob,
            'id_tipo_servicio'       => $idTipoServ,
            'tipo_servicio'          => $nomTipoServ ?? 'Soporte General',
            'fecha_termino'          => $ahora->toDateString(),
            'hora_termino'           => $ahora->toTimeString(),
            'terminado'              => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El servicio #' . $servicio->id . ' ha sido concluido exitosamente y pasa a estatus Por Liberar.',
        ]);
    }

    // ─── 6. REASIGNAR / LIBERAR DE PROCESO ──────────────────────────────────────
    public function reasignar(Request $request, int $id)
    {
        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::where('id', $id)->firstOrFail();

        if ($servicio->id_personaServidor != $personaId && Auth::user()->id_perfil != 1) {
            return response()->json(['error' => 'No tienes permiso para liberar la atención de este servicio.'], 403);
        }

        if ($servicio->terminado == 1 || $servicio->liberado == 1) {
            return response()->json(['error' => 'No se puede reasignar un servicio que ya fue terminado o liberado.'], 422);
        }

        $motivo = trim($request->input('motivo', 'Reasignación solicitada por el técnico'));

        $servicio->update([
            'proceso'                => 0,
            'id_personaServidor'     => null,
            'nombre_servidor'        => null,
            'sexo_servidor'          => null,
            'fecha_tomado'           => null,
            'hora_tomado'            => null,
            'clasificacion_servicio' => null,
            'motivo_modificado'      => $motivo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El servicio #' . $id . ' fue devuelto a la bandeja de solicitudes pendientes.',
        ]);
    }

    // ─── 7. SERVICIOS POR LIBERAR — Terminados esperando cierre ─────────────────
    public function porLiberar(Request $request)
    {
        $areasAsignadas = $this->getAreasAsignadas();
        $buscar         = trim($request->get('buscar', ''));

        $query = Servicio::with(['area', 'solicitante', 'servidor'])
            ->where('terminado', 1)
            ->where('liberado', 0);

        if (empty($areasAsignadas)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('id_area', $areasAsignadas);
        }

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('nombre_servidor', 'like', "%{$buscar}%")
                  ->orWhere('departamento', 'like', "%{$buscar}%")
                  ->orWhere('id', 'like', "%{$buscar}%");
            });
        }

        $query->orderByDesc('id');

        $totalPendientes = Servicio::where('pendiente', 1)->where('proceso', 0)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();
        $totalEnProceso  = Servicio::where('proceso', 1)->where('terminado', 0)->where('liberado', 0)->where('id_personaServidor', Auth::user()->id_persona)->count();
        $totalPorLiberar = Servicio::where('terminado', 1)->where('liberado', 0)->whereIn('id_area', $areasAsignadas ?: [0])->count();

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.tomar_servicios.partials.tabla_por_liberar', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' servicios',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.tomar_servicios.por_liberar', compact(
            'servicios', 'totalPendientes', 'totalEnProceso', 'totalPorLiberar'
        ));
    }

    // ─── 8. LIBERAR POR SOPORTE — Cierre definitivo por parte del técnico ────────
    public function liberarSoporte(Request $request, int $id)
    {
        $areasAsignadas = $this->getAreasAsignadas();
        $servicio       = Servicio::where('id', $id)->firstOrFail();

        if (!in_array($servicio->id_area, $areasAsignadas) && Auth::user()->id_perfil != 1) {
            return response()->json(['error' => 'No tienes permisos para liberar este servicio.'], 403);
        }

        if (!$servicio->terminado) {
            return response()->json(['error' => 'El servicio debe estar concluido antes de liberarlo.'], 422);
        }

        $ahora = now();
        $servicio->update([
            'liberado'       => 1,
            'estatus_final'  => 'Liberado',
            'fecha_finaliza' => $ahora->toDateString(),
            'hora_finaliza'  => $ahora->toTimeString(),
            'liberadox'      => 'Soporte',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El servicio #' . $id . ' ha sido liberado por el personal de Soporte Técnico.',
        ]);
    }

    // ─── 9. AJUSTAR FECHAS CON AUDITORÍA ────────────────────────────────────────
    public function ajustarFechas(Request $request, int $id)
    {
        $request->validate([
            'fecha_peticion'    => 'required|date',
            'hora_peticion'     => 'required|string',
            'fecha_tomado'      => 'nullable|date',
            'hora_tomado'       => 'nullable|string',
            'motivo_modificado' => 'required|string|min:5|max:500',
        ], [
            'fecha_peticion.required'    => 'La fecha de petición es obligatoria.',
            'hora_peticion.required'     => 'La hora de petición es obligatoria.',
            'motivo_modificado.required' => 'Debes justificar el motivo del cambio de fechas/horas.',
            'motivo_modificado.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $servicio = Servicio::findOrFail($id);
        $tecnico  = $this->getDatosTecnico();
        $ahora    = now();

        $updateData = [
            'fecha_peticion'    => $request->fecha_peticion,
            'hora_peticion'     => $request->hora_peticion,
            'modificado'        => 1,
            'modificadox'       => $tecnico['nombre_completo'],
            'motivo_modificado' => trim($request->motivo_modificado),
            'fecha_modificado'  => $ahora->toDateString(),
            'hora_modificado'   => $ahora->toTimeString(),
            'id_uss'            => Auth::id(),
        ];

        if ($request->filled('fecha_tomado')) {
            $updateData['fecha_tomado'] = $request->fecha_tomado;
        }
        if ($request->filled('hora_tomado')) {
            $updateData['hora_tomado'] = $request->hora_tomado;
        }

        $servicio->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Las fechas del servicio #' . $id . ' fueron actualizadas con registro de auditoría.',
        ]);
    }

    // ─── 10. HISTORIAL GENERAL DE SOPORTE ───────────────────────────────────────
    public function historial(Request $request)
    {
        $areasAsignadas = $this->getAreasAsignadas();
        $buscar         = trim($request->get('buscar', ''));
        $areaFiltro     = $request->get('id_area');
        $estadoFiltro   = $request->get('estado');

        $query = Servicio::with(['area', 'solicitante', 'servidor', 'mobiliario'])
            ->where(function ($q) {
                $q->where('liberado', 1)
                  ->orWhere('estatus_final', 'Cancelado')
                  ->orWhere('terminado', 1);
            });

        if (empty($areasAsignadas)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('id_area', $areasAsignadas);
        }

        if ($areaFiltro) {
            $query->where('id_area', $areaFiltro);
        }

        if ($estadoFiltro === 'liberados') {
            $query->where('estatus_final', 'Liberado');
        } elseif ($estadoFiltro === 'cancelados') {
            $query->where('estatus_final', 'Cancelado');
        } elseif ($estadoFiltro === 'terminados') {
            $query->where('terminado', 1)->where('liberado', 0);
        }

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('nombre_servidor', 'like', "%{$buscar}%")
                  ->orWhere('departamento', 'like', "%{$buscar}%")
                  ->orWhere('accion_realizada', 'like', "%{$buscar}%")
                  ->orWhere('inventario', 'like', "%{$buscar}%")
                  ->orWhere('id', 'like', "%{$buscar}%");
            });
        }

        $query->orderByDesc('id');

        $areas = Area::whereIn('id', $areasAsignadas)->orderBy('area')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.tomar_servicios.partials.tabla_historial', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' registros',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.tomar_servicios.historial', compact('servicios', 'areas'));
    }

    // ─── 11. HOJA DE SERVICIO OFICIAL IMPRIMIBLE / PDF ──────────────────────────
    public function hojaServicio(int $id)
    {
        $servicio = Servicio::with(['area', 'solicitante', 'servidor', 'mobiliario', 'departamentoRel', 'sedeRel'])
            ->findOrFail($id);

        return view('soporte_tecnico.tomar_servicios.hoja_servicio', compact('servicio'));
    }

    // ─── 12. REPORTES & ANALÍTICA DE SOPORTE ────────────────────────────────────
    public function reportes(Request $request)
    {
        $areasAsignadas = $this->getAreasAsignadas();
        $fechaDesde     = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta     = $request->get('fecha_hasta', now()->toDateString());
        $areaId         = $request->get('id_area');

        $query = Servicio::with(['area', 'servidor'])
            ->whereIn('id_area', $areasAsignadas ?: [0])
            ->whereDate('fecha_peticion', '>=', $fechaDesde)
            ->whereDate('fecha_peticion', '<=', $fechaHasta);

        if ($areaId) {
            $query->where('id_area', $areaId);
        }

        $totalServicios = (clone $query)->count();
        $liberados      = (clone $query)->where('estatus_final', 'Liberado')->count();
        $enProceso      = (clone $query)->where('proceso', 1)->where('terminado', 0)->count();
        $cancelados     = (clone $query)->where('estatus_final', 'Cancelado')->count();
        $pendientes     = (clone $query)->where('pendiente', 1)->where('proceso', 0)->count();

        $servicios = $query->orderByDesc('id')->paginate(20);
        $areas     = Area::whereIn('id', $areasAsignadas)->orderBy('area')->get();

        return view('soporte_tecnico.tomar_servicios.reportes', compact(
            'servicios', 'areas', 'fechaDesde', 'fechaHasta', 'areaId',
            'totalServicios', 'liberados', 'enProceso', 'cancelados', 'pendientes'
        ));
    }
}
