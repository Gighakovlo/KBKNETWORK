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
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('field_changed'); // Menyimpan nama kolom yang berubah (misal: status, ip_address)
            $table->text('old_value')->nullable(); // Nilai sebelum diubah
            $table->text('new_value')->nullable(); // Nilai sesudah diubah
            $table->string('changed_by')->nullable(); // Siapa yang mengubah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
