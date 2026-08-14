<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\BajaInsumo;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaSurtimiento;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Motivo;
use App\Traits\ParseaRangoFechas;
use App\Traits\AjustaStockInsumoArea;
use App\Traits\BuscaInsumosAjax;
use App\Traits\ConsultaStockInsumoArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BajasPorAreaExport;

class BajaInsumoController extends Controller
{
    use ParseaRangoFechas, AjustaStockInsumoArea, BuscaInsumosAjax, ConsultaStockInsumoArea;

    // Define el método que muestra el listado principal de bajas.
    // Request $request recibe los filtros enviados por el formulario o por la petición.
    public function index(Request $request)
    {
        // Obtiene el texto de búsqueda.
        // El segundo argumento de get() es el valor que se usa cuando el dato no fue enviado.
        $buscar          = $request->get('buscar', '');
        // Obtiene la fecha inicial del filtro; si no existe, utiliza una cadena vacía.
        $fechaInit        = $request->get('fecha_inicio', '');
        // Obtiene la fecha final del filtro; si no existe, utiliza una cadena vacía.
        $fechaFin         = $request->get('fecha_fin', '');
        // Obtiene el área seleccionada.
        // Primero busca el área de abastecimiento y si no existe, intenta con el área de almacén.
        $filtroArea       = $request->get('id_area_abastecimiento', $request->get('id_area_almacen', ''));
        // Obtiene la categoría seleccionada como filtro.
        $filtroCategoria  = $request->get('id_categoria', '');
        // Define cuántos registros se mostrarán por página.
        $perPage          = 10;

        // Convierte y valida las fechas recibidas.
        // La sintaxis [$a, $b, $c] permite recibir varios valores devueltos por un método.
        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        // Si el método anterior encontró un error, se regresa al formulario.
        if ($errorMsg) {
            // Regresa a la página anterior.
            return redirect()->back()
                // Conserva los valores que el usuario había enviado al formulario.
                ->withInput()
                // Guarda el mensaje de error para mostrarlo después de la redirección.
                ->with('error', $errorMsg);
        }

        // Si existe una fecha inicial procesada, utiliza esa versión para continuar.
        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        // Si existe una fecha final procesada, utiliza esa versión para continuar.
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
        }

