<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\BajaInsumo;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\AreaAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BajaInsumoController extends Controller
{
    // public indica que el método puede ser llamado desde fuera de la clase.
// function define un método.
// index es el nombre del método.
// Request $request es un parámetro tipado; obliga a recibir un objeto Request.
public function index(Request $request)
{
    // $ indica una variable.
    // -> accede a métodos o propiedades de un objeto.
    // get() obtiene un valor de la petición.
    // '' es una cadena vacía usada como valor por defecto.
    $buscar    = $request->get('buscar', '');

    // Asignación de otro parámetro recibido.
    $fechaInit = $request->get('fecha_inicio', '');

    // Asignación de otro parámetro recibido.
    $fechaFin  = $request->get('fecha_fin', '');

    // Asignación directa de un valor entero.
    $perPage   = 10;

    // null representa ausencia de valor.
    $fechaInitDb = null;

    // if crea una estructura condicional.
    // ! niega el resultado de una expresión.
    // empty() verifica si una variable está vacía.
    if (!empty($fechaInit)) {

        // try intenta ejecutar código que podría generar excepciones.
        try {

            // strpos() busca una cadena dentro de otra.
            // !== compara valor y tipo.
            if (strpos($fechaInit, '/') !== false) {

                // :: accede a métodos estáticos de una clase.
                // \ indica espacio de nombres global.
                // -> encadena métodos.
                $fechaInitDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaInit)
                    ->format('Y-m-d');

            } else {

                // parse() interpreta automáticamente una fecha.
                $fechaInitDb = \Carbon\Carbon::parse($fechaInit)
                    ->format('Y-m-d');
            }

        // catch captura excepciones generadas dentro del try.
        } catch (\Exception $e) {

            try {

                $fechaInitDb = \Carbon\Carbon::parse($fechaInit)
                    ->format('Y-m-d');

            } catch (\Exception $ex) {

                // Reasignación de una cadena vacía.
                $fechaInit = '';
            }
        }
    }

    // Inicialización con valor null.
    $fechaFinDb = null;

    if (!empty($fechaFin)) {

        try {

            if (strpos($fechaFin, '/') !== false) {

                $fechaFinDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFin)
                    ->format('Y-m-d');

            } else {

                $fechaFinDb = \Carbon\Carbon::parse($fechaFin)
                    ->format('Y-m-d');
            }

        } catch (\Exception $e) {

            try {

                $fechaFinDb = \Carbon\Carbon::parse($fechaFin)
                    ->format('Y-m-d');

            } catch (\Exception $ex) {

                $fechaFin = '';
            }
        }
    }

    // && significa AND lógico.
    // > compara si un valor es mayor que otro.
    if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {

        // redirect() crea una redirección.
        // -> encadena métodos.
        return redirect()->back()
            ->withInput()
            ->with('error', 'La fecha de inicio no puede ser posterior a la fecha de fin.');
    }

    if ($fechaInitDb) {

        // = asigna un valor.
        $fechaInit = $fechaInitDb;
    }

    if ($fechaFinDb) {

        $fechaFin = $fechaFinDb;
    }

    // :: acceso estático al modelo.
    // with() carga relaciones.
    // [] define un arreglo.
    $query = BajaInsumo::with(['insumo', 'areaAlmacen'])
        ->orderBy('id_baja_insumo', 'desc');

    if (!empty($buscar)) {

        // function define una función anónima (closure).
        // ($q) define parámetros.
        // use() permite utilizar variables externas.
        $query->where(function ($q) use ($buscar) {

            $q->where('motivo', 'LIKE', "%{$buscar}%")

              // -> encadena consultas.
              ->orWhereHas('insumo', function ($q2) use ($buscar) {

                  $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                     ->orWhere('clave', 'LIKE', "%{$buscar}%");
              })

              ->orWhereHas('areaAlmacen', function ($q3) use ($buscar) {

                  $q3->where('nombre', 'LIKE', "%{$buscar}%");
              });
        });
    }

    if ($fechaInitDb) {

        // whereDate compara únicamente la parte de fecha.
        $query->whereDate('fecha_baja', '>=', $fechaInitDb);
    }

    if ($fechaFinDb) {

        // <= significa menor o igual.
        $query->whereDate('fecha_baja', '<=', $fechaFinDb);
    }

    // ajax() verifica si la petición fue realizada mediante AJAX.
    if ($request->ajax()) {

        // [] crea un arreglo vacío.
        $sugerencias = [];

        // get() ejecuta la consulta.
        $records = $query->limit(10)->get();

        // foreach recorre cada elemento de una colección.
        foreach ($records as $b) {

            // Verifica si existe un valor.
            if ($b->insumo) {

                // [] agrega un elemento al arreglo.
                // => asigna clave => valor.
                $sugerencias[] = [
                    'text' => $b->insumo->descripcion,
                    'type' => 'Insumo',
                    'detail' => $b->insumo->clave
                ];
            }

            if ($b->areaAlmacen) {

                $sugerencias[] = [
                    'text' => $b->areaAlmacen->nombre,
                    'type' => 'Área',
                    'detail' => ''
                ];
            }

            if (!empty($b->motivo)) {

                $sugerencias[] = [
                    'text' => $b->motivo,
                    'type' => 'Motivo',
                    'detail' => ''
                ];
            }
        }

        $uniqueSugerencias = [];

        $seen = [];

        foreach ($sugerencias as $sug) {

            // strtolower() convierte texto a minúsculas.
            $key = strtolower($sug['text']);

            // isset() verifica si existe un índice o variable.
            // ! niega el resultado.
            if (!isset($seen[$key])) {

                $seen[$key] = true;

                $uniqueSugerencias[] = $sug;
            }
        }

        // response() genera una respuesta HTTP.
        // json() convierte datos a formato JSON.
        // array_values() reindexa un arreglo.
        return response()->json(array_values($uniqueSugerencias));
    }

    // paginate() divide resultados en páginas.
    $bajas = $query->paginate($perPage)

        // withQueryString() conserva parámetros de URL.
        ->withQueryString();

    // where() agrega una condición.
    // get() ejecuta la consulta.
    $areas = AreaAlmacen::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    // return devuelve un valor.
    // view() carga una vista.
    // compact() crea un arreglo usando nombres de variables.
    return view(
        'inventario.bajas_insumos.index',
        compact('bajas', 'areas', 'buscar', 'fechaInit', 'fechaFin')
    );
}
    /**
     * Busca insumos por clave o descripción para el autocompletado (AJAX).
     */
   // public permite acceder al método desde fuera de la clase.
