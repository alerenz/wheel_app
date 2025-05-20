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
        Schema::table('user_prizes', function (Blueprint $table) {
            $table->dropForeign(['wheel_id']);
            $table->foreign('wheel_id')->references('id')->on('wheels')->onUpdate('cascade')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_prizes', function (Blueprint $table) {
           $table->dropForeign(['wheel_id']);
            $table->foreign('wheel_id')->references('id')->on('wheels')->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }
};
