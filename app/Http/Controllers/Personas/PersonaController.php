<?php

namespace App\Http\Controllers\Personas;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    /**
     * Muestra el catálogo de personas con búsqueda y paginación AJAX.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = Persona::orderBy('id', 'desc');

        if (!empty($buscar)) {
            $b = trim($buscar);
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('ap_paterno', 'like', "%{$b}%")
                  ->orWhere('ap_materno', 'like', "%{$b}%")
                  ->orWhere('curp', 'like', "%{$b}%")
                  ->orWhere('rfc', 'like', "%{$b}%")
                  ->orWhere('e_mail', 'like', "%{$b}%");
            });
        }

        $personas = $query->paginate(10);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('admin_sistema.personas.partials.tabla', compact('personas'))->render(),
                'links' => $personas->links('pagination::bootstrap-4')->render(),
                'total' => $personas->total(),
                'info'  => 'Mostrando ' . ($personas->firstItem() ?? 0) . ' a ' . ($personas->lastItem() ?? 0) . ' de ' . $personas->total() . ' registros',
            ]);
        }

        return view('admin_sistema.personas.index', compact('personas'));
    }

    /**
     * Guarda una nueva persona en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'ap_paterno' => 'required|string|max:100',
            'ap_materno' => 'required|string|max:100',
            'fecha_nac'  => 'required|date',
            'sexo'       => 'required|in:M,F',
            'ecivil'     => 'required|string|max:50',
            'telefono'   => 'required|string|max:20',
            'rfc'        => 'required|string|max:13',
            'curp'       => 'required|string|max:18',
            'e_mail'     => 'required|email|max:150',
            'colonia'    => 'required|string|max:100',
            'calle'      => 'required|string|max:150',
            'numero'     => 'required|string|max:10',
            'estado'     => 'required|string|max:100',
            'municipio'  => 'required|string|max:100',
        ]);

        Persona::create([
            'nombre'     => trim($request->nombre),
            'ap_paterno' => trim($request->ap_paterno),
            'ap_materno' => trim($request->ap_materno),
            'fecha_nac'  => $request->fecha_nac,
            'sexo'       => $request->sexo,
            'ecivil'     => $request->ecivil,
            'rfc'        => strtoupper(trim($request->rfc)),
            'curp'       => strtoupper(trim($request->curp)),
            'e_mail'     => trim($request->e_mail),
            'telefono'   => trim($request->telefono),
            'colonia'    => trim($request->colonia),
            'calle'      => trim($request->calle),
            'numero'     => trim($request->numero),
            'estado'     => trim($request->estado),
            'municipio'  => trim($request->municipio),
            'estudiante' => $request->has('estudiante') ? 1 : 0,
            'fecha'      => now()->toDateString(),
            'hora'       => now()->toTimeString(),
            'usuario'    => Auth::id() ?? 1,
            'activo'     => 1,
            'id_sede'    => 1,
        ]);

        return redirect()
            ->route('personas.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de una persona.
     */
    public function editar($id)
    {
        $persona = Persona::findOrFail($id);
        $estados  = DB::table('estados')->orderBy('estado')->pluck('estado', 'estado');
        $municipios = DB::table('municipios')
            ->where('estado', $persona->estado)
            ->orderBy('municipio')
            ->pluck('municipio', 'municipio');

        return view('admin_sistema.personas.editar', compact('persona', 'estados', 'municipios'));
    }

    /**
     * Actualiza los datos de la persona.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'     => 'required|string|max:100',
            'ap_paterno' => 'required|string|max:100',
            'ap_materno' => 'required|string|max:100',
            'fecha_nac'  => 'required|date',
            'sexo'       => 'required|in:M,F',
            'ecivil'     => 'required|string|max:50',
            'telefono'   => 'required|string|max:20',
            'rfc'        => 'required|string|max:13',
            'curp'       => 'required|string|max:18',
            'e_mail'     => 'required|email|max:150',
            'colonia'    => 'required|string|max:100',
            'calle'      => 'required|string|max:150',
            'numero'     => 'required|string|max:10',
            'estado'     => 'required|string|max:100',
            'municipio'  => 'required|string|max:100',
        ]);

        $persona = Persona::findOrFail($id);

        $persona->update([
            'nombre'     => trim($request->nombre),
            'ap_paterno' => trim($request->ap_paterno),
            'ap_materno' => trim($request->ap_materno),
            'fecha_nac'  => $request->fecha_nac,
            'sexo'       => $request->sexo,
            'ecivil'     => $request->ecivil,
            'rfc'        => strtoupper(trim($request->rfc)),
            'curp'       => strtoupper(trim($request->curp)),
            'e_mail'     => trim($request->e_mail),
            'telefono'   => trim($request->telefono),
            'colonia'    => trim($request->colonia),
            'calle'      => trim($request->calle),
            'numero'     => trim($request->numero),
            'estado'     => trim($request->estado),
            'municipio'  => trim($request->municipio),
            'fecha'      => now()->toDateString(),
            'hora'       => now()->toTimeString(),
            'usuario'    => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('personas.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de la persona (AJAX).
     */
    public function cambiarStatus($id)
    {
        $persona = Persona::findOrFail($id);
        $persona->activo = ($persona->activo == 1) ? 0 : 1;
        $persona->fecha  = now()->toDateString();
        $persona->hora   = now()->toTimeString();
        $persona->usuario = Auth::id() ?? 1;
        $persona->save();

        return response()->json([
            'success' => true,
            'activo'  => $persona->activo,
            'message' => 'El estado se ha actualizado correctamente.',
        ]);
    }

    /**
     * Alterna el rol de estudiante de la persona (AJAX).
     */
    public function cambiarEstudiante($id)
    {
        $persona = Persona::findOrFail($id);
        $persona->estudiante = ($persona->estudiante == 1) ? 0 : 1;
        $persona->fecha  = now()->toDateString();
        $persona->hora   = now()->toTimeString();
        $persona->usuario = Auth::id() ?? 1;
        $persona->save();

        return response()->json([
            'success'    => true,
            'estudiante' => $persona->estudiante,
            'message'    => 'El rol de estudiante se ha actualizado correctamente.',
        ]);
    }

    /**
     * AJAX: Retorna los municipios de un estado dado.
     * Si estado=__estados__, retorna todos los estados disponibles.
     */
    public function municipios(Request $request)
    {
        $estado = $request->query('estado');

        // Caso especial: cargar lista de estados para combos
        if ($estado === '__estados__') {
            $estados = DB::table('estados')->orderBy('estado')->pluck('estado', 'estado');
            return response()->json($estados);
        }

        $municipios = DB::table('municipios')
            ->where('estado', $estado)
            ->orderBy('municipio')
            ->pluck('municipio', 'municipio');

        return response()->json($municipios);
    }

    /**
     * Muestra la vista de reportes.
     */
    public function reportes()
    {
        $estados = DB::table('estados')->orderBy('estado')->pluck('estado', 'estado');
        return view('admin_sistema.personas.analitica.reportes.index', compact('estados'));
    }

    /**
     * Genera la vista imprimible del reporte de personas.
     */
    public function imprimir(Request $request)
    {
        $sexo = $request->get('sexo');
        $estado = $request->get('estado');

        $query = Persona::orderBy('ap_paterno')->orderBy('nombre');

        if ($sexo && in_array($sexo, ['M', 'F'])) {
            $query->where('sexo', $sexo);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        $personas = $query->get();
        $estados  = DB::table('estados')->orderBy('estado')->pluck('estado', 'estado');

        return view('admin_sistema.personas.analitica.reportes.impresion', compact('personas', 'estados', 'sexo', 'estado'));
    }

    /**
     * Muestra la vista de analítica con gráficos por género.
     */
    public function graficas()
    {
        // Distribución por sexo
        $porSexo = Persona::select('sexo', DB::raw('COUNT(*) as total'))
            ->where('activo', 1)
            ->groupBy('sexo')
            ->pluck('total', 'sexo');

        // Contadores generales
        $totalActivos    = Persona::where('activo', 1)->count();
        $totalInactivos  = Persona::where('activo', 0)->count();
        $totalEstudiantes = Persona::where('estudiante', 1)->where('activo', 1)->count();

        return view('admin_sistema.personas.analitica.graficas', compact(
            'porSexo',
            'totalActivos',
            'totalInactivos',
            'totalEstudiantes'
        ));
    }
}