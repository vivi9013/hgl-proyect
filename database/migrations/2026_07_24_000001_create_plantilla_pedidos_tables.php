<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla: plantilla_pedidos + detalle_plantilla_pedidos
     * Módulo: Petición de Insumos — Plantillas de Pedido
     */
    public function up(): void
    {
        if (!Schema::hasTable('plantilla_pedidos')) {
            Schema::create('plantilla_pedidos', function (Blueprint $table) {
                $table->bigIncrements('id_plantilla_pedido');
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->integer('id_area_abastecimiento');        // FK lógica a areasabastecimiento (tabla legacy, sin FK formal)
                $table->integer('id_subarea_abastecimiento')->nullable(); // FK lógica a subareas_abastecimiento
                $table->unsignedBigInteger('id_area_almacen')->nullable(); // FK lógica a areas_almacen
                $table->date('fecha_registro')->nullable();
                $table->time('hora_registro')->nullable();
                $table->tinyInteger('activo')->default(1)->comment('1 = Activo, 0 = Inactivo');
                $table->unsignedBigInteger('id_usuario')->nullable();
            });
        }

        if (!Schema::hasTable('detalle_plantilla_pedidos')) {
            Schema::create('detalle_plantilla_pedidos', function (Blueprint $table) {
                $table->bigIncrements('id_detalle_plantilla');
                $table->unsignedBigInteger('id_plantilla_pedido');
                $table->integer('id_insumo');
                $table->string('cve_insumo', 50)->nullable();
                $table->integer('cantidad')->default(1);

                $table->foreign('id_plantilla_pedido')
                      ->references('id_plantilla_pedido')
                      ->on('plantilla_pedidos')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_plantilla_pedidos');
        Schema::dropIfExists('plantilla_pedidos');
    }
};

