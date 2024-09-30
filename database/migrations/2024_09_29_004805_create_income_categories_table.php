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
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->string('income_name');
            $table->text('income_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('income_categories')->insert([
            ['income_name' => 'Salario', 'income_description' => 'Ingreso por salario'],
            ['income_name' => 'Freelance', 'income_description' => 'Ingreso por trabajo freelance'],
            ['income_name' => 'Inversión', 'income_description' => 'Ingreso por inversión'],
            ['income_name' => 'Otro', 'income_description' => 'Otro tipo de ingreso'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_categories');
    }
};
