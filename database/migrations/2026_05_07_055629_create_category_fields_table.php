<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->onDelete('cascade');
            $table->string('field_name'); // Contoh: 'RAM', 'Processor', 'Tipe Lensa'
            $table->string('input_type')->default('text'); // Contoh: 'text', 'number', 'date' (Berguna buat render form HTML nanti)
            $table->boolean('is_required')->default(false); // Apakah wajib diisi?
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_fields');
    }
};