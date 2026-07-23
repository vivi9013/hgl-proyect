<?php

namespace App\Http\Controllers\Pacientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EspecialidadRx;
use App\Models\EstudioRx;

class RxEspecialidadController extends Controller
{
    /**
     * Listado principal y respuesta AJAX para la tabla
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $query = EspecialidadRx::query();

            if ($q = $request->query('q')) {
                $query->where(function ($query) use ($q) {
                    $query->where('nombre', 'like', "%{$q}%")
                          ->orWhere('abreviatura', 'like', "%{$q}%");
                });
            }

            $especialidades = $query->orderBy('id_especialidad', 'desc')->paginate(10);
            return response()->json($especialidades);
        }

        return view('pacientes.especialidades.index');
    }

    /**
     * Guardar especialidad
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:especialidad_rx,nombre',
            'abreviatura' => 'required|string|max:3|unique:especialidad_rx,abreviatura',
        ], [
            'nombre.unique' => 'La especialidad ya existe.',
            'abreviatura.unique' => 'La abreviatura ya está registrada.',
            'abreviatura.max' => 'La abreviatura debe ser de máximo 3 caracteres.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'nombre' => trim($request->get('nombre')),
            'abreviatura' => strtoupper(trim($request->get('abreviatura'))),
            'fecha_registro' => now()->toDateString(),
            'hora_registro' => now()->toTimeString(),
            'activo' => 1,
            'usuario' => auth()->id()
        ];

        EspecialidadRx::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Especialidad registrada con éxito.'
        ]);
    }

    /**
     * Ver datos de una especialidad específica (para edición)
     */
    public function edit($id)
    {
        $especialidad = EspecialidadRx::findOrFail($id);
        return response()->json($especialidad);
    }

    /**
     * Actualizar especialidad
     */
    public function update(Request $request, $id)
    {
        $especialidad = EspecialidadRx::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:especialidad_rx,nombre,' . $id . ',id_especialidad',
            'abreviatura' => 'required|string|max:3|unique:especialidad_rx,abreviatura,' . $id . ',id_especialidad',
        ], [
            'nombre.unique' => 'La especialidad ya existe.',
            'abreviatura.unique' => 'La abreviatura ya está registrada.',
            'abreviatura.max' => 'La abreviatura debe ser de máximo 3 caracteres.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $especialidad->update([
            'nombre' => trim($request->get('nombre')),
            'abreviatura' => strtoupper(trim($request->get('abreviatura'))),
            'fecha_registro' => now()->toDateString(),
            'hora_registro' => now()->toTimeString(),
            'usuario' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Especialidad actualizada con éxito.'
        ]);
    }

    /**
     * Alternar estado (activo / inactivo)
     */
    public function toggleStatus($id)
    {
        $especialidad = EspecialidadRx::findOrFail($id);
        $nuevoEstado = $especialidad->activo == 1 ? 0 : 1;

        $especialidad->update([
            'activo' => $nuevoEstado,
            'fecha_registro' => now()->toDateString(),
            'hora_registro' => now()->toTimeString(),
            'usuario' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'status' => $nuevoEstado,
            'message' => $nuevoEstado == 1 ? 'Especialidad activada.' : 'Especialidad desactivada.'
        ]);
    }

    /**
     * Hub de reportes
     */
    public function reportes()
    {
        return view('pacientes.especialidades.reportes.index');
    }

    /**
     * Impresión de listado oficial PDF
     */
    public function imprimir()
    {
        $especialidades = EspecialidadRx::orderBy('nombre', 'asc')->get();

        return view('pacientes.especialidades.reportes.impresion', compact('especialidades'));
    }

    /**
     * Gráficas interactiva de estudios por especialidad
     */
    public function graficas()
    {
        // Consultar cantidad de estudios por cada especialidad activa
        $stats = EspecialidadRx::where('especialidad_rx.activo', 1)
            ->leftJoin('estudios_rx', 'especialidad_rx.id_especialidad', '=', 'estudios_rx.especialidad')
            ->select('especialidad_rx.nombre', DB::raw('count(estudios_rx.id_estudios) as total_estudios'))
            ->groupBy('especialidad_rx.id_especialidad', 'especialidad_rx.nombre')
            ->orderBy('especialidad_rx.nombre', 'asc')
            ->get();

        return view('pacientes.especialidades.analitica.graficas', compact('stats'));
    }
}
