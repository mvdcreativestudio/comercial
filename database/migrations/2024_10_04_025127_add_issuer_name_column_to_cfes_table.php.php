<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::table('cfes', function (Blueprint $table) {
        $table->string('issuer_name')->nullable()->after('received');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('cfes', function (Blueprint $table) {
        $table->dropColumn('issuer_name');
      });
    }
};
