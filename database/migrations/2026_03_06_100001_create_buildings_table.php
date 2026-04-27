<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Tambahkan 2 baris ini untuk menyimpan posisi di General Map:
            $table->float('pos_x')->default(150);
            $table->float('pos_y')->default(150);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('buildings');
    }
};