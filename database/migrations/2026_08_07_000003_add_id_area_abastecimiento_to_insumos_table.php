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
        if (Schema::hasTable('insumos') && !Schema::hasColumn('insumos', 'id_area_abastecimiento')) {
            Schema::table('insumos', function (Blueprint $table) {
                $table->integer('id_area_abastecimiento')->nullable()->after('tipo');
                $table->foreign('id_area_abastecimiento')
                      ->references('id_area_abastecimiento')
                      ->on('areasabastecimiento')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('insumos') && Schema::hasColumn('insumos', 'id_area_abastecimiento')) {
            Schema::table('insumos', function (Blueprint $table) {
                $table->dropForeign(['id_area_abastecimiento']);
                $table->dropColumn('id_area_abastecimiento');
            });
        }
    }
};
