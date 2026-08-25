<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropForeign(['id_departemen']);
            $table->dropColumn('id_departemen');

            // Tambah kolom baru
            $table->unsignedBigInteger('id_user')->after('id_pengajuan');
            $table->foreign('id_user')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropColumn('id_user');

            $table->unsignedBigInteger('id_departemen')->after('id_pengajuan');
            $table->foreign('id_departemen')->references('id')->on('a02_departemen');
        });
    }
};