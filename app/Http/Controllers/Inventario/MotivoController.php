<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Motivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MotivoController extends Controller
{

    public function index(Request $request)
    {
        // Obtiene el valor del parámetro "buscar" enviado en la petición.
        // Si no existe asigna una cadena vacía ''.
        $buscar = $request->get('buscar', '');

        // Crea una consulta sobre el modelo Motivo.
        // Ordena los registros por id_motivo de forma descendente.
        $query = Motivo::orderBy('id_motivo', 'desc');

        // Verifica que la variable buscar no esté vacía.
        if (!empty($buscar)) {

            // Agrupa las condiciones de búsqueda dentro de una función anónima.
            // use($buscar) permite utilizar la variable dentro de la función.
            $query->where(function ($q) use ($buscar) {

                // Busca coincidencias parciales en el campo descripcion.
                $q->where('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        // Verifica si la petición fue realizada mediante AJAX.
        if ($request->ajax()) {

            // Selecciona únicamente los campos indicados.
            $sugerencias = $query->select(
                    'id_motivo',
                    'descripcion',
                    'modificar',
                    'activo'
                )

                // Limita la consulta a 10 resultados.
                ->limit(10)

                // Ejecuta la consulta.
                ->get()

                // Recorre cada resultado y crea un nuevo arreglo.
                ->map(fn($m) => [

                    // Asigna el id del registro.
                    'id' => $m->id_motivo,

                    // Asigna la descripción.
                    'descripcion' => $m->descripcion,

                    // Asigna el tipo de modificación.
                    'modificar' => $m->modificar,

                    // Asigna el estado.
                    'activo' => $m->activo,
                ]);

            // Devuelve los datos en formato JSON.
            return response()->json($sugerencias);
        }

        // Divide los resultados en páginas de 10 registros.
        $motivos = $query->paginate(10)

            // Conserva los parámetros de búsqueda en la URL.
            ->withQueryString();

        // Retorna la vista y envía las variables motivos y buscar.
        return view(
            'inventario.motivos.index',
            compact('motivos', 'buscar')
        );
    }

    public function guardar(Request $request)
    {
        // Valida los datos enviados desde el formulario.
        $request->validate([

            // Campo obligatorio, debe ser texto y máximo 255 caracteres.
            'descripcion' => 'required|string|max:255',

            // Campo obligatorio, debe ser 'Si' o 'No'.
            'modificar'   => 'required|in:Si,No',

        ], [

            // Mensajes personalizados de validación.
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'modificar.required'   => 'El campo Modificar Stock es obligatorio.',
            'modificar.in'         => 'El valor de Modificar Stock debe ser Si o No.',
        ]);

        // Busca si ya existe un registro con la misma descripción.
        $existe = Motivo::whereRaw(

                // Convierte el campo descripcion a minúsculas para comparar.
                'LOWER(descripcion) = ?',

                // Convierte la descripción recibida a minúsculas y elimina espacios.
                [strtolower(trim($request->descripcion))]
            )

            // Devuelve true o false.
            ->exists();

        // Si ya existe un registro igual.
        if ($existe) {

            // Regresa a la página anterior.
            return redirect()->back()

                // Conserva los datos capturados.
                ->withInput()

                // Envía mensaje de error.
                ->withErrors([
                    'descripcion' => 'Este motivo ya se encuentra registrado en el sistema.'
                ]);
        }

        // Crea un nuevo registro en la base de datos.
        Motivo::create([

            // Elimina espacios innecesarios de la descripción.
            'descripcion'    => trim($request->descripcion),

            // Guarda el valor de modificar stock.
            'modificar'      => $request->modificar,

            // Guarda el estado activo.
            'activo'         => 1,

            // Guarda la fecha actual.
            'fecha_registro' => now()->toDateString(),

            // Guarda la hora actual.
            'hora_registro'  => now()->toTimeString(),

            // Obtiene el id del usuario autenticado.
            // Si es null usa el valor 1.
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()->route('motivos.index')
            ->with('exitog', 'El motivo se ha guardado correctamente.');
    }

    public function editar($id)
    {
        // Busca el registro por su id.
        // Si no existe genera un error 404.
        $motivo = Motivo::findOrFail($id);

        // Retorna la vista de edición con el motivo encontrado.
        return view('inventario.motivos.editar', compact('motivo'));
    }

    public function actualizar(Request $request, $id)
    {
        // Busca el registro por su id.
        // Si no existe genera un error 404.
        $motivo = Motivo::findOrFail($id);

        // Valida los datos enviados desde el formulario.
        $request->validate([

            // Campo obligatorio, debe ser texto y máximo 255 caracteres.
            'descripcion' => 'required|string|max:255',

            // Campo obligatorio, debe ser 'Si' o 'No'.
            'modificar'   => 'required|in:Si,No',

        ], [
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 255 caracteres.',
            'modificar.required'   => 'El campo Modificar Stock es obligatorio.',
            'modificar.in'         => 'El valor de Modificar Stock debe ser Si o No.',
        ]);

        // Verifica si ya existe otro registro con la misma descripción (excluyendo el actual).
        $existe = Motivo::whereRaw(
                'LOWER(descripcion) = ?',
                [strtolower(trim($request->descripcion))]
            )
            ->where('id_motivo', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'descripcion' => 'Este motivo ya se encuentra registrado en el sistema.'
                ]);
        }

        // Actualiza los datos del registro encontrado.
        $motivo->update([

            // Actualiza la descripción.
            'descripcion'    => trim($request->descripcion),

            // Actualiza el campo modificar.
            'modificar'      => $request->modificar,

            // Actualiza fecha.
            'fecha_registro' => now()->toDateString(),

            // Actualiza hora.
            'hora_registro'  => now()->toTimeString(),

            // Guarda el usuario que realizó la modificación.
            'id_usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()->route('motivos.index')
            ->with('exito', 'El motivo se ha actualizado correctamente.');
    }

    public function cambiarStatus($id)
    {
        // Busca el registro por su id.
        // Si no existe genera un error 404.
        $motivo = Motivo::findOrFail($id);

        // Cambia el estado activo/inactivo usando operador ternario.
        $motivo->activo = $motivo->activo == 1 ? 0 : 1;

        // Actualiza la fecha.
        $motivo->fecha_registro = now()->toDateString();

        // Actualiza la hora.
        $motivo->hora_registro = now()->toTimeString();

        // Guarda el usuario que realizó el cambio.
        $motivo->id_usuario = Auth::id() ?? 1;

        // Guarda los cambios en la base de datos.
        $motivo->save();

        return redirect()->route('motivos.index')
            ->with('exito', 'El estado del motivo se ha actualizado.');
    }

    public function verificar(Request $request)
    {
        // Obtiene el parámetro descripcion desde la URL.
        $descripcion = $request->query('descripcion');

        // Si falta el parámetro.
        if (!$descripcion) {

            // Devuelve una respuesta JSON indicando error.
            return response()->json([
                'disponible' => false,
                'error'      => 'Parámetros ausentes'
            ]);
        }

        // Busca si ya existe un registro con la misma descripción.
        $existe = Motivo::whereRaw(
                'LOWER(descripcion) = ?',
                [strtolower(trim($descripcion))]
            )
            ->exists();

        // Devuelve un JSON indicando si el registro está disponible.
        // El operador ! invierte el valor booleano.
        return response()->json([
            'disponible' => !$existe
        ]);
    }

    public function imprimir(Request $request)
    {
        // Obtiene el valor del parámetro "buscar" enviado en la petición.
        $buscar = $request->get('buscar', '');

        // Crea una consulta sobre el modelo Motivo.
        $query = Motivo::orderBy('id_motivo', 'desc');

        // Aplica el filtro de búsqueda si existe.
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        // Obtiene todos los registros sin paginar para el reporte.
        $motivos = $query->get();

        // Retorna la vista de impresión con los datos.
        return view('inventario.motivos.reporte_impresion', compact('motivos', 'buscar'));
    }
}
