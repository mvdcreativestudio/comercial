<?php

use App\Enums\CurrentAccounts\TransactionTypeEnum;
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
        Schema::create('current_account_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', array_map('ucfirst', array_column(TransactionTypeEnum::cases(), 'value')));
            $table->decimal('late_fee', 10, 2)->nullable();
            $table->integer('payment_terms')->default(30);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('current_account_settings')->insert([
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.05, 'payment_terms' => 5],
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.05, 'payment_terms' => 7],
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.10, 'payment_terms' => 15],
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.05, 'payment_terms' => 30],
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.05, 'payment_terms' => 60],
            ['transaction_type' => TransactionTypeEnum::SALE, 'late_fee' => 0.05, 'payment_terms' => 90],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 5],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 7],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 15],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 30],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 60],
            ['transaction_type' => TransactionTypeEnum::PURCHASE, 'late_fee' => 0.10, 'payment_terms' => 90],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_account_settings');
    }
};
