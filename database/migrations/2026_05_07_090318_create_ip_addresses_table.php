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
        Schema::create('ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique(); // Tidak boleh ada IP ganda
            $table->string('gateway')->nullable();
            $table->string('description')->nullable(); // Keterangan tambahan (misal: IP Public Ruang Rapat)
            
            // Relasi ke tabel aset. nullOnDelete = Jika aset dihapus, IP tidak ikut terhapus, tapi kembali kosong (null)
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete(); 
            
            $table->enum('status', ['available', 'in_use', 'reserved'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
