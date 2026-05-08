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
        // Menggunakan SQL Mentah (Raw Query) khusus SQL Server untuk menghancurkan Index
        \Illuminate\Support\Facades\DB::statement('DROP INDEX ip_addresses_ip_address_unique ON ip_addresses');
    }

    public function down()
    {
       
    }
};
