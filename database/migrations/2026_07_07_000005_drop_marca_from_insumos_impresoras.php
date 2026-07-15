<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('insumos_impresoras', 'marca')) {
            Schema::table('insumos_impresoras', function (Blueprint $table) {
                $table->dropColumn('marca');
            });
        }
    }

    public function down(): void
    {
        Schema::table('insumos_impresoras', function (Blueprint $table) {
            $table->string('marca', 100)->after('id_insumo_impresora');
        });
    }
};
