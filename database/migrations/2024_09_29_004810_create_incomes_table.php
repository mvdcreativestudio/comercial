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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_name');
            $table->text('income_description')->nullable();
            $table->date('income_date');
            $table->decimal('income_amount', 15, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->foreignId('income_category_id')->constrained('income_categories')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('incomes')->insert([
            [
                'income_name' => 'Salario',
                'income_description' => 'Ingreso por salario',
                'income_date' => '2024-09-29',
                'income_amount' => 5000,
                'payment_method_id' => 1,
                'income_category_id' => 1,
                'client_id' => 302,
            ],
            [
                'income_name' => 'Freelance',
                'income_description' => 'Ingreso por trabajo freelance',
                'income_date' => '2024-09-29',
                'income_amount' => 2000,
                'payment_method_id' => 2,
                'income_category_id' => 2,
                'client_id' => 302,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
