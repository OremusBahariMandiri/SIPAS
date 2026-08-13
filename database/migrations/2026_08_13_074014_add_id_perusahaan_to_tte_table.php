<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('a05_tte', function (Blueprint $table) {
            // Langsung tambah kolom — tidak perlu drop apapun
            // karena unique id_user dan foreign id_user tidak ada di tabel ini
            $table->unsignedBigInteger('id_perusahaan')
                ->nullable()
                ->after('id_user');
        });

        Schema::table('a05_tte', function (Blueprint $table) {
            $table->foreign('id_perusahaan')
                ->references('id')
                ->on('a01_perusahaan');

            $table->unique(['id_user', 'id_perusahaan']);
        });
    }

    public function down(): void
    {
        Schema::table('a05_tte', function (Blueprint $table) {
            $table->dropForeign(['id_perusahaan']);
            $table->dropUnique(['id_user', 'id_perusahaan']);
            $table->dropColumn('id_perusahaan');
        });
    }
};
