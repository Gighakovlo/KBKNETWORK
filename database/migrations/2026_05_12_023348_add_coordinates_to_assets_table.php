<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        // Cek dulu, kalau kolom pos_x belum ada, baru bikin
        if (!Schema::hasColumn('assets', 'pos_x')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->decimal('pos_x', 8, 2)->nullable()->after('floor_id');
            });
        }

        // Cek juga untuk pos_y
        if (!Schema::hasColumn('assets', 'pos_y')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->decimal('pos_y', 8, 2)->nullable()->after('pos_x');
            });
        }
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'pos_x')) {
                $table->dropColumn('pos_x');
            }
            if (Schema::hasColumn('assets', 'pos_y')) {
                $table->dropColumn('pos_y');
            }
        });
    }
};
