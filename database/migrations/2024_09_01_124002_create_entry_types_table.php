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
        Schema::create('entry_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar registros de ejemplo en español
        DB::table('entry_types')->insert([
            ['name' => 'Venta eCommerce', 'description' => 'Registro de ingresos por ventas en línea'],
            ['name' => 'Pago Proveedores', 'description' => 'Registro de pagos a proveedores'],
            ['name' => 'Ingreso POS', 'description' => 'Registro de ingresos en tienda física'],
            ['name' => 'Gasto Operativo', 'description' => 'Registro de gastos operativos'],
            ['name' => 'Ajuste Contable', 'description' => 'Ajustes en las cuentas contables'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_types');
    }
};
