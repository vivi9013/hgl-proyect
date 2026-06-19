<?php

namespace App\Http\Controllers\Inventario;
use App\Http\Controllers\Controller;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\DetalleDevolucion;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\Motivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// class define una nueva clase PHP.
// DevolucionController es el nombre de este controlador.
// extends indica que hereda métodos y propiedades de Controller.
class DevolucionController extends Controller
{
    // private restringe el acceso a esta constante solo dentro de esta clase.
    // const define una constante cuyo valor no puede cambiar durante la ejecución.
    // PER_PAGE almacena el número de registros que se mostrarán por página.
    private const PER_PAGE = 10;

    // PENDIENTES

    // public indica que este método puede ser llamado desde fuera de la clase.
    // function declara un método llamado index.
    // Request $request recibe el objeto con todos los datos enviados por el usuario.
    public function index(Request $request)
    {
        // -> accede a métodos del objeto $request.
        // get() obtiene el valor de un parámetro de la petición.
        // El segundo argumento '' es el valor por defecto si el parámetro no existe.
        $buscar    = $request->get('buscar', '');

        // Obtiene el parámetro fecha_inicio desde la URL o el formulario.
        $fechaInit = $request->get('fecha_inicio', '');

        // Obtiene el parámetro fecha_fin desde la URL o el formulario.
        $fechaFin  = $request->get('fecha_fin', '');

        // $this-> accede a métodos del mismo objeto (controlador).
        // normalizarFecha() convierte la fecha a formato Y-m-d para usarla en la base de datos.
        // [] al lado izquierdo desestructura el arreglo devuelto en dos variables.
        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);

        // Normaliza la fecha de fin de la misma forma.
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        // && significa AND lógico; ambas condiciones deben ser verdaderas para entrar.
        // > compara si la fecha de inicio es mayor (más reciente) que la de fin.
        // Si el rango es incoherente se cancela y se muestra un error.
        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {

            // redirect() genera una redirección HTTP.
            // back() regresa a la página anterior.
            // withInput() conserva los valores escritos en el formulario.
            // with() envía un mensaje temporal de error a la siguiente petición.
            return redirect()->back()->withInput()
                ->with('error', 'La fecha de inicio no puede ser posterior a la fecha de fin.');
        }

