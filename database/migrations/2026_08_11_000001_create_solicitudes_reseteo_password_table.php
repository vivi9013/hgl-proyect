<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_reseteo_password', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_usuario');
            $table->integer('id_usuario')->nullable();
            $table->string('nombre_declarado');
            $table->string('dato_adicional')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente | aprobada | rechazada
            $table->string('ip', 45)->nullable();
            $table->date('fecha');
            $table->time('hora');
            $table->integer('revisado_por')->nullable();
            $table->text('nota_revision')->nullable();
            $table->date('fecha_revision')->nullable();
            $table->time('hora_revision')->nullable();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('revisado_por')->references('id')->on('usuarios')->nullOnDelete();

            $table->index(['id_usuario', 'estado']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reseteo_password');
    }
};
