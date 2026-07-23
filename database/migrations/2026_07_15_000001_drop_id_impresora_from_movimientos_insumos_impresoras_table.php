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
        if (Schema::hasColumn('movimientos_insumos_impresoras', 'id_impresora')) {
            Schema::table('movimientos_insumos_impresoras', function (Blueprint $table) {
                $table->dropColumn('id_impresora');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_insumos_impresoras', function (Blueprint $table) {
            $table->unsignedInteger('id_impresora')->nullable()->after('cantidad');
        });
    }
};
