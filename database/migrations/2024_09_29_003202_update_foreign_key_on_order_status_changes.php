<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeyOnOrderStatusChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_status_changes', function (Blueprint $table) {
            // Primero eliminamos la clave foránea existente
            $table->dropForeign(['order_id']);
            
            // Luego la volvemos a crear con la opción de eliminación en cascada
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_status_changes', function (Blueprint $table) {
            // Revertimos los cambios eliminando la clave foránea con cascada
            $table->dropForeign(['order_id']);
            
            // Y la volvemos a crear sin la opción de eliminación en cascada
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('restrict');
        });
    }
}