<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use App\Models\Perfil;
use App\Models\Configuracion;
use App\Models\SolicitudReseteoPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Muestra el catálogo principal de usuarios.
     */
    /**
     * Aplica el filtro de búsqueda a la query de usuarios.
     */
    private function aplicarFiltro($query, Request $request)
    {
        $buscar = $request->get('buscar');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('usuarios.nombre_usuario', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhereHas('persona', function ($qp) use ($buscarLimpiado) {
                      $qp->where('nombre', 'like', '%' . $buscarLimpiado . '%')
                         ->orWhere('ap_paterno', 'like', '%' . $buscarLimpiado . '%')
                         ->orWhere('ap_materno', 'like', '%' . $buscarLimpiado . '%');
                  })
                  ->orWhereHas('perfil', function ($qf) use ($buscarLimpiado) {
                      $qf->where('nombre', 'like', '%' . $buscarLimpiado . '%');
                  });
            });
        }

        $perfil = $request->input('perfil');
        if (!empty($perfil)) {
            $perfilArr = is_array($perfil) ? $perfil : explode(',', $perfil);
            $perfilArr = array_filter(array_map('trim', $perfilArr));
            if (!empty($perfilArr)) {
                $query->whereIn('usuarios.id_perfil', $perfilArr);
            }
        }

        return $query;
    }

    /**
     * Muestra el catálogo principal de usuarios.
     */
    public function index(Request $request)
    {
        $query = User::with(['persona', 'perfil'])
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->select('usuarios.*')
            ->orderBy('usuarios.id', 'desc');

        $this->aplicarFiltro($query, $request);

        $usuarios = $query->paginate(10);

        // Si es AJAX, retornamos la respuesta JSON con la tabla renderizada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin_sistema.usuarios.partials.tabla', compact('usuarios'))->render(),
                'links' => $usuarios->links('pagination::bootstrap-4')->render(),
                'total' => $usuarios->total(),
                'info' => "Mostrando " . ($usuarios->firstItem() ?? 0) . " a " . ($usuarios->lastItem() ?? 0) . " de " . $usuarios->total() . " registros"
            ]);
        }

        // Obtener personas activas que NO tienen una cuenta de usuario
        $personasSinUsuario = Persona::where('activo', 1)
            ->whereDoesntHave('usuario')
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombre')
            ->get();

        // Obtener perfiles disponibles
        $perfiles = Perfil::where('activo', 1)->orderBy('nombre')->get();

        // Obtener contraseña por defecto de la configuración general
        $config = Configuracion::first();
        $defaultPassword = $config ? $config->contra : '123456';

        $solicitudesPendientesCount = SolicitudReseteoPassword::where('estado', 'pendiente')->count();

        return view('admin_sistema.usuarios.index', compact('usuarios', 'personasSinUsuario', 'perfiles', 'defaultPassword', 'solicitudesPendientesCount'));
    }

    /**
     * Guarda un nuevo usuario.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'persona' => 'required|exists:personas,id|unique:usuarios,id_persona',
            'nombre'  => 'required|string|max:255|unique:usuarios,nombre_usuario',
            'perfil'  => 'required|exists:perfiles,id',
            'pass'    => 'required|string|min:4',
            'repass'  => 'required|string|same:pass',
            'tema'    => 'required|string',
        ], [
            'persona.unique' => 'Esta persona ya cuenta con un usuario registrado.',
            'nombre.unique'  => 'Este nombre de usuario ya está en uso.',
            'repass.same'    => 'Las contraseñas escritas no coinciden.',
        ]);

        User::create([
            'nombre_usuario'     => trim($request->nombre),
            'contra'             => bcrypt($request->pass),
            'id_persona'         => $request->persona,
            'id_perfil'          => $request->perfil,
            'tema'               => $request->tema,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'activo'             => 1,
            'usuario'            => Auth::id() ?? 1,
            'primera'            => 1,
            'cambiar_contrasena' => 1
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function editar($id)
    {
        $usuario = User::findOrFail($id);
        $perfiles = Perfil::where('activo', 1)->orderBy('nombre')->get();

        return view('admin_sistema.usuarios.editar', compact('usuario', 'perfiles'));
    }

    /**
     * Actualiza el usuario.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:usuarios,nombre_usuario,' . $id,
            'perfil' => 'required|exists:perfiles,id',
            'tema'   => 'required|string',
        ], [
            'nombre.unique' => 'Este nombre de usuario ya está en uso.',
        ]);

        $usuario = User::findOrFail($id);

        $usuario->update([
            'nombre_usuario' => trim($request->nombre),
            'id_perfil'      => $request->perfil,
            'tema'           => $request->tema,
            'fecha'          => now()->toDateString(),
            'hora'           => now()->toTimeString(),
            'usuario'        => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo del usuario.
     */
    public function cambiarStatus($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->activo = ($usuario->activo == 1) ? 0 : 1;
        $usuario->fecha = now()->toDateString();
        $usuario->hora = now()->toTimeString();
        $usuario->usuario = Auth::id() ?? 1;
        $usuario->save();

        return response()->json([
            'success' => true,
            'activo'  => $usuario->activo,
            'message' => 'El estado se ha actualizado correctamente.'
        ]);
    }

    /**
     * Restablece la contraseña de un usuario al valor por defecto de la configuración.
     */
    private function resetearPasswordDefault(User $usuario): void
    {
        $config = Configuracion::first();
        $defaultPassword = $config ? $config->contra : '123456';

        $usuario->contra = bcrypt($defaultPassword);
        $usuario->primera = 1;
        $usuario->fecha = now()->toDateString();
        $usuario->hora = now()->toTimeString();
        $usuario->usuario = Auth::id() ?? 1;
        $usuario->save();
    }

    /**
     * Restablece la contraseña del usuario a la contraseña por defecto.
     */
    public function restablecerPassword($id)
    {
        $usuario = User::findOrFail($id);
        $this->resetearPasswordDefault($usuario);

        return response()->json([
            'success' => true,
            'message' => 'La contraseña se ha restablecido al valor por defecto correctamente.'
        ]);
    }

    /**
     * Devuelve el HTML de la bandeja de solicitudes pendientes (para el modal).
     */
    public function solicitudesPendientes()
    {
        $solicitudes = SolicitudReseteoPassword::with('usuario.persona')
            ->where('estado', 'pendiente')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'html'  => view('admin_sistema.usuarios.partials.solicitudes', compact('solicitudes'))->render(),
            'total' => $solicitudes->count(),
        ]);
    }

    /**
     * Aprueba una solicitud: restablece la contraseña y marca la solicitud como resuelta.
     */
    public function aprobarSolicitud($id)
    {
        $solicitud = SolicitudReseteoPassword::findOrFail($id);

        if ($solicitud->estado !== 'pendiente' || !$solicitud->id_usuario) {
            return response()->json(['success' => false, 'message' => 'Esta solicitud ya no está disponible.']);
        }

        DB::transaction(function () use ($solicitud) {
            $usuario = User::findOrFail($solicitud->id_usuario);
            $this->resetearPasswordDefault($usuario);

            $solicitud->update([
                'estado'         => 'aprobada',
                'revisado_por'   => Auth::id() ?? 1,
                'fecha_revision' => now()->toDateString(),
                'hora_revision'  => now()->toTimeString(),
            ]);

            DB::table('actividades')->insert([
                'descripcion' => "Aprobación de solicitud de recuperación de contraseña para el usuario '{$usuario->nombre_usuario}'.",
                'filtro'     => 'Recuperación de Contraseña',
                'fecha'      => now()->toDateString(),
                'hora'       => now()->toTimeString(),
                'id_persona' => $usuario->id_persona,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'La contraseña se ha restablecido al valor por defecto correctamente.'
        ]);
    }

    /**
     * Rechaza una solicitud pendiente.
     */
    public function rechazarSolicitud(Request $request, $id)
    {
        $solicitud = SolicitudReseteoPassword::findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            return response()->json(['success' => false, 'message' => 'Esta solicitud ya no está disponible.']);
        }

        DB::transaction(function () use ($solicitud, $request) {
            $solicitud->update([
                'estado'         => 'rechazada',
                'revisado_por'   => Auth::id() ?? 1,
                'nota_revision'  => $request->input('nota'),
                'fecha_revision' => now()->toDateString(),
                'hora_revision'  => now()->toTimeString(),
            ]);

            DB::table('actividades')->insert([
                'descripcion' => "Rechazo de solicitud de recuperación de contraseña para el usuario '{$solicitud->nombre_usuario}'.",
                'filtro'     => 'Recuperación de Contraseña',
                'fecha'      => now()->toDateString(),
                'hora'       => now()->toTimeString(),
                'id_persona' => $solicitud->id_usuario ? User::find($solicitud->id_usuario)?->id_persona : null,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'La solicitud se ha rechazado.']);
    }

    /**
     * Verifica la disponibilidad de un nombre de usuario en tiempo real.
     */
    public function verificar(Request $request)
    {
        $username = $request->query('username');
        $id = $request->query('id');

        if (!$username) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $query = User::whereRaw('LOWER(nombre_usuario) = ?', [strtolower(trim($username))]);
        if ($id) {
            $query->where('id', '!=', $id);
        }
        $existe = $query->exists();

        return response()->json(['disponible' => !$existe]);
    }

    /**
     * Genera la lista imprimible de usuarios, respetando el filtro de búsqueda activo.
     */
    public function imprimir(Request $request)
    {
        $query = User::with(['persona', 'perfil'])
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->select('usuarios.*')
            ->orderBy('personas.ap_paterno')
            ->orderBy('personas.ap_materno')
            ->orderBy('personas.nombre');

        $this->aplicarFiltro($query, $request);

        $usuarios = $query->get();

        return view('admin_sistema.usuarios.analitica.reportes.impresion', compact('usuarios'));
    }

    /**
     * Muestra las gráficas estadísticas del módulo.
     */
    public function graficas()
    {
        // Contadores básicos
        $totalActivos = User::where('activo', 1)->count();
        $totalInactivos = User::where('activo', 0)->count();

        // Personas asociadas a usuarios agrupadas por género
        $usuariosPorSexo = DB::table('usuarios')
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->select('personas.sexo', DB::raw('count(*) as total'))
            ->groupBy('personas.sexo')
            ->pluck('total', 'sexo'); // Retorna ['M' => 10, 'F' => 15] o similar

        // Asegurar que existan ambas llaves para evitar errores en JS
        $porSexo = collect([
            'M' => $usuariosPorSexo->get('M', 0),
            'F' => $usuariosPorSexo->get('F', 0)
        ]);

        return view('admin_sistema.usuarios.analitica.graficas', compact('totalActivos', 'totalInactivos', 'porSexo'));
    }
}
