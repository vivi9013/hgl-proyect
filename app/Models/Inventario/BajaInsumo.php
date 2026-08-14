<?php

// Define el espacio de nombres al que pertenece este modelo.
// Permite organizar los modelos de Inventario y evitar conflictos con clases que tengan el mismo nombre.
namespace App\Models\Inventario;

// Importa la clase Model de Laravel.
// Se usa para que BajaInsumo pueda trabajar con la base de datos mediante Eloquent.
use Illuminate\Database\Eloquent\Model;

// Define el modelo que representa la tabla de bajas de insumos.
class BajaInsumo extends Model
{
    // Indica el nombre exacto de la tabla que utiliza este modelo.
    // Se define porque Laravel normalmente intenta obtener el nombre de la tabla a partir del nombre del modelo.
    protected $table = 'bajasinsumos';

    // Indica cuál es la clave primaria de la tabla.
    // Se define porque Laravel por defecto espera una columna llamada "id".
    protected $primaryKey = 'id_baja_insumo';

    // Desactiva los campos automáticos created_at y updated_at.
    // Se usa porque esta tabla maneja sus propias fechas mediante fecha_baja y hora_baja.
    public $timestamps = false;

    // Indica qué campos pueden asignarse directamente al crear o actualizar un registro.
    // Se usa para permitir asignación masiva mediante métodos como create() o update().
    protected $fillable = [
        'id_insumo',
        'id_area_almacen',
        'id_area_abastecimiento',
        'motivo',
        'iniciales_paciente',
        'no_expediente',
        'doctor_nombre',
        'doctor_especialidad',
        'persona_entrega',
        'cantidad',
        'fecha_baja',
        'hora_baja',
        'id_usuario',
        'cancelado',
    ];

    // Define cómo deben convertirse algunos valores cuando Laravel los obtiene de la base de datos.
    // Se usa para trabajar con los datos en el tipo que corresponde dentro de PHP.
    protected $casts = [
        'id_area_abastecimiento' => 'integer',

        // Convierte cantidad a un número entero.
        // Es adecuado porque representa una cantidad de piezas y no un texto.
        'cantidad'   => 'integer',

        // Convierte fecha_baja en una fecha que Laravel puede manejar como fecha.
        // Esto facilita trabajar posteriormente con fechas sin tratarlas solamente como texto.
        'fecha_baja' => 'date',
    ];

    // Define la relación entre una baja y el insumo al que pertenece.
    // belongsTo() se usa porque cada baja pertenece a un solo insumo.
    public function insumo()
    {
        // Indica que id_insumo de esta tabla se relaciona con id_insumo de la tabla de insumos.
        // Se indican ambas columnas porque la clave utilizada no sigue necesariamente la convención "id".
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }

    // Define la relación entre una baja y el área de almacén relacionada.
    // belongsTo() se usa porque cada baja pertenece a un área de almacén.
    public function areaAlmacen()
    {
        // Relaciona id_area_almacen de esta tabla con id_area_almacen de la tabla de áreas.
        // Se indican las claves manualmente porque la clave primaria utiliza un nombre personalizado.
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    // Define la relación entre una baja y el área de abastecimiento relacionada.
    // belongsTo() se usa porque cada baja almacena de forma inmutable el área asignada en el momento de la baja.
    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }
}