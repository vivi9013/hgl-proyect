<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('detalle_plantilla_pedidos', 'fondo_fijo')) {
            Schema::table('detalle_plantilla_pedidos', function (Blueprint $table) {
                $table->integer('fondo_fijo')->nullable()->default(0)->after('cantidad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('detalle_plantilla_pedidos', 'fondo_fijo')) {
            Schema::table('detalle_plantilla_pedidos', function (Blueprint $table) {
                $table->dropColumn('fondo_fijo');
            });
        }
    }
};
