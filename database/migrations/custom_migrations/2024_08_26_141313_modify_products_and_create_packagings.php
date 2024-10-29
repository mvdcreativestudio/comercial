<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Modificar la tabla products
        Schema::table('products', function (Blueprint $table) {
            // Eliminar columnas
            $table->dropColumn(['max_flavors', 'old_price', 'price', 'discount']);
            
            // Eliminar la FK y luego la columna store_id
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
            
            // Agregar nuevas columnas
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->foreignId('bulk_production_id')->constrained('bulk_productions')->onDelete('cascade');

            //Si al ejecutar ejecuta una parte, y luego da falla en la otra, yo borre esta parte de la migración, y le agregué esta:
            /*
                Schema::table('products', function (Blueprint $table) {
                // Agregar la FK a category_id si no está ya definida
                $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
                
                // Agregar la FK a bulk_production_id si no está ya definida
                $table->foreign('bulk_production_id')->references('id')->on('bulk_productions')->onDelete('cascade');
            });

            */
        });

        // Crear la tabla packagings
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_production_id')->constrained('bulk_productions')->onDelete('cascade');
            $table->integer('quantity_packaged');
            $table->dateTime('packaging_date');
            $table->foreignId('final_product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        // Revertir los cambios en la tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->integer('max_flavors')->nullable();
            $table->decimal('old_price', 8, 2)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('discount', 5, 2)->nullable();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropForeign(['bulk_production_id']);
            $table->dropColumn('bulk_production_id');
        });

        // Eliminar la tabla packagings
        Schema::dropIfExists('packagings');
    }
};