// function define un método.
// Request $request indica que se recibe un objeto Request.
public function buscarInsumos(Request $request)
{
    // $ indica una variable.
    // -> accede a métodos de un objeto.
    // get() obtiene un parámetro de la petición.
    $termino = $request->get('q', '');

    // Obtiene otro parámetro de la petición.
    $idArea = $request->get('id_area_almacen');

    // boolean() convierte el valor recibido a booleano.
    // false es el valor por defecto.
    $all = $request->boolean('all', false);

    // if crea una condición.
    // && significa AND lógico.
    // ! niega una expresión booleana.
    if ($all && !$idArea) {

        // return devuelve un resultado.
        // response() genera una respuesta HTTP.
        // json() convierte datos a JSON.
        // [] representa un arreglo vacío.
        return response()->json([]);
    }

    // strlen() devuelve la longitud de una cadena.
    // < significa menor que.
    if (!$all && strlen($termino) < 2) {

        return response()->json([]);
    }

    // :: acceso estático a una clase.
    $query = Insumo::where('activo', 1);

    if (strlen($termino) >= 1) {

        // function define una función anónima.
        // use() importa variables externas.
        $query->where(function ($q) use ($termino) {

            $q->where('descripcion', 'LIKE', "%{$termino}%")

              // orWhere agrega una condición OR.
              ->orWhere('clave', 'LIKE', "%{$termino}%");
        });
    }

    if ($idArea) {

        $query->whereHas('insumosArea', function ($q) use ($idArea) {

            $q->where('id_area_almacen', $idArea)

              // whereRaw permite escribir SQL manual.
              ->whereRaw('CAST(stock AS UNSIGNED) >= 1');
        });
    }

    $insumos = $query->select(
            'id_insumo',
            'clave',
            'descripcion',
            'tipo'
        )

        // orderBy ordena resultados.
        ->orderBy('clave')

        // when ejecuta una condición de forma fluida.
        // fn() => define una función flecha.
        ->when(!$all, fn($q) => $q->limit(20))

        // get ejecuta la consulta.
        ->get();

    // map recorre una colección y transforma sus elementos.
    $resultado = $insumos->map(function ($insumo) use ($idArea) {

        // [] crea un arreglo.
        // => asigna clave => valor.
        $data = [
            'id_insumo'   => $insumo->id_insumo,
            'clave'       => $insumo->clave,
            'descripcion' => $insumo->descripcion,
            'tipo'        => $insumo->tipo,
        ];

        if ($idArea) {

            $insumoArea = InsumoArea::where('id_insumo', $insumo->id_insumo)
                ->where('id_area_almacen', $idArea)
                ->first();

            // ? : es el operador ternario.
            // (int) realiza conversión de tipo a entero.
            $data['stock'] = $insumoArea ? (int) $insumoArea->stock : 0;
        }

        return $data;
    });

    return response()->json($resultado);
}

