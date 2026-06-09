<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Usa hasTable() para no romper la base de datos legacy si la tabla ya existe.
     */
    public function up(): void
    {
        if (!Schema::hasTable('devoluciones')) {
            Schema::create('devoluciones', function (Blueprint $table) {
                $table->increments('id_devolucion');
                $table->string('folio', 30)->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->unsignedInteger('id_area_abastecimiento')->nullable();
                $table->unsignedInteger('id_subarea_abastecimiento')->nullable();
                $table->unsignedInteger('id_area_almacen')->nullable();
                $table->date('fecha')->nullable();
                $table->time('hora')->nullable();
                $table->string('status', 30)->default('En proceso');
                $table->text('observaciones')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     * Solo elimina si fue creada por esta migración (no legacy).
     */
    public function down(): void
    {
        // No se elimina para proteger datos legacy
    }
};
