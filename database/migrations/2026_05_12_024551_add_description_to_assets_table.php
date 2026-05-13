<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        if (!Schema::hasColumn('assets', 'description')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->text('description')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
