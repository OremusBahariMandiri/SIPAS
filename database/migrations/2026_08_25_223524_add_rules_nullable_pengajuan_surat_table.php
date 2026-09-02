<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {

            // String / text columns
            $table->string('nomor_surat', 100)->nullable()->change();
            $table->string('perihal', 255)->nullable()->change();
            $table->string('file_original')->nullable()->change();
            $table->string('file_current')->nullable()->change();
            $table->string('file_signed')->nullable()->change();
            $table->string('status')->nullable()->change();

            // Datetime
            $table->dateTime('tanggal_surat')->nullable()->change();

            // Foreign keys / integers
            $table->unsignedBigInteger('id_perusahaan')->nullable()->change();
            $table->unsignedBigInteger('id_kepada')->nullable()->change();
            $table->unsignedBigInteger('id_jenis_dokumen')->nullable()->change();
            $table->unsignedBigInteger('id_sifat_surat')->nullable()->change();
            $table->unsignedBigInteger('id_user')->nullable()->change();

            // Numeric counters
            $table->integer('require_tte_pengaju')->nullable()->change();
            $table->integer('require_tte_kepada')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {

            $table->string('nomor_surat', 100)->nullable(false)->change();
            $table->string('perihal', 255)->nullable(false)->change();
            $table->string('file_original')->nullable(false)->change();
            $table->string('file_current')->nullable(false)->change();
            $table->string('file_signed')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();

            $table->dateTime('tanggal_surat')->nullable(false)->change();

            $table->unsignedBigInteger('id_perusahaan')->nullable(false)->change();
            $table->unsignedBigInteger('id_kepada')->nullable(false)->change();
            $table->unsignedBigInteger('id_jenis_dokumen')->nullable(false)->change();
            $table->unsignedBigInteger('id_sifat_surat')->nullable(false)->change();
            $table->unsignedBigInteger('id_user')->nullable(false)->change();

            $table->integer('require_tte_pengaju')->nullable(false)->change();
            $table->integer('require_tte_kepada')->nullable(false)->change();
        });
    }
};