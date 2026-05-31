<?php

namespace App\Http\Controllers\Pacientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PacienteRx;
use App\Models\EstudioRx;
use App\Models\MedicoRx;
use App\Models\EspecialidadRx;

class RxController extends Controller
{
    /**
     * Cargar la vista principal o retornar JSON con pacientes si es AJAX
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $query = PacienteRx::where('activo', 1);

            if ($q = $request->query('q')) {
                $query->where(function ($query) use ($q) {
                    $query->where('nombre', 'like', "%{$q}%")
                          ->orWhere('ap_paterno', 'like', "%{$q}%")
                          ->orWhere('ap_materno', 'like', "%{$q}%")
                          ->orWhere('nhc_hgl', 'like', "%{$q}%")
                          ->orWhere('rfc', 'like', "%{$q}%");
                });
            }

            $pacientes = $query->orderBy('id_paciente', 'desc')->paginate(10);
            return response()->json($pacientes);
        }

        // Cargar médicos y especialidades activos para los selectores del modal de estudios
        $medicos = MedicoRx::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $especialidades = EspecialidadRx::where('activo', 1)->orderBy('nombre', 'asc')->get();

        return view('pacientes.index', compact('medicos', 'especialidades'));
    }

    /**
     * Retorna lista de estudios paginada y filtrada en JSON
     */
    public function estudios(Request $request)
    {
        $query = EstudioRx::with('medicoRx')->where('activo', 1);

        if ($q = $request->query('q')) {
            $query->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                      ->orWhere('ap_paterno', 'like', "%{$q}%")
                      ->orWhere('ap_materno', 'like', "%{$q}%")
                      ->orWhere('nhc', 'like', "%{$q}%")
                      ->orWhere('especificado', 'like', "%{$q}%");
            });
        }

        $estudios = $query->orderBy('id_estudios', 'desc')->paginate(10);
        return response()->json($estudios);
    }

    /**
     * Ver detalles de un paciente específico
     */
    public function verPaciente($id)
    {
        $paciente = PacienteRx::findOrFail($id);
        return response()->json($paciente);
    }

    /**
     * Guardar nuevo paciente
     */
    public function guardarPaciente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'sexo' => 'required|in:M,F,O',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'domicilio' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'homoclave' => 'nullable|string|max:5',
            'nhc_hgl' => 'nullable|string|max:255',
            'sp' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $data = $request->only([
            'nombre', 'ap_paterno', 'ap_materno', 'sexo', 'fecha_nacimiento',
            'telefono', 'domicilio', 'rfc', 'homoclave', 'nhc_hgl', 'sp'
        ]);

        $data['tiene_nhc'] = $request->has('tiene_nhc') ? 1 : 0;
        $data['tiene_sp'] = $request->has('tiene_sp') ? 1 : 0;

        if ($data['tiene_nhc'] == 0) {
            $data['nhc_hgl'] = null;
        }
        if ($data['tiene_sp'] == 0) {
            $data['sp'] = null;
        }

        $data['fecha_registro'] = now()->toDateString();
        $data['hora_registro'] = now()->toTimeString();
        $data['activo'] = 1;
        $data['usuario'] = auth()->id();

        PacienteRx::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Paciente registrado con éxito.'
        ]);
    }

    /**
     * Actualizar datos del paciente
     */
    public function actualizarPaciente(Request $request, $id)
    {
        $paciente = PacienteRx::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'sexo' => 'required|in:M,F,O',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'domicilio' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'homoclave' => 'nullable|string|max:5',
            'nhc_hgl' => 'nullable|string|max:255',
            'sp' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $data = $request->only([
            'nombre', 'ap_paterno', 'ap_materno', 'sexo', 'fecha_nacimiento',
            'telefono', 'domicilio', 'rfc', 'homoclave', 'nhc_hgl', 'sp'
        ]);

        $data['tiene_nhc'] = $request->has('tiene_nhc') ? 1 : 0;
        $data['tiene_sp'] = $request->has('tiene_sp') ? 1 : 0;

        if ($data['tiene_nhc'] == 0) {
            $data['nhc_hgl'] = null;
        }
        if ($data['tiene_sp'] == 0) {
            $data['sp'] = null;
        }

        $paciente->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Paciente actualizado con éxito.'
        ]);
    }

    /**
     * Eliminación lógica de paciente
     */
    public function eliminarPaciente($id)
    {
        $paciente = PacienteRx::findOrFail($id);
        $paciente->update(['activo' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Paciente eliminado con éxito de los expedientes.'
        ]);
    }

    /**
     * Ver detalles de un estudio específico
     */
    public function verEstudio($id)
    {
        $estudio = EstudioRx::with(['medicoRx', 'especialidadRx', 'creador'])
            ->findOrFail($id);

        return response()->json($estudio);
    }

    /**
     * Guardar nuevo estudio
     */
    public function guardarEstudio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_estudio' => 'required|date',
            'hgl' => 'required|string|max:255',
            'especialidad' => 'required|exists:especialidad_rx,id_especialidad',
            'medico' => 'required|exists:medicos_rx,id_medicos',
            'total_cds' => 'nullable|integer|min:0',
            'especificado' => 'nullable|string',
            'otros_datos' => 'nullable|string',
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'nacimiento' => 'nullable|date',
            'sexo' => 'required|in:M,F,O',
            'nhc' => 'nullable|string|max:255',
            'sp' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $data = $request->only([
            'nhc', 'nombre', 'ap_paterno', 'ap_materno', 'nacimiento', 'sexo',
            'fecha_estudio', 'sp', 'hgl', 'especificado', 'total_cds',
            'especialidad', 'medico', 'otros_datos'
        ]);

        $data['craneo'] = $request->has('craneo') ? 1 : 0;
        $data['tx'] = $request->has('tx') ? 1 : 0;
        $data['abd'] = $request->has('abd') ? 1 : 0;
        $data['col'] = $request->has('col') ? 1 : 0;
        $data['m_sup'] = $request->has('m_sup') ? 1 : 0;
        $data['m_inf'] = $request->has('m_inf') ? 1 : 0;
        $data['contraste'] = $request->has('contraste') ? 1 : 0;

        // Calcular total de estudios (regiones anatómicas seleccionadas)
        $data['total_estudios'] = $data['craneo'] + $data['tx'] + $data['abd'] + $data['col'] + $data['m_sup'] + $data['m_inf'] + $data['contraste'];

        // Calcular edad
        if (!empty($data['nacimiento'])) {
            try {
                $birthDate = \Carbon\Carbon::parse($data['nacimiento']);
                $studyDate = \Carbon\Carbon::parse($data['fecha_estudio']);
                $data['edad'] = $birthDate->diffInYears($studyDate);
            } catch (\Exception $e) {
                $data['edad'] = 0;
            }
        } else {
            $data['edad'] = 0;
        }

        $data['fecha_registro'] = now()->toDateString();
        $data['hora_registro'] = now()->toTimeString();
        $data['activo'] = 1;
        $data['usuario'] = auth()->id();

        EstudioRx::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Estudio de radiología registrado con éxito.'
        ]);
    }

    /**
     * Actualizar datos del estudio
     */
    public function actualizarEstudio(Request $request, $id)
    {
        $estudio = EstudioRx::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'fecha_estudio' => 'required|date',
            'hgl' => 'required|string|max:255',
            'especialidad' => 'required|exists:especialidad_rx,id_especialidad',
            'medico' => 'required|exists:medicos_rx,id_medicos',
            'total_cds' => 'nullable|integer|min:0',
            'especificado' => 'nullable|string',
            'otros_datos' => 'nullable|string',
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'nacimiento' => 'nullable|date',
            'sexo' => 'required|in:M,F,O',
            'nhc' => 'nullable|string|max:255',
            'sp' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $data = $request->only([
            'nhc', 'nombre', 'ap_paterno', 'ap_materno', 'nacimiento', 'sexo',
            'fecha_estudio', 'sp', 'hgl', 'especificado', 'total_cds',
            'especialidad', 'medico', 'otros_datos'
        ]);

        $data['craneo'] = $request->has('craneo') ? 1 : 0;
        $data['tx'] = $request->has('tx') ? 1 : 0;
        $data['abd'] = $request->has('abd') ? 1 : 0;
        $data['col'] = $request->has('col') ? 1 : 0;
        $data['m_sup'] = $request->has('m_sup') ? 1 : 0;
        $data['m_inf'] = $request->has('m_inf') ? 1 : 0;
        $data['contraste'] = $request->has('contraste') ? 1 : 0;

        // Recalcular total de estudios
        $data['total_estudios'] = $data['craneo'] + $data['tx'] + $data['abd'] + $data['col'] + $data['m_sup'] + $data['m_inf'] + $data['contraste'];

        // Calcular edad
        if (!empty($data['nacimiento'])) {
            try {
                $birthDate = \Carbon\Carbon::parse($data['nacimiento']);
                $studyDate = \Carbon\Carbon::parse($data['fecha_estudio']);
                $data['edad'] = $birthDate->diffInYears($studyDate);
            } catch (\Exception $e) {
                $data['edad'] = 0;
            }
        } else {
            $data['edad'] = 0;
        }

        $estudio->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Estudio de radiología actualizado con éxito.'
        ]);
    }

    /**
     * Eliminación lógica de estudio
     */
    public function eliminarEstudio($id)
    {
        $estudio = EstudioRx::findOrFail($id);
        $estudio->update(['activo' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Estudio de radiología eliminado con éxito.'
        ]);
    }
}
