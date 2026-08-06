<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('file_path'); // Nama asli file saat diupload
        });

        // Backfill dokumen lama: nama file di server berformat "{timestamp}_{nama_asli_yang_disanitize}"
        // Ambil bagian setelah timestamp sebagai pendekatan nama asli, agar kolom baru tidak kosong.
        DB::table('documents')->whereNull('original_filename')->orderBy('id')->each(function ($doc) {
            $basename = basename($doc->file_path);
            $guessed = preg_replace('/^\d+_/', '', $basename);

            DB::table('documents')
                ->where('id', $doc->id)
                ->update(['original_filename' => $guessed ?: $basename]);
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('original_filename');
        });
    }
};
