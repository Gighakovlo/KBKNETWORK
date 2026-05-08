<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories');
            $table->string('asset_code')->unique(); // Contoh: 'PC-0001', 'MON-0150' (Otomatis dibuat oleh sistem)
            $table->string('name'); 
            $table->string('brand_model')->nullable();
            $table->enum('status', ['aktif', 'tidak digunakan', 'rusak', 'hilang'])->default('aktif');
            $table->string('current_user')->nullable();
            $table->string('installation_year', 4)->nullable();
            
            // Relasi ke Lokasi (Bisa null kalau barang masih di gudang / belum dipetakan)
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('floor_id')->nullable();
            
            // Koordinat untuk Micro Studio / Live Monitor (Jika null = Tidak muncul di map)
            $table->decimal('pos_x', 10, 4)->nullable();
            $table->decimal('pos_y', 10, 4)->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->foreign('floor_id')->references('id')->on('floors');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};