        // Inicia la consulta de bajas de insumos.
        // with() carga las relaciones que se necesitarán para mostrar los datos.
        // Se usa antes de get() o paginate() porque todavía se agregarán filtros.
        $query = BajaInsumo::with(['areaAbastecimiento', 'insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            // Ordena las bajas por identificador de mayor a menor.
            // 'desc' coloca primero los registros con el ID más alto.
            ->orderBy('id_baja_insumo', 'desc');

        // Si se recibió un área, agrega ese filtro a la consulta.
        if (!empty($filtroArea)) {
            // Agrupa las condiciones del área.
            // function() crea una función anónima y use() permite utilizar $filtroArea dentro de ella.
            $query->where(function ($q) use ($filtroArea) {
                // Comprueba directamente el área asignada guardada en la baja.
                $q->where('id_area_abastecimiento', $filtroArea)
                  // También permite que coincida directamente el área de almacén.
                  ->orWhere('id_area_almacen', $filtroArea)
                  // Fallback para registros históricos previos a la migración.
                  ->orWhereHas('insumo', function ($q2) use ($filtroArea) {
                      $q2->where('id_area_abastecimiento', $filtroArea)
                         ->orWhere('id_area_surtimiento', $filtroArea);
                  });
            });
        }

        // Si el usuario escribió una búsqueda, agrega las condiciones correspondientes.
        if (!empty($buscar)) {
            // Agrupa las diferentes opciones donde puede aparecer el texto.
            // Esto permite combinar las condiciones mediante OR sin perder el grupo.
            $query->where(function ($q) use ($buscar) {
                // Busca el texto dentro del motivo.
                // LIKE con '%' permite encontrar el texto aunque tenga otros caracteres antes o después.
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  // También busca dentro del insumo relacionado.
                  // orWhereHas() funciona como una alternativa a la condición anterior y además consulta una relación.
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      // Busca el texto dentro de la descripción del insumo.
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         // También busca dentro de la clave del insumo.
                         ->orWhere('clave', 'LIKE', "%{$buscar}%")
                         // Busca también dentro del área de abastecimiento relacionada.
                         // fn() es una función flecha; se usa aquí porque solo contiene una expresión.
                         ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
                  })
                  // Como última alternativa, busca dentro del área de almacén relacionada.
                  ->orWhereHas('areaAlmacen', function ($q3) use ($buscar) {
                      // Compara el nombre del área con el texto buscado.
                      $q3->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        // Si existe una fecha inicial, agrega el límite inferior al filtro.
        if ($fechaInitDb) {
            // whereDate() compara solamente la fecha y no la hora.
            // Se usa aquí porque el filtro trabaja con días completos.
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        // Si existe una fecha final, agrega el límite superior al filtro.
        if ($fechaFinDb) {
            // '<=' permite incluir también los registros del día indicado como fecha final.
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        // Si se seleccionó una categoría, filtra los insumos cuya categoría coincida.
        if (!empty($filtroCategoria)) {
            $query->whereHas('insumo', function ($q) use ($filtroCategoria) {
                $q->where('id_categoria', $filtroCategoria);
            });
        }

        // Comprueba si la petición llegó mediante AJAX.
        // Si es AJAX, este método devolverá sugerencias en JSON en lugar de la vista completa.
        if ($request->ajax()) {
            // Crea el arreglo donde se guardarán las sugerencias encontradas.
            $sugerencias = [];
            // Ejecuta la consulta y obtiene solo 10 registros.
            // get() ejecuta la consulta; aquí se usa porque los resultados se recorrerán inmediatamente.
            $records = $query->limit(10)->get();

            // Recorre cada baja encontrada para construir las sugerencias.
            foreach ($records as $b) {
                // Comprueba que la baja tenga un insumo relacionado antes de acceder a sus datos.
                if ($b->insumo) {
                    // Agrega una nueva sugerencia al final del arreglo.
                    $sugerencias[] = [
                        // Guarda la descripción del insumo como texto de la sugerencia.
                        'text'   => $b->insumo->descripcion,
                        // Indica que esta sugerencia corresponde a un insumo.
                        'type'   => 'Insumo',
                        // Guarda la clave del insumo como información adicional.
                        'detail' => $b->insumo->clave
                    ];
                }
                // Comprueba que exista un área de almacén relacionada.
                if ($b->areaAlmacen) {
                    // Agrega el área como una nueva sugerencia.
                    $sugerencias[] = [
                        // Guarda el nombre del área como texto de la sugerencia.
                        'text'   => $b->areaAlmacen->nombre,
                        // Indica que esta sugerencia corresponde a un área.
                        'type'   => 'Área',
                        // No se necesita información adicional para este tipo de sugerencia.
                        'detail' => ''
                    ];
                }
                // Comprueba que el motivo tenga algún contenido.
                if (!empty($b->motivo)) {
                    // Agrega el motivo como una nueva sugerencia.
                    $sugerencias[] = [
                        // Guarda el motivo como texto.
                        'text'   => $b->motivo,
                        // Indica que esta sugerencia corresponde a un motivo.
                        'type'   => 'Motivo',
                        // Este tipo de sugerencia no necesita información adicional.
                        'detail' => ''
                    ];
                }
            }

            // Crea el arreglo donde se guardarán las sugerencias sin repetir.
            $uniqueSugerencias = [];
            // Guarda las claves que ya fueron encontradas para detectar duplicados.
            $seen = [];

            // Recorre todas las sugerencias generadas anteriormente.
            foreach ($sugerencias as $sug) {
                // Convierte el texto a minúsculas para comparar sin distinguir mayúsculas y minúsculas.
                // strtolower() se usa porque la comparación debe tratar 'Área' y 'área' como el mismo texto.
                $key = strtolower($sug['text']);
                // Comprueba si esa sugerencia todavía no ha sido registrada.
                if (!isset($seen[$key])) {
                    // Marca el texto como ya encontrado.
                    $seen[$key] = true;
                    // Agrega la sugerencia al arreglo final sin duplicarla.
                    $uniqueSugerencias[] = $sug;
                }
            }

            // Devuelve las sugerencias en formato JSON.
            // response()->json() se usa porque la respuesta será consumida por AJAX/JavaScript.
            return response()->json(array_values($uniqueSugerencias));
        }

        // Ejecuta la consulta principal con paginación.
        // paginate() se usa en lugar de get() para dividir los resultados en páginas.
        // withQueryString() conserva los filtros actuales al cambiar de página.
        $bajas = $query->paginate($perPage)->withQueryString();

        // Obtiene las áreas de almacén que están activas.
        $areas = AreaAlmacen::where('activo', 1)
            // Ordena las áreas por nombre.
            ->orderBy('nombre')
            // Ejecuta la consulta y obtiene los resultados.
            ->get();

        // Obtiene las áreas de surtimiento activas.
        $areasSurtimiento = AreaSurtimiento::where('activo', 1)
            // Ordena las áreas por nombre.
            ->orderBy('nombre')
            // Ejecuta la consulta y obtiene los resultados.
            ->get();

        // Obtiene las áreas de abastecimiento activas.
        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)
            // Ordena las áreas por nombre.
            ->orderBy('nombre')
            // Ejecuta la consulta y obtiene los resultados.
            ->get();

        // Obtiene los motivos que están activos.
        $motivos = Motivo::where('activo', 1)
            ->orderBy('descripcion')
            ->get();

        // Obtiene las categorías de insumos ordenadas por nombre.
        $categorias = Categoria::orderBy('nombre_categoria')->get();

        // Devuelve la vista principal de bajas de insumos.
        // view() indica qué archivo Blade se mostrará.
        return view(
            // Indica la ruta de la vista que se debe cargar.
            'inventario.bajas_insumos.index',
            // compact() crea un arreglo usando los nombres de las variables.
            // Se usa para enviar varios valores a la vista de forma breve y ordenada.
            compact('bajas', 'areas', 'areasSurtimiento', 'areasAbastecimiento', 'categorias', 'motivos', 'buscar', 'fechaInit', 'fechaFin', 'filtroArea', 'filtroCategoria')
        );
    }





    // Recibe la petición que contiene los datos de la nueva baja.
    public function guardar(Request $request)
    {
        // Valida los datos antes de realizar cambios en la base de datos.
        // validate() detiene el proceso y devuelve errores automáticamente si algún dato no cumple las reglas.
        $request->validate([
            // El insumo es obligatorio, debe ser un número entero y debe existir en la tabla de insumos.
            'id_insumo'              => 'required|integer|exists:insumos,id_insumo',
            // El área de almacén es obligatoria, debe ser un entero y debe existir.
            'id_area_almacen'        => 'required|integer|exists:areas_almacen,id_area_almacen',
            // El área de abastecimiento es opcional, pero si se envía debe ser un entero válido y existir.
            'id_area_abastecimiento' => 'nullable|integer|exists:areasabastecimiento,id_area_abastecimiento',
            // El motivo es obligatorio, debe ser texto y no puede superar 500 caracteres.
            'motivo'                 => 'required|string|max:500',
            'motivo_otro'            => 'required_if:motivo,Otro|nullable|string|max:500',
            'iniciales_paciente'     => 'nullable|string|max:100',
            'no_expediente'          => 'nullable|string|max:100',
            'doctor_nombre'          => 'nullable|string|max:200',
            'doctor_especialidad'    => 'nullable|string|max:200',
            'persona_entrega'        => 'nullable|string|max:200',
            'cantidad'               => 'required|integer|min:1',
        ], [
            // Mensaje para indicar que se debe seleccionar un insumo.
            'id_insumo.required'              => 'Debe seleccionar un insumo.',
            // Mensaje para indicar que el insumo seleccionado no existe.
            'id_insumo.exists'                => 'El insumo seleccionado no existe.',
            // Mensaje para indicar que se debe seleccionar un área de almacén.
            'id_area_almacen.required'        => 'Debe seleccionar un área de almacén.',
            // Mensaje para indicar que el área de almacén no existe.
            'id_area_almacen.exists'          => 'El área seleccionada no existe.',
            // Mensaje para indicar que el área de abastecimiento no existe.
            'id_area_abastecimiento.exists'   => 'El área de asignación seleccionada no existe.',
            // Mensaje para indicar que el motivo es obligatorio.
            'motivo.required'                 => 'El motivo de la baja es obligatorio.',
            'motivo_otro.required_if'         => 'Debe especificar el motivo cuando selecciona la opción "Otro".',
            'motivo_otro.max'                 => 'El motivo alternativo no puede superar los 500 caracteres.',
            // Mensaje para indicar que el motivo superó el límite permitido.
            'motivo.max'                      => 'El motivo no puede superar los 500 caracteres.',
            'iniciales_paciente.max'          => 'Las iniciales del paciente no pueden superar 100 caracteres.',
            'no_expediente.max'               => 'El número de expediente no puede superar 100 caracteres.',
            // Mensaje para indicar que el nombre del doctor superó el límite permitido.
            'doctor_nombre.max'               => 'El nombre del doctor no puede superar 200 caracteres.',
            // Mensaje para indicar que la especialidad superó el límite permitido.
            'doctor_especialidad.max'         => 'La especialidad del doctor no puede superar 200 caracteres.',
            'persona_entrega.max'             => 'La persona quien entrega no puede superar 200 caracteres.',
            // Mensaje para indicar que no se recibió una cantidad.
            'cantidad.required'               => 'La cantidad es obligatoria.',
            // Mensaje para indicar que la cantidad debe ser al menos 1.
            'cantidad.min'                    => 'La cantidad debe ser al menos 1.',
        ]);

        // Determina el valor final del motivo
        $motivoFinal = ($request->motivo === 'Otro' && $request->filled('motivo_otro'))
            ? trim($request->motivo_otro)
            : trim($request->motivo);

        // Busca el registro que relaciona el insumo con el área de almacén seleccionada.
        // where() agrega condiciones y first() obtiene solamente el primer registro encontrado.
        $insumoArea = InsumoArea::where('id_insumo', $request->id_insumo)
            // Limita la búsqueda al área de almacén recibida.
            ->where('id_area_almacen', $request->id_area_almacen)
            // Ejecuta la consulta y devuelve el primer registro encontrado.
            ->first();

        // Si no existe la relación entre el insumo y el área, no se puede realizar la baja.
        if (!$insumoArea) {
            // Regresa al formulario anterior.
            return redirect()->back()
                // Conserva los datos enviados por el usuario.
                ->withInput()
                // Agrega un error asociado específicamente al campo cantidad.
                ->withErrors([
                    'cantidad' => 'El insumo no tiene existencia en el área seleccionada.'
                ]);
        }

        // Convierte el stock actual a entero.
        // El cast '(int)' asegura que el valor se trate como un número entero antes de compararlo.
        $stockActual = (int) $insumoArea->stock;

        // Comprueba que la cantidad solicitada no sea mayor que el stock disponible.
        if ($request->cantidad > $stockActual) {
            // Regresa al formulario porque no se puede completar la operación.
            return redirect()->back()
                // Conserva los datos que el usuario había enviado.
                ->withInput()
                // Agrega un error relacionado con la cantidad solicitada.
                ->withErrors([
                    'cantidad' => "La cantidad excede el stock disponible ({$stockActual} piezas)."
                ]);
        }

        // Inicia una transacción para que todos los cambios se guarden como una sola operación.
        // use() permite que la función anónima utilice las variables recibidas desde este método.
        DB::transaction(function () use ($request, $insumoArea, $motivoFinal) {
            // Crea el registro de la nueva baja guardando el área de abastecimiento de forma inmutable.
            BajaInsumo::create([
                // Guarda el insumo al que pertenece la baja.
                'id_insumo'              => $request->id_insumo,
                // Guarda el área de almacén donde se realiza la baja.
                'id_area_almacen'        => $request->id_area_almacen,
                // Guarda el área de asignación en el registro de la baja sin modificar el catálogo de insumos.
                'id_area_abastecimiento' => $request->filled('id_area_abastecimiento')
                    ? $request->id_area_abastecimiento
                    : ($insumoArea->insumo->id_area_abastecimiento ?? null),
                // Guarda el motivo final (seleccionado o ingresado manualmente en "Otro").
                'motivo'                 => $motivoFinal,
                // Guarda las iniciales del paciente si fueron enviadas.
                'iniciales_paciente'     => $request->filled('iniciales_paciente') ? trim($request->iniciales_paciente) : null,
                // Guarda el número de expediente si fue enviado.
                'no_expediente'          => $request->filled('no_expediente') ? trim($request->no_expediente) : null,
                // Guarda el nombre del doctor si fue enviado; de lo contrario guarda null.
                'doctor_nombre'          => $request->filled('doctor_nombre') ? trim($request->doctor_nombre) : null,
                // Guarda la especialidad del doctor si fue enviada.
                'doctor_especialidad'    => $request->filled('doctor_especialidad') ? trim($request->doctor_especialidad) : null,
                // Guarda la persona quien entrega si fue enviada.
                'persona_entrega'        => $request->filled('persona_entrega') ? trim($request->persona_entrega) : null,
                // Guarda la cantidad que será descontada del stock.
                'cantidad'               => $request->cantidad,
                // Guarda la fecha actual de la baja en formato de fecha.
                'fecha_baja'             => now()->toDateString(),
                // Guarda la hora actual de la baja.
                'hora_baja'              => now()->toTimeString(),
                // Guarda el ID del usuario actual.
                'id_usuario'             => Auth::id() ?? 1,
                // Marca inicialmente la baja como no cancelada.
                'cancelado'              => 'No',
            ]);

            // Descuenta la cantidad registrada del stock del insumo en esa área.
            // Se llama al método del trait para reutilizar la lógica de modificación del stock.
            $this->ajustarStockInsumoArea($insumoArea, (int) $request->cantidad, 'restar');
        });

        // Redirige al listado de bajas después de completar la operación.
        return redirect()
            // Indica la ruta que debe abrirse después del registro.
            ->route('bajas_insumos.index')
            // Guarda un mensaje de éxito para mostrarlo al usuario.
            ->with('exitog', 'La baja de insumo se ha registrado correctamente.');
    }

    // Recibe la petición con los filtros del historial.
    public function historialPorAreaAsignada(Request $request)
    {
        // Obtiene el área de abastecimiento o, si no existe, el área de surtimiento.
        // Se usa el segundo get() como valor alternativo para aceptar ambos tipos de filtro.
        $idAreaAbastecimiento = $request->get('id_area_abastecimiento', $request->get('id_area_surtimiento', ''));
        // Obtiene la fecha inicial del filtro.
        $fechaInit            = $request->get('fecha_inicio', '');
        // Obtiene la fecha final del filtro.
        $fechaFin             = $request->get('fecha_fin', '');

        // Procesa las fechas antes de utilizarlas en la consulta.
        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        // Si hay un error de fechas y la petición no es AJAX, regresa al formulario.
        if ($errorMsg && !$request->ajax()) {
            // Regresa a la página anterior conservando los datos y mostrando el error.
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        // Construye la consulta del historial y carga las relaciones necesarias.
        $query = BajaInsumo::with(['areaAbastecimiento', 'insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            // Ordena el historial colocando primero las bajas más recientes por ID.
            ->orderBy('id_baja_insumo', 'desc');

        // Si se recibió un área, aplica el filtro correspondiente.
        if (!empty($idAreaAbastecimiento)) {
            $query->where(function ($q) use ($idAreaAbastecimiento) {
                $q->where('id_area_abastecimiento', $idAreaAbastecimiento)
                  ->orWhereHas('insumo', function ($q2) use ($idAreaAbastecimiento) {
                      $q2->where('id_area_abastecimiento', $idAreaAbastecimiento)
                         ->orWhere('id_area_surtimiento', $idAreaAbastecimiento);
                  });
            });
        }

        // Si existe fecha inicial, limita el historial desde ese día.
        if ($fechaInitDb) {
            // Compara únicamente la parte de fecha del campo fecha_baja.
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        // Si existe fecha final, limita el historial hasta ese día.
        if ($fechaFinDb) {
            // Incluye los registros del día indicado como fecha final.
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        // Obtiene el historial paginado, mostrando 15 registros por página.
        $bajas = $query->paginate(15)->withQueryString();

        // Comprueba si la petición del historial llegó mediante AJAX.
        if ($request->ajax()) {
            // Renderiza la vista parcial de la tabla y convierte su contenido en HTML.
            // render() se usa para obtener el HTML de una vista en forma de texto.
            $html = view('inventario.bajas_insumos.partials.tabla_historial_area', compact('bajas'))->render();
            // Devuelve el HTML y el total de registros en formato JSON para que AJAX pueda actualizar la pantalla.
            return response()->json(['html' => $html, 'total' => $bajas->total()]);
        }

        // Obtiene las áreas de abastecimiento activas y las ordena por nombre.
        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        // Muestra la vista completa del historial junto con los datos necesarios.
        return view('inventario.bajas_insumos.historial_area', compact('bajas', 'areasAbastecimiento', 'idAreaAbastecimiento', 'fechaInit', 'fechaFin'));
    }

    // Recibe los filtros enviados para generar el reporte.
    public function exportarExcel(Request $request)
    {
        // Valida los filtros antes de realizar la consulta.
        $request->validate([
            'id_area_abastecimiento' => 'nullable|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'id_area_surtimiento'    => 'nullable|integer',
            'id_categoria'           => 'nullable|integer|exists:categorias,id_categoria',
            'fecha_inicio'           => 'required|date',
            'fecha_fin'              => 'required|date',
        ]);

        // Obtiene el área de abastecimiento o, como alternativa, el área de surtimiento.
        $idAreaAbastecimiento = $request->get('id_area_abastecimiento', $request->get('id_area_surtimiento', ''));
        // Obtiene la categoría seleccionada.
        $idCategoria          = $request->get('id_categoria', '');
        // Obtiene la fecha inicial del reporte.
        $fechaInit            = $request->get('fecha_inicio', '');
        // Obtiene la fecha final del reporte.
        $fechaFin             = $request->get('fecha_fin', '');

        // Prepara las fechas para utilizarlas en la consulta.
        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        // Construye la consulta del reporte y carga las relaciones que se mostrarán en el Excel.
        $query = BajaInsumo::with(['areaAbastecimiento', 'insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'insumo.categoria', 'areaAlmacen'])
            // Ordena primero por fecha, colocando las más recientes arriba.
            ->orderBy('fecha_baja', 'desc')
            // Cuando las fechas son iguales, ordena por hora de forma descendente.
            ->orderBy('hora_baja', 'desc');

        // Si existe un área seleccionada, aplica el filtro al reporte.
        // La lógica es idéntica a la del método index() para garantizar coherencia.
        if (!empty($idAreaAbastecimiento)) {
            $query->where(function ($q) use ($idAreaAbastecimiento) {
                // Comprueba directamente el área asignada guardada en la baja.
                $q->where('id_area_abastecimiento', $idAreaAbastecimiento)
                  // También permite que coincida el área de almacén (registros históricos).
                  ->orWhere('id_area_almacen', $idAreaAbastecimiento)
                  // Fallback: verifica en el insumo relacionado.
                  ->orWhereHas('insumo', function ($q2) use ($idAreaAbastecimiento) {
                      $q2->where('id_area_abastecimiento', $idAreaAbastecimiento)
                         ->orWhere('id_area_surtimiento', $idAreaAbastecimiento);
                  });
            });
        }

        // Si existe una categoría seleccionada, filtra los insumos por esa categoría.
        if (!empty($idCategoria)) {
            $query->whereHas('insumo', function ($q) use ($idCategoria) {
                $q->where('id_categoria', $idCategoria);
            });
        }

        // Aplica el límite inferior de fecha cuando existe.
        if ($fechaInitDb) {
            // Compara únicamente la fecha de la baja.
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        // Aplica el límite superior de fecha cuando existe.
        if ($fechaFinDb) {
            // Incluye las bajas realizadas durante la fecha final.
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        // Ejecuta la consulta y obtiene todos los registros para construir el reporte.
        $bajas = $query->get();

        // Busca el área y la categoría seleccionadas para los títulos del reporte.
        $areaSeleccionada = !empty($idAreaAbastecimiento) ? AreaAbastecimiento::find($idAreaAbastecimiento) : null;
        $categoriaSeleccionada = !empty($idCategoria) ? Categoria::find($idCategoria) : null;

        // Determina la agrupación del reporte:
        // Si el área es "Todas las áreas" (vacío) y hay una categoría específica, usa el nombre de la categoría como título principal del grupo.
        if (empty($idAreaAbastecimiento) && $categoriaSeleccionada) {
            $bajasPorArea = $bajas->groupBy(function () use ($categoriaSeleccionada) {
                return $categoriaSeleccionada->nombre_categoria;
            });
        } elseif ($areaSeleccionada && $categoriaSeleccionada) {
            $bajasPorArea = $bajas->groupBy(function () use ($areaSeleccionada, $categoriaSeleccionada) {
                return $areaSeleccionada->nombre . ' - ' . $categoriaSeleccionada->nombre_categoria;
            });
        } else {
            $bajasPorArea = $bajas->groupBy(function ($baja) {
                return $baja->areaAbastecimiento->nombre 
                    ?? $baja->insumo->areaAbastecimiento->nombre 
                    ?? $baja->insumo->areaSurtimiento->nombre 
                    ?? $baja->motivo
                    ?? 'Sin Área Asignada';
            });
        }

        // Construye el nombre del archivo usando la fecha y hora actuales.
        // El operador '.' concatena varias cadenas para formar un solo nombre.
        $filename = 'Reporte_Bajas_Por_Area_Asignada_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Genera la descarga del archivo XLSX real a partir de la vista Blade
        return Excel::download(
            new BajasPorAreaExport($bajasPorArea, $areaSeleccionada, $fechaInit, $fechaFin),
            $filename
        );
    }

    // Recibe el ID de la baja que se quiere cambiar de estado.
    public function toggleStatus($id)
    {
        // Busca la baja por su ID.
        // findOrFail() se usa porque si no existe el registro, Laravel genera automáticamente una respuesta de error.
        $baja = BajaInsumo::findOrFail($id);

        // Comprueba si la baja actualmente no está cancelada.
        if ($baja->cancelado === 'No') {
            // Inicia una transacción porque se actualizará el stock y el estado de la baja.
            // Ambos cambios deben completarse juntos.
            DB::transaction(function () use ($baja) {
                // Busca la relación entre el insumo y el área donde se realizó la baja.
                $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                    // Limita la búsqueda al mismo insumo y al mismo almacén de la baja.
                    ->where('id_area_almacen', $baja->id_area_almacen)
                    // Obtiene el primer registro encontrado.
                    ->first();

                // Solo ajusta el stock si existe el registro del insumo en esa área.
                if ($insumoArea) {
                    // Suma nuevamente al stock la cantidad que había sido dada de baja.
                    $this->ajustarStockInsumoArea($insumoArea, (int) $baja->cantidad, 'sumar');
                }

                // Actualiza el estado de la baja.
                $baja->update([
                    // 'Si' indica que la baja quedó cancelada.
                    'cancelado' => 'Si'
                ]);
            });

            // Regresa al listado después de cancelar la baja.
            return redirect()
                // Indica la ruta de destino.
                ->route('bajas_insumos.index')
                // Guarda un mensaje de resultado para mostrarlo al usuario.
                ->with(
                    // Tipo de mensaje utilizado para indicar una operación exitosa.
                    'exito',
                    // Informa que la baja fue cancelada y el stock restaurado.
                    'La baja de insumo ha sido cancelada y el stock restaurado.'
                );
        // Si la baja ya estaba cancelada, entra en la lógica para reactivarla.
        } else {
            // Busca nuevamente el registro de stock del mismo insumo.
            $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                // Limita la búsqueda al mismo almacén donde se realizó la baja.
                ->where('id_area_almacen', $baja->id_area_almacen)
                // Obtiene el primer registro encontrado.
                ->first();

            // Si no existe el registro de stock, no se puede reactivar la baja.
            if (!$insumoArea) {
                // Regresa al listado.
                return redirect()
                    // Indica la ruta de destino.
                    ->route('bajas_insumos.index')
                    // Prepara un mensaje de error para la redirección.
                    ->with(
                        // Tipo de mensaje que se mostrará como error.
                        'error',
                        // Explica por qué no se puede continuar con la reactivación.
                        'El insumo no tiene registro de stock en la misma área.'
                    );
            }

            // Convierte el stock actual a entero antes de compararlo.
            $stockActual = (int) $insumoArea->stock;

            // Comprueba que exista suficiente stock para volver a realizar la baja.
            if ($baja->cantidad > $stockActual) {
                // Regresa al listado si no hay stock suficiente.
                return redirect()
                    // Indica la ruta de destino.
                    ->route('bajas_insumos.index')
                    // Prepara el mensaje de error.
                    ->with(
                        // Indica que el mensaje corresponde a un error.
                        'error',
                        // Informa cuánto stock existe y cuánto se necesita descontar.
                        "No se puede reactivar la baja. El stock disponible ({$stockActual} piezas) es insuficiente para dar de baja {$baja->cantidad} piezas."
                    );
            }

            // Inicia una transacción para que el descuento de stock y el cambio de estado se realicen juntos.
            DB::transaction(function () use ($baja, $insumoArea) {
                // Vuelve a descontar del stock la cantidad de la baja.
                $this->ajustarStockInsumoArea($insumoArea, (int) $baja->cantidad, 'restar');

                // Actualiza nuevamente el estado de la baja.
                $baja->update([
                    // 'No' indica que la baja vuelve a estar activa.
                    'cancelado' => 'No'
                ]);
            });

            // Regresa al listado después de reactivar la baja.
            return redirect()
                // Indica la ruta de destino.
                ->route('bajas_insumos.index')
                // Guarda el mensaje de resultado.
                ->with(
                    // Tipo de mensaje utilizado para una operación exitosa.
                    'exito',
                    // Informa que la baja fue reactivada y el stock volvió a descontarse.
                    'La baja de insumo ha sido reactivada y el stock descontado.'
                );
        }
    }

    // Recibe los filtros que se utilizarán para generar el reporte.
    public function imprimir(Request $request)
    {
        // Obtiene el texto de búsqueda.
        $buscar     = $request->get('buscar', '');
        // Obtiene la fecha inicial.
        $fechaInit  = $request->get('fecha_inicio', '');
        // Obtiene la fecha final.
        $fechaFin   = $request->get('fecha_fin', '');
        // Obtiene el área seleccionada, aceptando abastecimiento o almacén como alternativa.
        $filtroArea = $request->get('id_area_abastecimiento', $request->get('id_area_almacen', ''));

        // Procesa y valida el rango de fechas.
        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        // Si existe un error de fechas, regresa al formulario.
        if ($errorMsg) {
            // Regresa a la página anterior.
            return redirect()->back()
                // Conserva los datos enviados.
                ->withInput()
                // Guarda el mensaje de error.
                ->with('error', $errorMsg);
        }

        // Si existe una fecha inicial procesada, reemplaza la original.
        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        // Si existe una fecha final procesada, reemplaza la original.
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
        }

        // Construye la consulta del reporte de impresión.
        // with() carga las relaciones que se utilizarán en la vista.
        $query = BajaInsumo::with(['areaAbastecimiento', 'insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            // Ordena las bajas por fecha, de la más reciente a la más antigua.
            ->orderBy('fecha_baja', 'desc')
            // Dentro de la misma fecha, ordena por hora de forma descendente.
            ->orderBy('hora_baja', 'desc');

        // Si existe un filtro de área, agrega las condiciones correspondientes.
        if (!empty($filtroArea)) {
            // Agrupa las condiciones para que las alternativas del área se evalúen juntas.
            $query->where(function ($q) use ($filtroArea) {
                $q->where('id_area_abastecimiento', $filtroArea)
                  // También acepta que el área coincida directamente con el almacén.
                  ->orWhere('id_area_almacen', $filtroArea)
                  // Fallback para registros históricos previos a la migración.
                  ->orWhereHas('insumo', function ($q2) use ($filtroArea) {
                      $q2->where('id_area_abastecimiento', $filtroArea)
                         ->orWhere('id_area_surtimiento', $filtroArea);
                  });
            });
        }

        // Si existe texto de búsqueda, agrega los filtros de texto.
        if (!empty($buscar)) {
            // Agrupa las diferentes opciones donde puede encontrarse el texto.
            $query->where(function ($q) use ($buscar) {
                // Busca el texto dentro del motivo.
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  // También busca dentro del insumo relacionado.
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      // Busca dentro de la descripción del insumo.
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         // Busca también dentro de la clave del insumo.
                         ->orWhere('clave', 'LIKE', "%{$buscar}%")
                         // Busca dentro del área de abastecimiento relacionada.
                         ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
                  })
                  // También busca dentro del área de almacén.
                  ->orWhereHas('areaAlmacen', function ($q3) use ($buscar) {
                      // Compara el nombre del área con el texto buscado.
                      $q3->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        // Aplica el límite inferior de fecha.
        if ($fechaInitDb) {
            // Compara solamente la fecha de la baja.
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        // Aplica el límite superior de fecha.
        if ($fechaFinDb) {
            // Incluye los registros de la fecha final.
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        // Ejecuta la consulta y obtiene como máximo 500 registros.
        // limit() se usa para evitar cargar una cantidad ilimitada de datos en el reporte.
        $bajas = $query->limit(500)->get();

        // Busca el área que corresponde al filtro seleccionado.
        // Si no hay filtro, utiliza null para indicar que no existe un área seleccionada.
        $areaFiltrada = !empty($filtroArea) ? AreaAbastecimiento::find($filtroArea) : null;

        // Devuelve la vista utilizada para imprimir el reporte.
        return view(
            // Indica la vista Blade que contiene el formato de impresión.
            'inventario.bajas_insumos.reporte_impresion',
            // Envía a la vista los datos obtenidos y los filtros utilizados.
            // compact() crea el arreglo con las variables que la vista necesita.
            compact('bajas', 'buscar', 'fechaInit', 'fechaFin', 'areaFiltrada')
        );
    }


}