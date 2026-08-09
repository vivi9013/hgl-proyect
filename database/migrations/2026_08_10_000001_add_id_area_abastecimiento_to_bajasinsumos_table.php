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
        if (Schema::hasTable('bajasinsumos') && !Schema::hasColumn('bajasinsumos', 'id_area_abastecimiento')) {
            Schema::table('bajasinsumos', function (Blueprint $table) {
                $table->integer('id_area_abastecimiento')->nullable()->after('id_area_almacen');
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
        if (Schema::hasTable('bajasinsumos') && Schema::hasColumn('bajasinsumos', 'id_area_abastecimiento')) {
            Schema::table('bajasinsumos', function (Blueprint $table) {
                $table->dropForeign(['id_area_abastecimiento']);
                $table->dropColumn('id_area_abastecimiento');
            });
        }
    }
};