// public permite acceder al método desde fuera de la clase.
public function consultarStock(Request $request)
{
    // Obtiene parámetros de la petición.
    $idInsumo = $request->get('id_insumo');
    $idArea   = $request->get('id_area_almacen');

    // || significa OR lógico.
    if (!$idInsumo || !$idArea) {

        return response()->json([
            'stock' => 0,
            'error' => 'Parámetros incompletos'
        ]);
    }

    $insumoArea = InsumoArea::where('id_insumo', $idInsumo)
        ->where('id_area_almacen', $idArea)
        ->first();

    // Operador ternario.
    $stock = $insumoArea ? (int) $insumoArea->stock : 0;

    return response()->json([
        'stock' => $stock
    ]);
}

// Método que recibe un objeto Request.
public function guardar(Request $request)
{
    // validate() valida los datos recibidos.
    $request->validate([

        // | separa reglas de validación.
        'id_insumo'       => 'required|integer|exists:insumos,id_insumo',
        'id_area_almacen' => 'required|integer|exists:areas_almacen,id_area_almacen',
        'motivo'          => 'required|string|max:500',
        'cantidad'        => 'required|integer|min:1',

    ], [

        // => asigna clave => valor.
        'id_insumo.required'       => 'Debe seleccionar un insumo.',
        'id_insumo.exists'         => 'El insumo seleccionado no existe.',
        'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
        'id_area_almacen.exists'   => 'El área seleccionada no existe.',
        'motivo.required'          => 'El motivo de la baja es obligatorio.',
        'motivo.max'               => 'El motivo no puede superar los 500 caracteres.',
        'cantidad.required'        => 'La cantidad es obligatoria.',
        'cantidad.min'             => 'La cantidad debe ser al menos 1.',
    ]);

    $insumoArea = InsumoArea::where('id_insumo', $request->id_insumo)
        ->where('id_area_almacen', $request->id_area_almacen)
        ->first();

    // ! niega el resultado.
    if (!$insumoArea) {

        return redirect()->back()

            // Encadenamiento de métodos.
            ->withInput()

            ->withErrors([
                'cantidad' => 'El insumo no tiene existencia en el área seleccionada.'
            ]);
    }

    // (int) convierte un valor a entero.
    $stockActual = (int) $insumoArea->stock;

    // > significa mayor que.
    if ($request->cantidad > $stockActual) {

        return redirect()->back()
            ->withInput()
            ->withErrors([
                'cantidad' => "La cantidad excede el stock disponible ({$stockActual} piezas)."
            ]);
    }

    // :: acceso estático a una clase.
    // transaction() ejecuta operaciones dentro de una transacción.
    // function define una función anónima.
    // use() importa variables externas a la función.
    DB::transaction(function () use ($request, $insumoArea) {

        // :: acceso estático al modelo.
        // create() crea un registro.
        // [] define un arreglo.
        // => asigna clave => valor.
        BajaInsumo::create([
            'id_insumo'       => $request->id_insumo,
            'id_area_almacen' => $request->id_area_almacen,
            'motivo'          => trim($request->motivo),
            'cantidad'        => $request->cantidad,
            'fecha_baja'      => now()->toDateString(),
            'hora_baja'       => now()->toTimeString(),
            'id_usuario'      => Auth::id() ?? 1,
            'cancelado'       => 'No',
        ]);

        // (int) convierte valores a enteros.
        // - realiza una resta.
        $nuevoStock = (int) $insumoArea->stock - (int) $request->cantidad;

        // update() actualiza registros.
        // (string) convierte un valor a cadena.
        $insumoArea->update([
            'stock' => (string) $nuevoStock
        ]);
    });

    // return devuelve un resultado.
    // redirect() genera una redirección.
    // -> encadena métodos.
    return redirect()
        ->route('bajas_insumos.index')
        ->with('exitog', 'La baja de insumo se ha registrado correctamente.');
}

    // public permite acceso al método desde fuera de la clase.
    public function toggleStatus($id)
    {
        // findOrFail() busca un registro o lanza una excepción.
        $baja = BajaInsumo::findOrFail($id);

        // === compara valor y tipo.
        if ($baja->cancelado === 'No') {

            DB::transaction(function () use ($baja) {

                $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                    ->where('id_area_almacen', $baja->id_area_almacen)
                    ->first();

                if ($insumoArea) {

                    // + realiza una suma.
                    // (int) convierte a entero.
                    $stockRestaurado = (int) $insumoArea->stock + (int) $baja->cantidad;

                    $insumoArea->update([
                        'stock' => (string) $stockRestaurado
                    ]);
                }

                $baja->update([
                    'cancelado' => 'Si'
                ]);
            });

            return redirect()
                ->route('bajas_insumos.index')
                ->with(
                    'exito',
                    'La baja de insumo ha sido cancelada y el stock restaurado.'
                );

        } else {

            $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                ->where('id_area_almacen', $baja->id_area_almacen)
                ->first();

            // ! niega una expresión booleana.
            if (!$insumoArea) {

                return redirect()
                    ->route('bajas_insumos.index')
                    ->with(
                        'error',
                        'El insumo no tiene registro de stock en la misma área.'
                    );
            }

            $stockActual = (int) $insumoArea->stock;

            // > compara si un valor es mayor que otro.
            if ($baja->cantidad > $stockActual) {

                return redirect()
                    ->route('bajas_insumos.index')
                    ->with(
                        'error',
                        "No se puede reactivar la baja. El stock disponible ({$stockActual} piezas) es insuficiente para dar de baja {$baja->cantidad} piezas."
                    );
            }

            DB::transaction(function () use ($baja, $insumoArea) {

                // - realiza una resta.
                $nuevoStock = (int) $insumoArea->stock - (int) $baja->cantidad;

                $insumoArea->update([
                    'stock' => (string) $nuevoStock
                ]);

                $baja->update([
                    'cancelado' => 'No'
                ]);
            });

            return redirect()
                ->route('bajas_insumos.index')
                ->with(
                    'exito',
                    'La baja de insumo ha sido reactivada y el stock descontado.'
                );
        }
    }

    // public permite acceder al método desde fuera de la clase.
