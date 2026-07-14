<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('insumos_impresoras', 'descripcion')) {
            Schema::table('insumos_impresoras', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }

        if (Schema::hasColumn('movimientos_insumos_impresoras', 'comentarios')) {
            Schema::table('movimientos_insumos_impresoras', function (Blueprint $table) {
                $table->dropColumn('comentarios');
            });
        }
    }

    public function down(): void
    {
        Schema::table('insumos_impresoras', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('modelos_compatibles');
        });

        Schema::table('movimientos_insumos_impresoras', function (Blueprint $table) {
            $table->text('comentarios')->nullable()->after('proveedor');
        });
    }
};
