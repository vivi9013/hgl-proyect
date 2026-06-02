<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla: areas_surtimiento
     * Módulo: Inventario de Medicamentos y Material de Curación
     */
    public function up(): void
    {
        if (!Schema::hasTable('areas_surtimiento')) {
            Schema::create('areas_surtimiento', function (Blueprint $table) {
                $table->id('id_area_surtimiento');
                $table->string('nombre', 255);
                $table->string('tipo', 100);
                $table->date('fecha_registro')->nullable();
                $table->time('hora_registro')->nullable();
                $table->tinyInteger('activo')->default(1)->comment('1 = Activo, 0 = Inactivo');
                $table->unsignedBigInteger('id_usuario')->nullable();
                
                // Índice único para evitar duplicados del mismo nombre por tipo
                $table->unique(['nombre', 'tipo']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No borramos la tabla por seguridad si ya existía previamente con datos legacy.
    }
};
