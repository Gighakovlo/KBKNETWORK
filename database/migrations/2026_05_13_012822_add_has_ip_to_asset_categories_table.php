<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            // Menambah kolom boolean (true/false) setelah kolom prefix
            $table->boolean('has_ip')->default(false)->after('prefix');
        });
    }

    public function down()
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('has_ip');
        });
    }
};