        // :: es el operador de acceso estático al modelo.
        // with() carga relaciones definidas en el modelo usando Eager Loading.
        // Carga areaAlmacen, areaAbastecimiento, usuario.persona y motivo junto con la devolución.
        $query = Devolucion::with(['areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])

            // whereIn() filtra registros cuyo campo status sea uno de los valores del arreglo.
            ->whereIn('status', ['En proceso', 'Cancelado'])

            // orderBy() ordena los resultados por id_devolucion de forma descendente.
            ->orderBy('id_devolucion', 'desc');

        // ! niega el resultado de empty().
        // empty() devuelve true si la variable está vacía.
        // Solo aplica el filtro de búsqueda si el usuario escribió algo.
        if (!empty($buscar)) {

            // where() con una función anónima agrupa condiciones OR dentro de un AND.
            // use($buscar) permite usar la variable $buscar dentro de la función.
            $query->where(function ($q) use ($buscar) {

                // where() busca coincidencias parciales en el campo id_devolucion.
                // LIKE con %% permite buscar el texto en cualquier posición.
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")

                  // orWhereHas() agrega una condición OR sobre una relación.
                  // fn($q2) => es una función flecha que pasa $q2 como parámetro.
                  // Busca devoluciones cuya área de almacén contenga el texto buscado.
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))

                  // Busca también por nombre del área de abastecimiento relacionada.
                  ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        // whereDate() filtra comparando solo la parte de fecha del campo fecha_devolucion.
        // >= significa mayor o igual; filtra desde la fecha de inicio inclusive.
        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);

        // <= significa menor o igual; filtra hasta la fecha de fin inclusive.
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        // paginate() divide los resultados en páginas de PER_PAGE registros.
        // self::PER_PAGE accede a la constante definida en esta misma clase.
        // withQueryString() conserva los parámetros de búsqueda en los enlaces de paginación.
        $devoluciones  = $query->paginate(self::PER_PAGE)->withQueryString();

        // where() filtra áreas de almacén que estén activas.
        // orderBy() las ordena alfabéticamente por nombre.
        // get() ejecuta la consulta y devuelve todos los registros.
        $areasAlmacen  = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        // Obtiene los motivos activos ordenados por descripción.
        $motivos       = Motivo::where('activo', 1)->orderBy('descripcion')->get();

        // try intenta ejecutar código que podría lanzar una excepción.
        // Se usa porque la tabla áreas de abastecimiento puede no existir en la base de datos.
        try {
            // orderBy() ordena las áreas de abastecimiento por nombre.
            $areasAbastecimiento = AreaAbastecimiento::orderBy('nombre')->get();

        // catch captura cualquier excepción que ocurra dentro del try.
        // \Exception es la clase base de todas las excepciones en PHP.
        } catch (\Exception $e) {

            // collect() crea una colección vacía de Laravel cuando no hay datos disponibles.
            $areasAbastecimiento = collect();
        }

        // return devuelve la respuesta al navegador.
        // view() carga la vista indicada y le envía las variables.
        // compact() crea un arreglo asociativo usando los nombres de las variables.
        return view('inventario.devoluciones.index', compact(
            'devoluciones', 'areasAlmacen', 'areasAbastecimiento', 'motivos',
            'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    // public indica que este método puede llamarse desde fuera de la clase.
    // store() es el nombre del método que recibe y guarda una nueva devolución.
    // Request $request contiene todos los datos enviados desde el formulario.
    public function store(Request $request)
    {
        // validate() verifica que los datos cumplan las reglas antes de continuar.
        // Si alguna regla falla, Laravel regresa automáticamente con los errores.
        $request->validate([

            // required obliga a enviar el campo.
            // integer verifica que sea un número entero.
            // exists verifica que el valor exista en la tabla y columna indicadas.
            'id_area_almacen' => 'required|integer|exists:areas_almacen,id_area_almacen',

            // Valida que el motivo exista en la tabla motivos.
            'id_motivo'       => 'required|integer|exists:motivos,id_motivo',

        ], [

            // => asocia una clave de error con su mensaje personalizado.
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'   => 'El área de almacén seleccionada no existe.',
            'id_motivo.required'       => 'Debe seleccionar un motivo de devolución.',
            'id_motivo.exists'         => 'El motivo de devolución seleccionado no existe.',
        ]);

        // :: accede estáticamente al modelo Devolucion.
        // create() inserta un nuevo registro en la tabla devoluciones.
        // [] define el arreglo de columnas con sus valores.
        $devolucion = Devolucion::create([

            // Auth::id() obtiene el id del usuario autenticado en la sesión.
            // ?? es el operador null coalescing; usa 1 si Auth::id() devuelve null.
            'id_usuario_registro'      => Auth::id() ?? 1,

            // -> accede a la propiedad del objeto $request con el valor enviado.
            'id_area_almacen'          => $request->id_area_almacen,

            // ?? devuelve null si el campo no fue enviado en la petición.
            'id_area_abastecimiento'   => $request->id_area_abastecimiento ?? null,

            // Igual que el anterior pero para la subárea de abastecimiento.
            'id_subarea_abastecimiento'=> $request->id_subarea_abastecimiento ?? null,

            // now() devuelve la fecha y hora actuales.
            // toDateString() extrae solo la parte de fecha en formato Y-m-d.
            'fecha_devolucion'         => now()->toDateString(),

            // toTimeString() extrae solo la parte de hora en formato H:i:s.
            'hora_devolucion'          => now()->toTimeString(),

            // Se asigna el status inicial como "En proceso".
            'status'                   => 'En proceso',

            // Los totales inician en 0 porque aún no se han agregado insumos.
            'total_productos'          => 0,
            'total_cantidad'           => 0,

            // Guarda el motivo seleccionado por el usuario.
            'id_motivo'                => $request->id_motivo,
        ]);

        // route() redirige a una ruta nombrada pasando el id de la devolución creada.
        // -> encadena with() para enviar un mensaje de éxito a la vista siguiente.
        return redirect()
            ->route('devoluciones.detalle', $devolucion->id_devolucion)
            ->with('exitog', 'Devolución creada correctamente. Ahora agregue los insumos.');
    }

    // DETALLE (agregar / ver insumos)

    // public indica que el método es accesible desde fuera de la clase.
    // detalle() recibe el id de la devolución como parámetro de la URL.
    // $id es el identificador de la devolución que se quiere mostrar.
    public function detalle($id)
    {
        // with() carga las relaciones detalles.insumo, areaAlmacen, areaAbastecimiento y motivo.
        // findOrFail() busca la devolución por su id y lanza error 404 si no existe.
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'motivo'])->findOrFail($id);

        // return devuelve la vista de detalle con la devolución encontrada.
        return view('inventario.devoluciones.detalle', compact('devolucion'));
    }

    // FINALIZAR

    // public permite acceder al método desde fuera de la clase.
    // finalizar() recibe el id de la devolución a finalizar.
    public function finalizar($id)
    {
        // with() carga las relaciones necesarias para calcular totales y actualizar stock.
        // findOrFail() obtiene la devolución o detiene la ejecución con error 404.
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento'])->findOrFail($id);

        // -> accede a la propiedad status del objeto.
        // !== compara valor y tipo; verifica que no sea exactamente "En proceso".
        if ($devolucion->status !== 'En proceso') {

            // route() redirige al listado de devoluciones.
            // with() envía un mensaje de error al usuario.
            return redirect()->route('devoluciones.index')
                ->with('error', 'Esta devolución ya fue finalizada.');
        }

        // -> accede a la relación detalles del objeto devolución.
        // isEmpty() devuelve true si la colección no tiene elementos.
        // No se puede finalizar una devolución sin insumos registrados.
        if ($devolucion->detalles->isEmpty()) {

            // Redirige al detalle de la devolución con un mensaje de error.
            return redirect()->route('devoluciones.detalle', $id)
                ->with('error', 'No puede finalizar una devolución sin insumos registrados.');
        }

        // DB::transaction() ejecuta todas las operaciones dentro de una transacción.
        // Si alguna falla, todas se deshacen automáticamente (rollback).
        // function() define una función anónima que recibe $devolucion con use().
        DB::transaction(function () use ($devolucion) {

            // foreach recorre cada elemento de la colección detalles.
            // $detalle representa cada insumo registrado en la devolución.
            foreach ($devolucion->detalles as $detalle) {

                // where() filtra por id_insumo para encontrar el stock del área correspondiente.
                // first() devuelve el primer resultado o null si no existe.
                $insumoArea = InsumoArea::where('id_insumo', $detalle->id_insumo)
                    ->where('id_area_almacen', $devolucion->id_area_almacen)
                    ->first();

                // if verifica si ya existe un registro de stock para este insumo en el área.
                if ($insumoArea) {

                    // (int) convierte el valor a entero para realizar operaciones numéricas.
                    // + suma el stock actual con la cantidad devuelta.
                    $nuevoStock = (int) $insumoArea->stock + (int) $detalle->cantidad;

                    // update() modifica el campo stock con el nuevo valor calculado.
                    // (string) convierte el entero a cadena porque el campo es de tipo texto.
                    $insumoArea->update(['stock' => (string) $nuevoStock]);

                // else se ejecuta cuando no existe registro de stock para el insumo en esa área.
                } else {

                    // create() inserta un nuevo registro de stock en la tabla insumosarea.
                    InsumoArea::create([
                        'id_insumo'       => $detalle->id_insumo,
                        'id_area_almacen' => $devolucion->id_area_almacen,

                        // Convierte la cantidad a cadena para almacenarla correctamente.
                        'stock'           => (string) $detalle->cantidad,
                        'fondo_fijo'      => 0,
                    ]);
                }
            }

            // update() actualiza los totales y cambia el status a "Terminado".
            // count() cuenta cuántos detalles (insumos) tiene la devolución.
            // sum() suma todos los valores del campo cantidad en los detalles.
            $devolucion->update([
                'status'          => 'Terminado',
                'total_productos' => $devolucion->detalles->count(),
                'total_cantidad'  => $devolucion->detalles->sum('cantidad'),
            ]);
        });

        // Redirige al comprobante de la devolución con un mensaje de éxito.
        // {$devolucion->id_devolucion} inserta el id dentro de la cadena de texto.
        return redirect()
            ->route('devoluciones.comprobante', $devolucion->id_devolucion)
            ->with('exitog', "La devolución DEV-{$devolucion->id_devolucion} ha sido finalizada correctamente.");
    }

    // public permite llamar al método desde fuera de la clase.
    // comprobante() recibe el id de la devolución para mostrar su comprobante.
    public function comprobante($id)
    {
        // with() carga todas las relaciones necesarias para mostrar el comprobante completo.
        // findOrFail() busca la devolución y lanza error 404 si no existe.
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->findOrFail($id);

        // !== compara valor y tipo; si no está terminada no se permite generar el comprobante.
        if ($devolucion->status !== 'Terminado') {
            // redirect() redirige al detalle de la devolución.
            // with() envía un mensaje de error flash a la sesión del usuario.
            return redirect()->route('devoluciones.detalle', $id)
                ->with('error', 'No se puede generar el comprobante de una devolución que no esté terminada.');
        }

        // return devuelve la vista del comprobante con la devolución encontrada.
        return view('inventario.devoluciones.comprobante', compact('devolucion'));
    }

    // ALTERNAR ESTADO (CANCELAR / REACTIVAR)

    // public indica que este método es accesible desde fuera de la clase.
    // toggleStatus() alterna el estado de una devolución entre "En proceso" y "Cancelado".
    // $id es el identificador de la devolución recibido desde la URL.
    public function toggleStatus($id)
    {
        // findOrFail() busca la devolución por su id.
        // Si no existe lanza automáticamente un error 404.
        $devolucion = Devolucion::findOrFail($id);

        // === compara valor y tipo al mismo tiempo.
        // Verifica que el status sea exactamente la cadena "Terminado".
        if ($devolucion->status === 'Terminado') {

            // Una devolución terminada no puede cambiar de estado.
            return redirect()->route('devoluciones.index')
                ->with('error', 'No se puede cambiar el estado de una devolución terminada.');
        }

        // Verifica si el status es exactamente "En proceso" para cancelarla.
        if ($devolucion->status === 'En proceso') {

            // update() cambia el campo status a "Cancelado" en la base de datos.
            $devolucion->update(['status' => 'Cancelado']);

            // Redirige con un mensaje indicando que la devolución fue cancelada.
            return redirect()->route('devoluciones.index')
                ->with('exito', "La devolución DEV-{$devolucion->id_devolucion} ha sido cancelada.");

        // else se ejecuta cuando el status no es "En proceso", es decir, es "Cancelado".
        } else {

            // Reactiva la devolución cambiando su status de vuelta a "En proceso".
            $devolucion->update(['status' => 'En proceso']);

            return redirect()->route('devoluciones.index')
                ->with('exito', "La devolución DEV-{$devolucion->id_devolucion} ha sido reactivada.");
        }
    }

    // TERMINADAS

    // public indica que el método puede ser llamado desde fuera de la clase.
    // terminadas() lista únicamente las devoluciones con status "Terminado".
    // Request $request contiene los filtros de búsqueda enviados desde el formulario.
    public function terminadas(Request $request)
    {
        // get() obtiene el valor del parámetro buscar desde la URL o formulario.
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        // normalizarFecha() convierte las fechas al formato Y-m-d para la base de datos.
        // [] desestructura el arreglo devuelto en dos variables.
        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        // with() carga relaciones para evitar consultas adicionales en la vista.
        // where() filtra solo las devoluciones con status "Terminado".
        $query = Devolucion::with(['areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->where('status', 'Terminado')
            ->orderBy('id_devolucion', 'desc');

        // Aplica el filtro de búsqueda si el usuario escribió algo.
        if (!empty($buscar)) {

            // function() define una función anónima para agrupar condiciones OR.
            // use($buscar) importa la variable al contexto de la función.
            $query->where(function ($q) use ($buscar) {

                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")

                  // fn($q2) => es una función flecha; busca por nombre del área de almacén.
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        // Aplica el filtro de fecha inicio si fue proporcionado.
        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);

        // Aplica el filtro de fecha fin si fue proporcionado.
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        // paginate() divide los resultados en páginas usando la constante PER_PAGE.
        $devoluciones = $query->paginate(self::PER_PAGE)->withQueryString();

        // Carga la vista de terminadas con las variables necesarias.
        return view('inventario.devoluciones.terminadas', compact(
            'devoluciones', 'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    // REPORTES

    // public permite acceder al método desde fuera de la clase.
    // reportes() carga la vista de filtros para generar reportes.
    // Request $request recibe los parámetros opcionales enviados.
    public function reportes(Request $request)
    {
        // Obtiene las áreas de almacén activas para mostrar en los filtros del reporte.
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        // try intenta cargar las áreas de abastecimiento.
        // Se usa try porque esta tabla puede no existir en algunas instalaciones.
        try {
            $areasAbastecimiento = AreaAbastecimiento::orderBy('nombre')->get();

        // catch captura la excepción si la tabla no existe y asigna una colección vacía.
        } catch (\Exception $e) {
            $areasAbastecimiento = collect();
        }

        // Retorna la vista de reportes con las áreas disponibles.
        return view('inventario.devoluciones.reportes', compact('areasAlmacen', 'areasAbastecimiento'));
    }

    // public permite llamar al método desde fuera de la clase.
    // imprimir() genera el reporte de devoluciones aplicando todos los filtros.
    // Request $request contiene los filtros de búsqueda enviados por el usuario.
    public function imprimir(Request $request)
    {
        // Obtiene los parámetros de filtro desde la URL o el formulario.
        $buscar        = $request->get('buscar', '');
        $fechaInit     = $request->get('fecha_inicio', '');
        $fechaFin      = $request->get('fecha_fin', '');
        $idAreaAlmacen = $request->get('id_area_almacen', '');
        $status        = $request->get('status', '');

        // Normaliza ambas fechas al formato Y-m-d para usarlas en la consulta.
        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        // Si el rango de fechas es incoherente se intercambian para no romper la impresión.
        // [] con asignación múltiple intercambia los valores de ambas variables en una sola línea.
        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            [$fechaInitDb, $fechaFinDb] = [$fechaFinDb, $fechaInitDb];
            [$fechaInit,   $fechaFin]   = [$fechaFin,   $fechaInit];
        }

        // with() carga todas las relaciones necesarias para el reporte completo.
        // orderBy() ordena por fecha y hora descendente para mostrar los más recientes primero.
        $query = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->orderBy('fecha_devolucion', 'desc')
            ->orderBy('hora_devolucion', 'desc');

        // Aplica el filtro de texto si el usuario escribió algo en el buscador.
        if (!empty($buscar)) {

            $query->where(function ($q) use ($buscar) {

                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")

                  // fn() => es una función flecha que filtra por nombre del área de almacén.
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        // empty() verifica si la variable está vacía.
        // ! niega el resultado; solo aplica el filtro si hay un valor seleccionado.
        if (!empty($idAreaAlmacen)) $query->where('id_area_almacen', $idAreaAlmacen);

        // Forzamos que los reportes solo listen devoluciones con status "Terminado".
        // Esto previene que se generen reportes de devoluciones en proceso o pendientes.
        $status = 'Terminado';
        $query->where('status', 'Terminado');

        // Aplica el rango de fechas si fueron proporcionadas.
        if ($fechaInitDb)           $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)            $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        // limit() restringe la consulta a un máximo de 500 registros.
        // Evita que se agote la memoria en tablas con muchos datos.
        // get() ejecuta la consulta y devuelve los resultados.
        $devoluciones = $query->limit(500)->get();

        // Carga la vista de impresión con las devoluciones y los filtros aplicados.
        return view('inventario.devoluciones.reporte_impresion', compact(
            'devoluciones', 'buscar', 'fechaInit', 'fechaFin', 'status'
        ));
    }

    // public permite llamar al método desde fuera de la clase.
    // buscarInsumos() devuelve insumos activos en formato JSON para el autocompletado.
    // Request $request contiene el término de búsqueda enviado mediante AJAX.
    public function buscarInsumos(Request $request)
    {
        // get() obtiene el parámetro q (término de búsqueda) de la petición.
        $termino = $request->get('q', '');

        // boolean() convierte el parámetro all a verdadero o falso.
        // Se usa para el modo panel donde se cargan todos los insumos sin límite.
        $all     = $request->boolean('all', false);

        // strlen() mide la longitud del término de búsqueda.
        // < 2 verifica que tenga menos de dos caracteres.
        // Si no está en modo all y el término es muy corto, devuelve vacío.
        if (!$all && strlen($termino) < 2) {

            // response()->json([]) devuelve una respuesta JSON con un arreglo vacío.
            return response()->json([]);
        }

        // where() filtra solo los insumos con el campo activo igual a 1.
        $query = Insumo::where('activo', 1);

        // >= verifica que el término tenga al menos un carácter para aplicar el filtro.
        if (strlen($termino) >= 1) {

            // Agrupa las condiciones OR para buscar por descripción o clave.
            $query->where(function ($q) use ($termino) {

                $q->where('descripcion', 'LIKE', "%{$termino}%")

                  // orWhere() agrega una condición alternativa de búsqueda.
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });
        }

        // select() especifica qué columnas devolver para no cargar datos innecesarios.
        // when() aplica el límite de 20 resultados solo cuando no está en modo all.
        // fn($q) => $q->limit(20) es una función flecha que aplica el límite a la consulta.
        $insumos = $query->select('id_insumo', 'clave', 'descripcion', 'tipo')
            ->orderBy('clave')
            ->when(!$all, fn($q) => $q->limit(20))
            ->get();

        // json() convierte la colección de insumos a formato JSON para la respuesta AJAX.
        return response()->json($insumos);
    }

    // HELPER: Normalizar fecha
    // private restringe el método para que solo pueda usarse dentro de este controlador.
    // normalizarFecha() convierte una fecha de cualquier formato a Y-m-d.
    // ?string indica que el parámetro puede ser una cadena o null.
    // : array indica que el método siempre devuelve un arreglo.
    private function normalizarFecha(?string $fecha): array
    {
        // empty() devuelve true si la cadena está vacía o es null.
        // return [null, ''] devuelve de inmediato un arreglo con dos valores por defecto.
        if (empty($fecha)) return [null, ''];

        // try intenta convertir la fecha; puede fallar si el formato es inválido.
        try {

            // str_contains() verifica si la cadena contiene el carácter /.
            // ? : es el operador ternario; si tiene / usa createFromFormat, si no usa parse.
            $db = str_contains($fecha, '/')
                // createFromFormat() convierte una fecha con formato d/m/Y a objeto Carbon.
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d')
                // parse() interpreta automáticamente el formato de la fecha.
                : \Carbon\Carbon::parse($fecha)->format('Y-m-d');

            // Devuelve la fecha formateada dos veces: una para la BD y otra para el input.
            return [$db, $db];

        // catch captura la excepción si la fecha no pudo ser interpretada.
        } catch (\Exception $e) {

            // Devuelve null y cadena vacía para indicar que la fecha no es válida.
            return [null, ''];
        }
    }
}
