<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('currencies', 'code')) {
                $table->string('code')->unique();
            }
            if (!Schema::hasColumn('currencies', 'symbol')) {
                $table->string('symbol');
            }
            if (!Schema::hasColumn('currencies', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('currencies', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 8);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (Schema::hasColumn('currencies', 'id')) {
                $table->dropColumn('id');
            }
            if (Schema::hasColumn('currencies', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('currencies', 'symbol')) {
                $table->dropColumn('symbol');
            }
            if (Schema::hasColumn('currencies', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('currencies', 'exchange_rate')) {
                $table->dropColumn('exchange_rate');
            }
        });
    }
};