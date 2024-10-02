<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsAndCreateRawMaterialPrices extends Migration
{
    public function up()
    {
        // Eliminar la FK y la columna bulk_production_id de la tabla products
        Schema::table('products', function (Blueprint $table) {
            // Si existe, eliminamos la FK
            $table->dropForeign(['bulk_production_id']);
            // Eliminamos la columna
            $table->dropColumn('bulk_production_id');
        });

        // Crear la tabla raw_material_prices
        Schema::create('raw_material_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_id');
            $table->string('currency');
            $table->decimal('price', 8, 2);
            $table->timestamps();

            // FK hacia la tabla raw_materials
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Restaurar la columna bulk_production_id y su FK
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('bulk_production_id')->nullable();
            $table->foreign('bulk_production_id')->references('id')->on('bulk_productions')->onDelete('set null');
        });

        // Eliminar la tabla raw_material_prices
        Schema::dropIfExists('raw_material_prices');
    }
}
