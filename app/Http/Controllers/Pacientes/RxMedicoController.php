<?php

namespace App\Http\Controllers\Pacientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\MedicoRx;

class RxMedicoController extends Controller
{
    /**
     * Listado principal y respuesta AJAX para la tabla
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $query = MedicoRx::query();

            if ($q = $request->query('q')) {
                $query->where(function ($query) use ($q) {
                    $query->where('nombre', 'like', "%{$q}%")
                          ->orWhere('abreviatura', 'like', "%{$q}%");
                });
            }

            $medicos = $query->orderBy('id_medicos', 'desc')->paginate(10);
            return response()->json($medicos);
        }

        return view('pacientes.medicos.index');
    }

    /**
     * Guardar médico
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:medicos_rx,nombre',
            'abreviatura' => 'required|string|max:4|unique:medicos_rx,abreviatura',
        ], [
            'nombre.unique' => 'El médico ya existe.',
            'abreviatura.unique' => 'La abreviatura ya está registrada.',
            'abreviatura.max' => 'La abreviatura debe ser de máximo 4 caracteres.'
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

        MedicoRx::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Médico registrado con éxito.'
        ]);
    }

    /**
     * Ver datos de un médico específico (para edición)
     */
    public function edit($id)
    {
        $medico = MedicoRx::findOrFail($id);
        return response()->json($medico);
    }

    /**
     * Actualizar médico
     */
    public function update(Request $request, $id)
    {
        $medico = MedicoRx::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:medicos_rx,nombre,' . $id . ',id_medicos',
            'abreviatura' => 'required|string|max:4|unique:medicos_rx,abreviatura,' . $id . ',id_medicos',
        ], [
            'nombre.unique' => 'El médico ya existe.',
            'abreviatura.unique' => 'La abreviatura ya está registrada.',
            'abreviatura.max' => 'La abreviatura debe ser de máximo 4 caracteres.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $medico->update([
            'nombre' => trim($request->get('nombre')),
            'abreviatura' => strtoupper(trim($request->get('abreviatura'))),
            'fecha_registro' => now()->toDateString(),
            'hora_registro' => now()->toTimeString(),
            'usuario' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Médico actualizado con éxito.'
        ]);
    }

    /**
     * Alternar estado (activo / inactivo)
     */
    public function toggleStatus($id)
    {
        $medico = MedicoRx::findOrFail($id);
        $nuevoEstado = $medico->activo == 1 ? 0 : 1;

        $medico->update([
            'activo' => $nuevoEstado,
            'fecha_registro' => now()->toDateString(),
            'hora_registro' => now()->toTimeString(),
            'usuario' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'status' => $nuevoEstado,
            'message' => $nuevoEstado == 1 ? 'Médico activado.' : 'Médico desactivado.'
        ]);
    }

    /**
     * Hub de reportes
     */
    public function reportes()
    {
        return view('pacientes.medicos.reportes.index');
    }

    /**
     * Impresión de listado oficial PDF
     */
    public function imprimir()
    {
        $medicos = MedicoRx::orderBy('nombre', 'asc')->get();

        return view('pacientes.medicos.reportes.impresion', compact('medicos'));
    }

    /**
     * Gráficas interactivas de estudios por médico
     */
    public function graficas()
    {
        // Consultar cantidad de estudios por cada médico activo
        $stats = MedicoRx::where('medicos_rx.activo', 1)
            ->leftJoin('estudios_rx', 'medicos_rx.id_medicos', '=', 'estudios_rx.medico')
            ->select('medicos_rx.nombre', DB::raw('count(estudios_rx.id_estudios) as total_estudios'))
            ->groupBy('medicos_rx.id_medicos', 'medicos_rx.nombre')
            ->orderBy('medicos_rx.nombre', 'asc')
            ->get();

        return view('pacientes.medicos.analitica.graficas', compact('stats'));
    }
}
