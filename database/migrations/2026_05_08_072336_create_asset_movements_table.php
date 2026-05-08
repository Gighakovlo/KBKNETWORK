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
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke barang yang dipindah. cascadeOnDelete = jika barang musnah, sejarahnya ikut musnah (atau bisa di-null-kan jika ingin sejarah tetap ada)
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            
            // Kita simpan lokasi/user lama dan baru dalam bentuk Teks (String), 
            // agar jika nama Gedung di masa depan dihapus dari master, log sejarah ini tetap utuh dan bisa dibaca.
            $table->string('previous_location')->nullable();
            $table->string('new_location')->nullable();
            $table->string('previous_user')->nullable();
            $table->string('new_user')->nullable();
            
            $table->string('reason')->nullable(); // Alasan pindah
            $table->date('movement_date'); // Tanggal mutasi
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
