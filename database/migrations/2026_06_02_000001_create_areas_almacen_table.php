<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla: areas_almacen
     * Módulo: Inventario de Medicamentos y Material de Curación
     */
    public function up(): void
    {
        if (!Schema::hasTable('areas_almacen')) {
            Schema::create('areas_almacen', function (Blueprint $table) {
                $table->id('id_area_almacen');
                $table->string('nombre', 255)->unique();
                $table->tinyInteger('activo')->default(1)->comment('1 = Activo, 0 = Inactivo');
                $table->date('fecha_registro')->nullable();
                $table->time('hora_registro')->nullable();
                $table->unsignedBigInteger('id_usuario')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No borramos la tabla por seguridad si ya existía previamente con datos legacy,
        // pero si fuera necesario se podría usar dropIfExists.
    }
};
