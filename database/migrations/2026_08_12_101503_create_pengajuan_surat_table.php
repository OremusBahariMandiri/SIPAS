<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b01_pengajuan_surat', function (Blueprint $table) {
            $table->id();
            $table->datetime('tanggal_surat');
            $table->unsignedBigInteger('id_perusahaan');
            $table->unsignedBigInteger('id_kepada');
            $table->string('nomor_surat', 100);
            $table->unsignedBigInteger('id_jenis_dokumen');
            $table->string('perihal', 255);
            $table->string('file_original', 500);
            $table->string('file_signed', 500)->nullable();
            $table->enum('status', ['draft', 'waiting', 'in_review', 'approved', 'rejected'])
                  ->default('draft');
            $table->unsignedBigInteger('id_user');
            $table->timestamps();

            $table->foreign('id_perusahaan')->references('id')->on('a01_perusahaan');
            $table->foreign('id_kepada')->references('id')->on('users');
            $table->foreign('id_jenis_dokumen')->references('id')->on('a06_jenis_dokumen');
            $table->foreign('id_user')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b01_pengajuan_surat');
    }
};