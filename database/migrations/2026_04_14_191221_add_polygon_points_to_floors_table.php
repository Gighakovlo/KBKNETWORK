<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('floors', function (Blueprint $table) {
            // Menambahkan memori bentuk bebas untuk lantai
            $table->text('polygon_points')->nullable()->after('box_height');
        });
    }

    public function down()
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn('polygon_points');
        });
    }
};
