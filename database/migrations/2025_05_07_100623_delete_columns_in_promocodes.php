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
        Schema::table('promocodes', function (Blueprint $table) {
            $table->dropColumn('type_discount');
            $table->dropColumn('discount_value');
            $table->dropColumn('expiry_date');
            $table->dropColumn('code');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->string('code')->unique();
            $table->enum('type_discount', ['Процентная', 'Фиксированная']);
            $table->float('discount_value');
            $table->date('expiry_date');
            $table->dropColumn('name');
        });
    }
};
