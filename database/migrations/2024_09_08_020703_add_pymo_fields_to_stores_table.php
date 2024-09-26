<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('invoices_enabled')->default(false); // Campo para habilitar o no la facturación
            $table->string('pymo_user')->nullable(); // Usuario para PyMo
            $table->string('pymo_password')->nullable(); // Contraseña para PyMo
            $table->string('pymo_branch_office')->nullable(); // Sucursal para PyMo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('invoices_enabled');
            $table->dropColumn('pymo_user');
            $table->dropColumn('pymo_password');
            $table->dropColumn('pymo_branch_office');
        });
    }
};
