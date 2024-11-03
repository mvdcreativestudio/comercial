<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, hacemos el campo nullable y agregamos la clave foránea
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('store_id')
                  ->nullable() // Hacemos que el campo sea nullable
                  ->constrained('stores')
                  ->onDelete('cascade')
        });

        // Luego, asignamos null a los registros existentes en la columna store_id
        DB::table('suppliers')->update(['store_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
