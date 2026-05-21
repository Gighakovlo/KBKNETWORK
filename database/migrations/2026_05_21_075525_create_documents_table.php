<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Nama Dokumen
            $table->string('category')->nullable(); // Kategori (SOP, Manual, Invoice, dll)
            $table->string('file_path'); // Alamat file di server
            $table->text('description')->nullable(); // Keterangan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};