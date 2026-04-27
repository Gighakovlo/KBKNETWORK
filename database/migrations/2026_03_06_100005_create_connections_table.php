<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            // Kita hilangkan strict foreign key ke switch, ganti dengan sistem tipe & ID
            $table->string('from_type'); // Isinya nanti: 'switch' atau 'pc'
            $table->unsignedBigInteger('from_id'); // ID dari switch atau PC tersebut
            
            $table->string('to_type'); // Isinya nanti: 'switch' atau 'pc'
            $table->unsignedBigInteger('to_id'); // ID dari switch atau PC tersebut
            
            $table->string('color')->default('#ff0000'); // Warna kabel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
