<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete(); // Relasi ke Gedung
            $table->string('name'); // Nama Lantai (Cth: Lantai 1)
            $table->string('image_path'); // Lokasi file gambar denah
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('floors');
    }
};