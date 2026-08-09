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
        if (Schema::hasTable('insumos') && !Schema::hasColumn('insumos', 'id_area_surtimiento')) {
            Schema::table('insumos', function (Blueprint $table) {
                $table->integer('id_area_surtimiento')->nullable()->after('tipo');
                $table->foreign('id_area_surtimiento')
                      ->references('id_area_surtimiento')
                      ->on('areas_surtimiento')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('insumos') && Schema::hasColumn('insumos', 'id_area_surtimiento')) {
            Schema::table('insumos', function (Blueprint $table) {
                $table->dropForeign(['id_area_surtimiento']);
                $table->dropColumn('id_area_surtimiento');
            });
        }
    }
};
