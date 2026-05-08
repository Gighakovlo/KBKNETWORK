<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: 'Komputer', 'Monitor', 'Keyboard'
            $table->string('prefix', 10)->unique(); // Contoh: 'PC', 'MON', 'KEY' (Untuk Auto-ID)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_categories');
    }
};