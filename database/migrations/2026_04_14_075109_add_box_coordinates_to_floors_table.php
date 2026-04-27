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
        Schema::table('floors', function (Blueprint $table) {
            $table->decimal('box_width', 10, 2)->nullable();
            $table->decimal('box_height', 10, 2)->nullable();
            $table->decimal('box_left', 10, 2)->nullable();
            $table->decimal('box_top', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            //
        });
    }
};
