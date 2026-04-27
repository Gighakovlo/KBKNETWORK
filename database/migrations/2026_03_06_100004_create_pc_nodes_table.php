<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pc_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete(); // Relasi ke Lantai
            $table->string('name'); // Nama PC
            $table->string('ip_address')->nullable();
            $table->string('brand_model')->nullable();
            $table->string('current_user')->nullable(); // Pemilik / Pengguna PC saat ini
            $table->float('pos_x')->default(100);
            $table->float('pos_y')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_nodes');
    }
};