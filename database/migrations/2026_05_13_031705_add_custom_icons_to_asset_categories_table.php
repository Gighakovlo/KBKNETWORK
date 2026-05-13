<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('icon_path')->nullable()->after('has_ip'); 
            $table->integer('icon_size')->default(40)->after('icon_path'); 
            $table->string('color', 10)->default('#3b82f6')->after('icon_size'); 
        });
    }

    public function down()
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn(['icon_path', 'icon_size', 'color']);
        });
    }
};