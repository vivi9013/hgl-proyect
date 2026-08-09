<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración de respaldo/documentación para la tabla 'servicios' del legacy.
 * Solo se ejecuta si la tabla NO existe previamente.
 * En producción con datos heredados esta tabla ya existe, por lo que esta
 * migración solo aplica a entornos de desarrollo limpios.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servicios')) {
            Schema::create('servicios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_usc')->nullable()->comment('ID de usuario (USC)');
                $table->unsignedBigInteger('id_personaSolicitante')->nullable();
                $table->date('fecha_peticion')->nullable();
                $table->time('hora_peticion')->nullable();
                $table->unsignedBigInteger('id_departamento')->nullable();
                $table->string('departamento', 200)->nullable();
                $table->text('descripcion_servicio')->nullable();
                $table->unsignedBigInteger('id_area')->nullable();
                $table->tinyInteger('pendiente')->default(0);
                $table->tinyInteger('proceso')->default(0);
                $table->tinyInteger('terminado')->default(0);
                $table->tinyInteger('liberado')->default(0);
                $table->string('estatus_final', 50)->nullable();
                $table->string('nombre_solicitante', 300)->nullable();
                $table->char('sexo_solicitante', 1)->nullable();
                $table->string('ext_telefonica', 20)->nullable();
                $table->string('sede', 200)->nullable();
                $table->string('abre_sede', 20)->nullable();
                $table->unsignedBigInteger('id_sede')->nullable();
                $table->unsignedBigInteger('id_personaServidor')->nullable();
                $table->string('nombre_servidor', 300)->nullable();
                $table->char('sexo_servidor', 1)->nullable();
                $table->date('fecha_tomado')->nullable();
                $table->time('hora_tomado')->nullable();
                $table->date('fecha_termino')->nullable();
                $table->time('hora_termino')->nullable();
                $table->date('fecha_finaliza')->nullable();
                $table->time('hora_finaliza')->nullable();
                $table->string('liberadox', 50)->nullable();
                $table->string('clasificacion_servicio', 100)->nullable();
                $table->text('accion_realizada')->nullable();
                $table->unsignedBigInteger('id_tipo_servicio')->nullable();
                $table->string('tipo_servicio', 100)->nullable();
                $table->tinyInteger('modificado')->default(0);
                $table->string('modificadox', 300)->nullable();
                $table->string('motivo_modificado', 500)->nullable();
                $table->date('fecha_modificado')->nullable();
                $table->time('hora_modificado')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Solo elimina la tabla si la creamos nosotros (en entornos de prueba sin datos legacy)
        // En producción no hacer nada para no destruir datos.
    }
};
