<?php

namespace App\Http\Controllers\MisDatos;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Sepomex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MisDatosController extends Controller
{
    /**
     * Muestra el formulario con los datos personales del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Obtener la relación de persona o buscarla por ID de persona
        $persona = $user->persona;

        if (!$persona) {
            // Si el usuario no tiene una persona asociada, creamos un objeto vacío o fallamos
            abort(404, 'No se encontraron datos personales asociados a este usuario.');
        }

        // Consultar los estados de manera óptima desde sepomex
        $estados = Sepomex::estadosUnicos();

        return view('sidebar.mis_datos.index', compact('persona', 'estados'));
    }

    /**
     * Procesa la actualización de los datos personales.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'No autorizado');
        }

        $persona = $user->persona;
        if (!$persona) {
            abort(404, 'No se encontraron datos personales asociados a este usuario.');
        }

        // Validación estricta y limpia según mejores prácticas
        $validatedData = $request->validate([
            'nombre'     => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'required|string|max:255',
            'fecha_nac'  => 'required|date',
            'sexo'       => 'required|in:M,F',
            'ecivil'     => 'required|string|in:Soltero(a),Casado(a),Viudo(a),Divorciado(a),Union Libre,No especificado',
            'tel'        => 'required|string|max:50',
            'rfc'        => 'required|string|max:13|min:10',
            'curp'       => 'required|string|max:18|min:18',
            'email'      => 'required|email|max:255',
            'colonia'    => 'required|string|max:255',
            'calle'      => 'required|string|max:255',
            'numero'     => 'required|string|max:50',
            'estado'     => 'required|string|max:255',
            'municipio'  => 'required|string|max:255',
        ], [
            'nombre.required'     => 'El nombre es obligatorio.',
            'ap_paterno.required' => 'El apellido paterno es obligatorio.',
            'ap_materno.required' => 'El apellido materno es obligatorio.',
            'fecha_nac.required'  => 'La fecha de nacimiento es obligatoria.',
            'sexo.required'       => 'El sexo es obligatorio.',
            'ecivil.required'     => 'El estado civil es obligatorio.',
            'tel.required'        => 'El teléfono es obligatorio.',
            'rfc.required'        => 'El RFC es obligatorio.',
            'rfc.min'             => 'El RFC debe tener al menos 10 caracteres.',
            'rfc.max'             => 'El RFC no debe exceder los 13 caracteres.',
            'curp.required'       => 'El CURP es obligatorio.',
            'curp.min'            => 'El CURP debe tener exactamente 18 caracteres.',
            'curp.max'            => 'El CURP debe tener exactamente 18 caracteres.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'email.email'         => 'Debe ingresar un correo electrónico válido.',
            'colonia.required'    => 'La colonia es obligatoria.',
            'calle.required'      => 'La calle es obligatoria.',
            'numero.required'     => 'El número de domicilio es obligatorio.',
            'estado.required'     => 'El estado es obligatorio.',
            'municipio.required'  => 'El municipio es obligatorio.',
        ]);

        // Actualizar los datos personales con Eloquent
        $persona->update([
            'nombre'     => $validatedData['nombre'],
            'ap_paterno' => $validatedData['ap_paterno'],
            'ap_materno' => $validatedData['ap_materno'],
            'fecha_nac'  => $validatedData['fecha_nac'],
            'sexo'       => $validatedData['sexo'],
            'ecivil'     => $validatedData['ecivil'],
            'telefono'   => $validatedData['tel'],
            'rfc'        => strtoupper($validatedData['rfc']),
            'curp'       => strtoupper($validatedData['curp']),
            'e_mail'     => $validatedData['email'],
            'colonia'    => $validatedData['colonia'],
            'calle'      => $validatedData['calle'],
            'numero'     => $validatedData['numero'],
            'estado'     => $validatedData['estado'],
            'municipio'  => $validatedData['municipio'],
            'fecha'      => now()->toDateString(),
            'hora'       => now()->toTimeString(),
            'usuario'    => $user->nombre_usuario ?? 'sistema',
        ]);

        return redirect()
            ->route('mis_datos.index')
            ->with('success', 'Tus datos personales se han actualizado correctamente.');
    }
}
