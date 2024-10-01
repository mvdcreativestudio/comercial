<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromCurrencyRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
    */
    public function up()
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            // Eliminar las columnas date, buy, y sell
            $table->dropColumn(['date', 'buy', 'sell']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
    */
    public function down()
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            // Restaurar las columnas date, buy, y sell
            $table->date('date')->nullable();
            $table->decimal('buy', 10, 5)->nullable();
            $table->decimal('sell', 10, 5)->nullable();
        });
    }
}
