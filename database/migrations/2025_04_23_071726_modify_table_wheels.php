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
        Schema::table('wheels', function (Blueprint $table) {
            $table->dropColumn('day_of_week');

            $table->json('days_of_week');
            $table->integer('max_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheels', function (Blueprint $table) {
            $table->string('day_of_week');

            // Удаление новых столбцов
            $table->dropColumn(['days_of_week', 'max_attempts']);
        });
    }
};
