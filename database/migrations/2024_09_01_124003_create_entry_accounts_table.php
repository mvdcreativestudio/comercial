<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntryAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('entry_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insertar registros de ejemplo en español
        DB::table('entry_accounts')->insert([
            ['code' => '1020', 'name' => 'Banco eCommerce', 'description' => 'Dinero depositado en la cuenta bancaria por ventas en línea'],
            ['code' => '7001', 'name' => 'Ventas eCommerce', 'description' => 'Ingresos generados a través de ventas en línea'],
            ['code' => '7002', 'name' => 'Ventas POS', 'description' => 'Ingresos generados a través de ventas en tienda física'],
            ['code' => '1010', 'name' => 'Caja POS', 'description' => 'Dinero en efectivo recibido en el punto de venta'],
            ['code' => '6100', 'name' => 'Comisiones de pago', 'description' => 'Gastos por comisiones de tarjetas de crédito y débito'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('entry_accounts');
    }
}
