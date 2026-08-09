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
                if (!Schema::hasColumn('bajasinsumos', 'doctor_nombre')) {
                    $table->string('doctor_nombre', 200)->nullable()->after('motivo');
                }
                if (!Schema::hasColumn('bajasinsumos', 'doctor_especialidad')) {
                    $table->string('doctor_especialidad', 200)->nullable()->after('doctor_nombre');
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
                if (Schema::hasColumn('bajasinsumos', 'doctor_especialidad')) {
                    $columnsToDrop[] = 'doctor_especialidad';
                }
                if (Schema::hasColumn('bajasinsumos', 'doctor_nombre')) {
                    $columnsToDrop[] = 'doctor_nombre';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
