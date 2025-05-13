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
            $table->unsignedBigInteger('promocodeCode_id')->nullable();
            $table->foreign('promocodeCode_id')
              ->references('id')
              ->on('promocodes_codes')
              ->onUpdate('cascade') 
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_prizes', function (Blueprint $table) {
            $table->dropColumn('promocodeCode_id');
        });
    }
};
