<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla insumos_impresoras si no existe (compatible con BD legacy).
     */
    public function up(): void
    {
        if (!Schema::hasTable('insumos_impresoras')) {
            Schema::create('insumos_impresoras', function (Blueprint $table) {
                $table->increments('id_insumo_impresora');
                $table->string('familia', 50);
                $table->string('modelo', 100);
                $table->string('color', 50);
                $table->text('modelos_compatibles')->nullable();
                $table->string('tiempo_uso', 100)->nullable();
                $table->integer('hojas_uso_total')->nullable();
                $table->integer('stock')->default(0);
                $table->tinyInteger('activo')->default(1);
                $table->date('fecha')->nullable();
                $table->time('hora')->nullable();
                $table->unsignedBigInteger('id_usuario')->nullable();
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
