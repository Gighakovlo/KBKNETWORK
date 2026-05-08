<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('category_field_id')->constrained('category_fields')->onDelete('cascade');
            $table->text('value')->nullable(); // Menggunakan text agar bisa menampung deskripsi panjang
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_values');
    }
};