// function define un método.
// Request $request indica que se recibe un objeto Request.
public function imprimir(Request $request)
{
    // $ indica una variable.
    // -> accede a métodos de un objeto.
    // get() obtiene parámetros de la petición.
    // '' es una cadena vacía por defecto.
    $buscar    = $request->get('buscar', '');
    $fechaInit = $request->get('fecha_inicio', '');
    $fechaFin  = $request->get('fecha_fin', '');

    // null representa ausencia de valor.
    $fechaInitDb = null;

    // if crea una estructura condicional.
    // ! niega una expresión.
    // empty() verifica si una variable está vacía.
    if (!empty($fechaInit)) {

        // try intenta ejecutar código que puede generar excepciones.
        try {

            // strpos() busca una cadena dentro de otra.
            // !== compara valor y tipo.
            if (strpos($fechaInit, '/') !== false) {

                // :: acceso estático.
                // \ indica espacio de nombres global.
                // -> encadena métodos.
                $fechaInitDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaInit)
                    ->format('Y-m-d');

            } else {

                $fechaInitDb = \Carbon\Carbon::parse($fechaInit)
                    ->format('Y-m-d');
            }

        // catch captura excepciones.
        } catch (\Exception $e) {

            try {

                $fechaInitDb = \Carbon\Carbon::parse($fechaInit)
                    ->format('Y-m-d');

            } catch (\Exception $ex) {

                // Reasignación de una cadena vacía.
                $fechaInit = '';
            }
        }
    }

    // Inicialización con null.
    $fechaFinDb = null;

    if (!empty($fechaFin)) {

        try {

            if (strpos($fechaFin, '/') !== false) {

                $fechaFinDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFin)
                    ->format('Y-m-d');

            } else {

                $fechaFinDb = \Carbon\Carbon::parse($fechaFin)
                    ->format('Y-m-d');
            }

        } catch (\Exception $e) {

            try {

                $fechaFinDb = \Carbon\Carbon::parse($fechaFin)
                    ->format('Y-m-d');

            } catch (\Exception $ex) {

                $fechaFin = '';
            }
        }
    }

    // && representa AND lógico.
    // > compara si un valor es mayor que otro.
    if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {

        // Variable temporal para intercambio de valores.
        $temp = $fechaInitDb;

        $fechaInitDb = $fechaFinDb;

        $fechaFinDb = $temp;
    }

    if ($fechaInitDb) {

        $fechaInit = $fechaInitDb;
    }

    if ($fechaFinDb) {

        $fechaFin = $fechaFinDb;
    }

    // :: acceso estático al modelo.
    // with() carga relaciones.
    // [] define un arreglo.
    $query = BajaInsumo::with(['insumo', 'areaAlmacen'])

        // orderBy() ordena resultados.
        ->orderBy('fecha_baja', 'desc')

        // Encadenamiento de métodos.
        ->orderBy('hora_baja', 'desc');

    if (!empty($buscar)) {

        // function define una función anónima.
        // use() permite usar variables externas.
        $query->where(function ($q) use ($buscar) {

            $q->where('motivo', 'LIKE', "%{$buscar}%")

              // orWhereHas() agrega una condición OR sobre una relación.
              ->orWhereHas('insumo', function ($q2) use ($buscar) {

                  $q2->where('descripcion', 'LIKE', "%{$buscar}%")

                     // orWhere agrega una condición OR.
                     ->orWhere('clave', 'LIKE', "%{$buscar}%");
              });
        });
    }

    if ($fechaInitDb) {

        // whereDate() compara únicamente fechas.
        // >= significa mayor o igual.
        $query->whereDate('fecha_baja', '>=', $fechaInitDb);
    }

    if ($fechaFinDb) {

        // <= significa menor o igual.
        $query->whereDate('fecha_baja', '<=', $fechaFinDb);
    }

    // limit() limita la cantidad de registros.
    // get() ejecuta la consulta.
    $bajas = $query->limit(500)->get();

    // return devuelve un resultado.
    // view() carga una vista.
    // compact() crea un arreglo con variables.
    return view(
        'inventario.bajas_insumos.reporte_impresion',
        compact('bajas', 'buscar', 'fechaInit', 'fechaFin')
    );
}
}