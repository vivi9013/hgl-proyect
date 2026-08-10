<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla movimientos_insumos_impresoras si no existe (compatible con BD legacy).
     */
    public function up(): void
    {
        if (!Schema::hasTable('movimientos_insumos_impresoras')) {
            Schema::create('movimientos_insumos_impresoras', function (Blueprint $table) {
                $table->increments('id_movimiento');
                $table->unsignedInteger('id_insumo_impresora');
                $table->string('tipo', 50);             // Entrada / Salida
                $table->string('concepto', 100);        // Compra, Donación, Uso, Por daño
                $table->integer('cantidad');
                $table->string('proveedor', 150)->nullable();
                $table->date('fecha_movimiento');
                $table->tinyInteger('activo')->default(1);  // 1: Activo, 0: Cancelado
                $table->date('fecha')->nullable();
                $table->time('hora')->nullable();
                $table->unsignedBigInteger('id_usuario')->nullable();

                $table->foreign('id_insumo_impresora')
                      ->references('id_insumo_impresora')
                      ->on('insumos_impresoras')
                      ->onDelete('restrict');
            });
        }
    }

    /**
     * No se elimina la tabla en rollback para proteger datos legacy.
     */
    public function down(): void
    {
        // Sin acción — protección de datos legacy
    }
};
