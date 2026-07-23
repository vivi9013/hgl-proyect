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
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->text('serie')->nullable()->change();
            $table->text('otros')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobiliario', function (Blueprint $table) {
            $table->text('serie')->nullable(false)->change();
            $table->text('otros')->nullable(false)->change();
        });
    }
};
