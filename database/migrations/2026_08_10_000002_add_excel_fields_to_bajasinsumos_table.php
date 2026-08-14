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
        if (Schema::hasTable('bajasinsumos')) {
            Schema::table('bajasinsumos', function (Blueprint $table) {
                if (!Schema::hasColumn('bajasinsumos', 'iniciales_paciente')) {
                    $table->string('iniciales_paciente', 100)->nullable()->after('motivo');
                }
                if (!Schema::hasColumn('bajasinsumos', 'no_expediente')) {
                    $table->string('no_expediente', 100)->nullable()->after('iniciales_paciente');
                }
                if (!Schema::hasColumn('bajasinsumos', 'persona_entrega')) {
                    $table->string('persona_entrega', 200)->nullable()->after('doctor_especialidad');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bajasinsumos')) {
            Schema::table('bajasinsumos', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('bajasinsumos', 'iniciales_paciente')) {
                    $columnsToDrop[] = 'iniciales_paciente';
                }
                if (Schema::hasColumn('bajasinsumos', 'no_expediente')) {
                    $columnsToDrop[] = 'no_expediente';
                }
                if (Schema::hasColumn('bajasinsumos', 'persona_entrega')) {
                    $columnsToDrop[] = 'persona_entrega';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
