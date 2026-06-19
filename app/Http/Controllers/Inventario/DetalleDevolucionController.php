<?php

namespace App\Http\Controllers\Inventario;
use App\Http\Controllers\Controller;
use App\Models\Inventario\DetalleDevolucion;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\Insumo;
use Illuminate\Http\Request;

class DetalleDevolucionController extends Controller
{
    // public indica que el método puede ser accedido desde fuera de la clase.
    // function declara un método.
    // Request $request recibe el objeto que contiene los datos enviados por el usuario.
    public function store(Request $request)
    {
        // validate() valida los datos recibidos antes de continuar.
        // El primer arreglo contiene las reglas.
        // El segundo arreglo contiene los mensajes personalizados.
        $request->validate([

            // => relaciona una clave con un valor dentro del arreglo.
            // required obliga a enviar el dato.
            // integer obliga a que sea un número entero.
            // exists verifica que el valor exista en una tabla de la base de datos.
            'id_devolucion' => 'required|integer|exists:devoluciones,id_devolucion',
            'id_insumo'     => 'required|integer|exists:insumos,id_insumo',
            'cantidad'      => 'required|integer|min:1',

        ], [

            // Define mensajes personalizados para cada regla de validación.
            'id_devolucion.required' => 'La devolución es requerida.',
            'id_devolucion.exists'   => 'La devolución no existe.',
            'id_insumo.required'     => 'Debe seleccionar un insumo.',
            'id_insumo.exists'       => 'El insumo seleccionado no existe.',
            'cantidad.required'      => 'La cantidad es requerida.',
            'cantidad.min'           => 'La cantidad debe ser al menos 1.',
        ]);

        // :: es el operador de acceso estático.
        // findOrFail() busca un registro por su llave primaria.
        // Si no existe, Laravel genera automáticamente un error 404.
        $devolucion = Devolucion::findOrFail($request->id_devolucion);

        // -> accede a una propiedad del objeto.
        // !== compara valor y tipo al mismo tiempo.
        // Verifica que el status sea exactamente "En proceso".
        if ($devolucion->status !== 'En proceso') {

            // ajax() verifica si la petición fue realizada mediante AJAX.
            if ($request->ajax()) {

                // response() crea una respuesta HTTP.
                // json() convierte el arreglo a formato JSON.
                // 422 es el código HTTP de error de validación.
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede agregar insumos a una devolución finalizada.',
                ], 422);
            }

            // redirect() genera una redirección.
            // back() regresa a la página anterior.
            // with() envía un dato temporal a la siguiente petición.
            return redirect()
                ->back()
                ->with(
                    'error',
                    'No se puede agregar insumos a una devolución finalizada.'
                );
        }

        // create() inserta un nuevo registro en la base de datos.
        // Los datos del arreglo se asignan a las columnas correspondientes.
        DetalleDevolucion::create([
            'id_devolucion' => $request->id_devolucion,
            'id_insumo'     => $request->id_insumo,
            'cantidad'      => $request->cantidad,
        ]);

        // Si la petición fue AJAX devuelve respuesta JSON.
        if ($request->ajax()) {

            return response()->json([
                'ok' => true,
                'mensaje' => 'Insumo agregado correctamente.'
            ]);
        }

        // route() redirige a una ruta nombrada.
        // El segundo parámetro se envía como argumento de la ruta.
        return redirect()
            ->route('devoluciones.detalle', $request->id_devolucion)
            ->with(
                'exitog',
                'Insumo agregado correctamente a la devolución.'
            );
    }

    // Método encargado de actualizar un detalle existente.
    public function update(Request $request, $id)
    {
        // Busca el detalle por su id.
        // $id es un parámetro recibido desde la URL.
        $detalle = DetalleDevolucion::findOrFail($id);

        // Valida que cantidad sea obligatoria, entera y mayor o igual a 1.
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        // update() actualiza los campos indicados del registro encontrado.
        $detalle->update([
            'cantidad' => $request->cantidad,
        ]);

        // Devuelve JSON si la petición es AJAX.
        if ($request->ajax()) {

            return response()->json([
                'ok' => true,
                'mensaje' => 'Detalle actualizado correctamente.'
            ]);
        }

        // Redirecciona a la vista del detalle de la devolución.
        // $detalle->id_devolucion obtiene el id relacionado.
        return redirect()
            ->route('devoluciones.detalle', $detalle->id_devolucion)
            ->with(
                'exito',
                'Detalle actualizado correctamente.'
            );
    }

    // Método encargado de eliminar un detalle.
    public function destroy($id)
    {
        // Busca el detalle por su id.
        $detalle = DetalleDevolucion::findOrFail($id);

        // Obtiene el id de la devolución relacionada.
        // = asigna un valor a una variable.
        $idDevol = $detalle->id_devolucion;

        // find() busca un registro.
        // Si no existe devuelve null en lugar de lanzar excepción.
        $devolucion = Devolucion::find($idDevol);

        // && representa el operador lógico AND.
        // Ambas condiciones deben cumplirse para entrar al bloque.
        if ($devolucion && $devolucion->status !== 'En proceso') {

            // request() obtiene la instancia actual de Request.
            if (request()->ajax()) {

                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se puede eliminar insumos de una devolución finalizada.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with(
                    'error',
                    'No se puede eliminar insumos de una devolución finalizada.'
                );
        }

        // delete() elimina el registro de la base de datos.
        $detalle->delete();

        // Si la petición es AJAX devuelve una respuesta JSON.
        if (request()->ajax()) {

            return response()->json([
                'ok' => true,
                'mensaje' => 'Insumo eliminado de la devolución.'
            ]);
        }

        // Redirecciona nuevamente al detalle de la devolución.
        return redirect()
            ->route('devoluciones.detalle', $idDevol)
            ->with(
                'exito',
                'Insumo eliminado de la devolución.'
            );
    }
}