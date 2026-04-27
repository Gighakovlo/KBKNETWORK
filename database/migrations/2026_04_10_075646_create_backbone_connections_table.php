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
        Schema::create('backbone_connections', function (Blueprint $table) {
            $table->id();
            // Jalur 1: Boleh pakai cascade
            $table->foreignId('from_building_id')->constrained('buildings')->onDelete('cascade');
            
            // Jalur 2: Wajib pakai 'no action' agar SQL Server tidak panik (multiple cascade paths)
            $table->foreignId('to_building_id')->constrained('buildings')->onDelete('no action');
            
            $table->string('color')->default('#ef4444');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backbone_connections');
    }
};
