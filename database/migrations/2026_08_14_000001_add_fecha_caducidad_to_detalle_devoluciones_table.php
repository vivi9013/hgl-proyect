<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Usa hasColumn() para no fallar si la columna ya existe en la base de datos.
     */
    public function up(): void
    {
        if (Schema::hasTable('detalle_devoluciones') && !Schema::hasColumn('detalle_devoluciones', 'fecha_caducidad')) {
            Schema::table('detalle_devoluciones', function (Blueprint $table) {
                $table->date('fecha_caducidad')->nullable()->after('cantidad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detalle_devoluciones') && Schema::hasColumn('detalle_devoluciones', 'fecha_caducidad')) {
            Schema::table('detalle_devoluciones', function (Blueprint $table) {
                $table->dropColumn('fecha_caducidad');
            });
        }
    }
};
