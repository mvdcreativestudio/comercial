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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3);
            $table->string('symbol', 5);
            $table->string('name');
            $table->decimal('exchange_rate', 10, 4);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('payment_methods')->insert([
            ['description' => 'Efectivo'],
            ['description' => 'Transferencia bancaria'],
            ['description' => 'Tarjeta de crédito'],
            ['description' => 'Tarjeta de débito'],
        ]);

        DB::table('currencies')->insert([
            ['code' => 'USD', 'symbol' => '$', 'name' => 'Dólar estadounidense', 'exchange_rate' => 1],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro', 'exchange_rate' => 0.85],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'Libra esterlina', 'exchange_rate' => 0.73],
            ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Yen japonés', 'exchange_rate' => 110.15],
            ['code' => 'CNY', 'symbol' => '¥', 'name' => 'Yuan chino', 'exchange_rate' => 6.46],
            ['code' => 'CAD', 'symbol' => '$', 'name' => 'Dólar canadiense', 'exchange_rate' => 1.25],
            ['code' => 'AUD', 'symbol' => '$', 'name' => 'Dólar australiano', 'exchange_rate' => 1.29],
            ['code' => 'CHF', 'symbol' => 'Fr', 'name' => 'Franco suizo', 'exchange_rate' => 0.92],
            ['code' => 'SEK', 'symbol' => 'kr', 'name' => 'Corona sueca', 'exchange_rate' => 8.67],
            ['code' => 'NZD', 'symbol' => '$', 'name' => 'Dólar neozelandés', 'exchange_rate' => 1.36],
            ['code' => 'KRW', 'symbol' => '₩', 'name' => 'Won surcoreano', 'exchange_rate' => 1130.10],
            ['code' => 'SGD', 'symbol' => '$', 'name' => 'Dólar singapurense', 'exchange_rate' => 1.35],
            ['code' => 'NOK', 'symbol' => 'kr', 'name' => 'Corona noruega', 'exchange_rate' => 8.67],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
