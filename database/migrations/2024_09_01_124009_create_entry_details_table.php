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
        Schema::create('entry_details', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount_debit', 15, 2)->default(0);
            $table->decimal('amount_credit', 15, 2)->default(0);
            $table->foreignId('entry_id')->constrained('entries');
            $table->foreignId('entry_entry_account_id')->constrained('entry_accounts');
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar registros de ejemplo en español
        DB::table('entry_details')->insert([
            ['entry_id' => 1, 'entry_entry_account_id' => 4, 'amount_debit' => 0, 'amount_credit' => 1000.00],
            ['entry_id' => 1, 'entry_entry_account_id' => 2, 'amount_debit' => 1000.00, 'amount_credit' => 0],
            ['entry_id' => 2, 'entry_entry_account_id' => 3, 'amount_debit' => 500.00, 'amount_credit' => 0],
            ['entry_id' => 2, 'entry_entry_account_id' => 1, 'amount_debit' => 0, 'amount_credit' => 500.00],
            ['entry_id' => 3, 'entry_entry_account_id' => 3, 'amount_debit' => 0, 'amount_credit' => 700.00],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_details');
    }
};
