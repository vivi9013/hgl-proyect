<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaAlmacenController extends Controller
{

public function index(Request $request) // Método público llamado index. Recibe el objeto Request con los datos enviados por el usuario.
{
    $buscar = $request->get('buscar', ''); // Obtiene el valor del parámetro "buscar". Si no existe, devuelve una cadena vacía.

    $query = AreaAlmacen::orderBy('id_area_almacen', 'desc'); // Crea una consulta y ordena los registros por id_area_almacen de forma descendente.

    if (!empty($buscar)) { // Verifica que la variable buscar no esté vacía.
        $query->where('nombre', 'LIKE', "%{$buscar}%"); // Agrega una condición SQL LIKE para buscar coincidencias parciales.
    }

    // AJAX: devolver sugerencias JSON para el autocomplete del buscador
    if ($request->ajax()) { // Verifica si la petición fue realizada mediante AJAX.
        $sugerencias = $query->select('id_area_almacen', 'nombre', 'activo') // Selecciona únicamente estos campos.
            ->limit(10) // Limita los resultados a 10 registros.
            ->get() // Ejecuta la consulta y obtiene los resultados.
            ->map(fn($a) => [ // Recorre cada resultado utilizando una función flecha.
                'id'     => $a->id_area_almacen, // Asigna el id del área.
                'nombre' => $a->nombre, // Asigna el nombre del área.
                'activo' => $a->activo, // Asigna el estado activo.
            ]);
        return response()->json($sugerencias); // Devuelve los datos en formato JSON.
    }

    $areas = $query->paginate(10)->withQueryString(); // Pagina los resultados mostrando 10 registros por página y conserva los parámetros de la URL.

    return view('inventario.areas_almacen.index', compact('areas', 'buscar')); // Retorna la vista enviando las variables areas y buscar.
}

/**
 * Guarda una nueva área de almacén en la base de datos.
 */
public function guardar(Request $request) // Método encargado de guardar un nuevo registro.
{
    $request->validate([ // Valida los datos enviados desde el formulario.
        'nombre' => 'required|string|max:255', // El campo nombre es obligatorio, debe ser texto y máximo 255 caracteres.
    ], [
        'nombre.required' => 'El nombre del área es obligatorio.', // Mensaje personalizado para campo obligatorio.
        'nombre.max'      => 'El nombre no puede superar los 255 caracteres.', // Mensaje personalizado para longitud máxima.
    ]);

    // Verificar duplicados (insensible a mayúsculas/minúsculas)
    $existe = AreaAlmacen::whereRaw( // Ejecuta una condición SQL personalizada.
        'LOWER(nombre) = ?', // Convierte el nombre a minúsculas para comparar.
        [strtolower(trim($request->nombre))] // Convierte el valor recibido a minúsculas y elimina espacios al inicio y final.
    )->exists(); // Devuelve true si existe un registro coincidente.

    if ($existe) { // Verifica si ya existe el área.
        return redirect()->back() // Regresa a la página anterior.
            ->withInput() // Conserva los datos escritos por el usuario.
            ->withErrors(['nombre' => 'Esta área de almacén ya se encuentra registrada.']); // Muestra mensaje de error.
    }

    AreaAlmacen::create([ // Inserta un nuevo registro en la base de datos.
        'nombre'         => trim($request->nombre), // Guarda el nombre sin espacios innecesarios.
        'activo'         => 1, // Establece el estado como activo.
        'fecha_registro' => now()->toDateString(), // Guarda la fecha actual.
        'hora_registro'  => now()->toTimeString(), // Guarda la hora actual.
        'id_usuario'     => Auth::id() ?? 1, // Obtiene el id del usuario autenticado. Si es nulo usa 1.
    ]);

    return redirect() // Inicia una redirección.
        ->route('areas_almacen.index') // Redirige a la ruta indicada.
        ->with('exitog', 'El área de almacén se ha guardado correctamente.'); // Envía mensaje de éxito.
}

/**
 * Muestra el formulario de edición de un área.
 */
public function editar($id) // Método para cargar un registro específico.
{
    $area = AreaAlmacen::findOrFail($id); // Busca el registro por id. Si no existe genera error 404.

    return view('inventario.areas_almacen.editar', compact('area')); // Envía el registro a la vista de edición.
}

//Actualiza los datos de un área de almacén.
public function actualizar(Request $request, $id) // Método para actualizar un registro existente.
{
    $request->validate([ // Valida los datos enviados.
        'nombre' => 'required|string|max:255', // Reglas de validación.
    ], [
        'nombre.required' => 'El nombre del área es obligatorio.', // Mensaje para campo obligatorio.
        'nombre.max'      => 'El nombre no puede superar los 255 caracteres.', // Mensaje para longitud máxima.
    ]);

    $area = AreaAlmacen::findOrFail($id); // Busca el área por id.

    // Verificar duplicados excluyendo el registro actual
    $existe = AreaAlmacen::whereRaw( // Consulta personalizada.
        'LOWER(nombre) = ?',
        [strtolower(trim($request->nombre))]
    )
        ->where('id_area_almacen', '!=', $id) // Excluye el registro actual de la búsqueda.
        ->exists(); // Verifica si existe otro registro con el mismo nombre.

    if ($existe) { // Si existe un duplicado.
        return redirect()->back() // Regresa a la página anterior.
            ->withInput() // Conserva los datos escritos.
            ->withErrors(['nombre' => 'Ya existe otra área de almacén registrada con ese nombre.']); // Muestra error.
    }

    $area->update([ // Actualiza los datos del registro.
        'nombre'         => trim($request->nombre), // Actualiza el nombre.
        'fecha_registro' => now()->toDateString(), // Actualiza la fecha.
        'hora_registro'  => now()->toTimeString(), // Actualiza la hora.
        'id_usuario'     => Auth::id() ?? 1, // Guarda el usuario que realizó el cambio.
    ]);

    return redirect() // Redirige al usuario.
        ->route('areas_almacen.index') // Ruta destino.
        ->with('exito', 'El área de almacén se ha actualizado correctamente.'); // Mensaje de éxito.
}

/**
 * Alterna el estado activo/inactivo de un área de almacén.
 */
public function cambiarStatus($id) // Método para cambiar el estado de un registro.
{
    $area = AreaAlmacen::findOrFail($id); // Busca el área por id.

    $area->activo = $area->activo == 1 ? 0 : 1; // Operador ternario. Si está activo cambia a 0, si no cambia a 1.

    $area->fecha_registro = now()->toDateString(); // Actualiza la fecha.
    $area->hora_registro  = now()->toTimeString(); // Actualiza la hora.
    $area->id_usuario     = Auth::id() ?? 1; // Guarda el usuario que realizó el cambio.

    $area->save(); // Guarda los cambios en la base de datos.

    return redirect() // Redirige al usuario.
        ->route('areas_almacen.index') // Ruta de destino.
        ->with('exito', 'El estado del área de almacén se ha actualizado.'); // Mensaje de éxito.
}

/**
 * Verifica por AJAX si el nombre ya está registrado.
 */
public function verificar(Request $request) // Método utilizado para validación en tiempo real.
{
    $nombre = $request->query('nombre'); // Obtiene el parámetro nombre desde la URL.

    if (!$nombre) { // Si no se recibió el parámetro.
        return response()->json([ // Devuelve respuesta JSON.
            'disponible' => false, // Indica que no está disponible.
            'error' => 'Parámetro ausente' // Mensaje de error.
        ]);
    }

    $existe = AreaAlmacen::whereRaw( // Consulta SQL personalizada.
        'LOWER(nombre) = ?',
        [strtolower(trim($nombre))]
    )->exists(); // Comprueba si el nombre ya existe.

    return response()->json([ // Devuelve respuesta JSON.
        'disponible' => !$existe // Operador lógico NOT. Si existe devuelve false, si no existe devuelve true.
    ]);
}

/**
 * Genera el reporte/impresión de las áreas de almacén.
 */
public function imprimir(Request $request) // Método para generar el reporte.
{
    $buscar = $request->get('buscar', ''); // Obtiene el valor del filtro de búsqueda.

    $query = AreaAlmacen::orderBy('nombre', 'asc'); // Ordena los registros alfabéticamente.

    if (!empty($buscar)) { // Verifica si existe un filtro.
        $query->where('nombre', 'LIKE', "%{$buscar}%"); // Filtra registros por coincidencia parcial.
    }

    $areas = $query->get(); // Obtiene todos los resultados de la consulta.

    return view(
        'inventario.areas_almacen.reporte_impresion', // Vista del reporte.
        compact('areas', 'buscar') // Envía las variables a la vista.
    );
}
}