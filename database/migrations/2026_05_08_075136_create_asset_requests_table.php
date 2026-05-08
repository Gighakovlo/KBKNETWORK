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
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requester_name'); // Nama pemohon
            $table->string('department'); // Divisi/Departemen
            $table->string('request_type'); // Cth: Permintaan Barang Baru, Perbaikan, Kabel LAN, dll
            $table->text('description'); // Penjelasan detail
            $table->enum('status', ['pending', 'completed'])->default('pending'); // Status tiket
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
