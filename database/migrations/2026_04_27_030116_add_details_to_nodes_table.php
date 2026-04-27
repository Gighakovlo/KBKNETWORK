<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('switch_nodes', function (Blueprint $table) {
            $table->string('installation_year', 4)->nullable()->after('brand_model');
        });

        Schema::table('pc_nodes', function (Blueprint $table) {
            $table->string('installation_year', 4)->nullable()->after('current_user');
            $table->enum('status', ['aktif', 'tidak digunakan', 'rusak'])->default('aktif')->after('installation_year');
        });
    }

    public function down()
    {
        Schema::table('switch_nodes', function (Blueprint $table) {
            $table->dropColumn('installation_year');
        });

        Schema::table('pc_nodes', function (Blueprint $table) {
            $table->dropColumn(['installation_year', 'status']);
        });
    }
};