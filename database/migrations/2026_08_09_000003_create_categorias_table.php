<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla categorias si no existe (compatible con BD legacy).
     * Usada por el módulo Bajas de Insumos e Insumos por Área para clasificar insumos.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->increments('id_categoria');
                $table->string('nombre_categoria', 150);
                $table->text('descripcion')->nullable();
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
