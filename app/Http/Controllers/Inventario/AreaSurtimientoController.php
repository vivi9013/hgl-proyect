<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaSurtimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaSurtimientoController extends Controller
{

    public function index(Request $request)
    {
        // Obtiene el valor del parámetro "buscar" enviado en la petición.
        // Si no existe asigna una cadena vacía ''.
        $buscar = $request->get('buscar', '');

        // Crea una consulta sobre el modelo AreaSurtimiento.
        // Ordena los registros por id_area_surtimiento de forma descendente.
        $query = AreaSurtimiento::orderBy('id_area_surtimiento', 'desc');

        // Verifica que la variable buscar no esté vacía.
        if (!empty($buscar)) {

            // Agrupa las condiciones de búsqueda dentro de una función anónima.
            // use($buscar) permite utilizar la variable dentro de la función.
            $query->where(function ($q) use ($buscar) {

                // Busca coincidencias parciales en el campo nombre.
                $q->where('nombre', 'LIKE', "%{$buscar}%")

                    // También busca coincidencias en el campo tipo.
                    ->orWhere('tipo', 'LIKE', "%{$buscar}%");
            });
        }

        // Verifica si la petición fue realizada mediante AJAX.
        if ($request->ajax()) {

            // Selecciona únicamente los campos indicados.
            $sugerencias = $query->select(
                    'id_area_surtimiento',
                    'nombre',
                    'tipo',
                    'activo'
                )

                // Limita la consulta a 10 resultados.
                ->limit(10)

                // Ejecuta la consulta.
                ->get()

                // Recorre cada resultado y crea un nuevo arreglo.
                ->map(fn($a) => [

                    // Asigna el id del registro.
                    'id' => $a->id_area_surtimiento,

                    // Asigna el nombre.
                    'nombre' => $a->nombre,

                    // Asigna el tipo.
                    'tipo' => $a->tipo,

                    // Asigna el estado.
                    'activo' => $a->activo,
                ]);

            // Devuelve los datos en formato JSON.
            return response()->json($sugerencias);
        }

        // Divide los resultados en páginas de 10 registros.
        $areas = $query->paginate(10)

            // Conserva los parámetros de búsqueda en la URL.
            ->withQueryString();

        // Retorna la vista y envía las variables areas y buscar.
        return view(
            'inventario.areas_surtimiento.index',
            compact('areas', 'buscar')
        );
    }

    public function guardar(Request $request)
    {
        // Valida los datos enviados desde el formulario.
        $request->validate([

            // Campo obligatorio, debe ser texto y máximo 255 caracteres.
            'nombre' => 'required|string|max:255',

            // Campo obligatorio, debe ser texto y máximo 100 caracteres.
            'tipo' => 'required|string|max:100',

        ], [

            // Mensajes personalizados de validación.
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'tipo.required' => 'El tipo de área es obligatorio.',
            'tipo.max' => 'El tipo no puede superar los 100 caracteres.',
        ]);

        // Busca si ya existe un registro con el mismo nombre y tipo.
        $existe = AreaSurtimiento::whereRaw(

                // Convierte el campo nombre a minúsculas para comparar.
                'LOWER(nombre) = ?',

                // Convierte el nombre recibido a minúsculas y elimina espacios.
                [strtolower(trim($request->nombre))]
            )

            // Compara el tipo recibido.
            ->where('tipo', $request->tipo)

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
                    'nombre' => 'Esta área de surtimiento ya se encuentra registrada con ese tipo.'
                ]);
        }

        // Crea un nuevo registro en la base de datos.
        AreaSurtimiento::create([

            // Elimina espacios innecesarios del nombre.
            'nombre' => trim($request->nombre),

            // Guarda el tipo seleccionado.
            'tipo' => $request->tipo,

            // Guarda el estado activo.
            'activo' => 1,

            // Guarda la fecha actual.
            'fecha_registro' => now()->toDateString(),

            // Guarda la hora actual.
            'hora_registro' => now()->toTimeString(),

            // Obtiene el id del usuario autenticado.
            // Si es null usa el valor 1.
            'id_usuario' => Auth::id() ?? 1,
        ]);

        return redirect()->route('areas_surtimiento.index')
            ->with('exitog', 'El área de surtimiento se ha guardado correctamente.');
    }

    public function actualizar(Request $request, $id)
    {
        // Busca el registro por su id.
        // Si no existe genera un error 404.
        $area = AreaSurtimiento::findOrFail($id);

        // Actualiza los datos del registro encontrado.
        $area->update([

            // Actualiza el nombre.
            'nombre' => trim($request->nombre),

            // Actualiza el tipo.
            'tipo' => $request->tipo,

            // Actualiza fecha.
            'fecha_registro' => now()->toDateString(),

            // Actualiza hora.
            'hora_registro' => now()->toTimeString(),

            // Guarda el usuario que realizó la modificación.
            'id_usuario' => Auth::id() ?? 1,
        ]);

        return redirect()->route('areas_surtimiento.index')
            ->with('exito', 'El área de surtimiento se ha actualizado correctamente.');
    }

    public function cambiarStatus($id)
    {
        // Busca el registro por su id.
        // Si no existe genera un error 404.
        $area = AreaSurtimiento::findOrFail($id);

        // Cambia el estado activo/inactivo usando operador ternario.
        $area->activo = $area->activo == 1 ? 0 : 1;

        // Actualiza la fecha.
        $area->fecha_registro = now()->toDateString();

        // Actualiza la hora.
        $area->hora_registro = now()->toTimeString();

        // Guarda el usuario que realizó el cambio.
        $area->id_usuario = Auth::id() ?? 1;

        // Guarda los cambios en la base de datos.
        $area->save();

        return redirect()->route('areas_surtimiento.index')
            ->with('exito', 'El estado del área de surtimiento se ha actualizado.');
    }

    public function verificar(Request $request)
    {
        // Obtiene el parámetro nombre desde la URL.
        $nombre = $request->query('nombre');

        // Obtiene el parámetro tipo desde la URL.
        $tipo = $request->query('tipo');

        // Si falta alguno de los parámetros.
        if (!$nombre || !$tipo) {

            // Devuelve una respuesta JSON indicando error.
            return response()->json([
                'disponible' => false,
                'error' => 'Parámetros ausentes'
            ]);
        }

        // Busca si ya existe un registro con el mismo nombre y tipo.
        $existe = AreaSurtimiento::whereRaw(
                'LOWER(nombre) = ?',
                [strtolower(trim($nombre))]
            )
            ->where('tipo', $tipo)
            ->exists();

        // Devuelve un JSON indicando si el registro está disponible.
        // El operador ! invierte el valor booleano.
        return response()->json([
            'disponible' => !$existe
        ]);
    }
}
