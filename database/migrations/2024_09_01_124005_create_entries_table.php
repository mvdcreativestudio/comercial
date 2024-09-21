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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->text('concept');
            $table->boolean('is_balanced')->default(false);
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('entry_type_id')->constrained('entry_types');
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar registros de ejemplo en español
        DB::table('entries')->insert([
            ['entry_date' => '2024-09-01', 'entry_type_id' => 1, 'concept' => 'Venta de producto A vía eCommerce', 'currency_id' => 1, 'is_balanced' => true],
            ['entry_date' => '2024-09-02', 'entry_type_id' => 2, 'concept' => 'Pago a Proveedor X', 'currency_id' => 1, 'is_balanced' => true],
            ['entry_date' => '2024-09-03', 'entry_type_id' => 3, 'concept' => 'Ingreso por venta en tienda física', 'currency_id' => 1, 'is_balanced' => true],
            ['entry_date' => '2024-09-04', 'entry_type_id' => 4, 'concept' => 'Pago de comisiones bancarias', 'currency_id' => 2, 'is_balanced' => true],
            ['entry_date' => '2024-09-05', 'entry_type_id' => 5, 'concept' => 'Ajuste contable por error de registro', 'currency_id' => 3, 'is_balanced' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
