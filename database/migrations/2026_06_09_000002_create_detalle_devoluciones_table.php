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
        if (!Schema::hasTable('detalle_devoluciones')) {
            Schema::create('detalle_devoluciones', function (Blueprint $table) {
                $table->increments('id_detalle_devolucion');
                $table->unsignedInteger('id_devolucion');
                $table->unsignedInteger('id_insumo')->nullable();
                $table->string('cantidad', 20)->default('0');
                $table->text('motivo')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se elimina para proteger datos legacy
    }
};
