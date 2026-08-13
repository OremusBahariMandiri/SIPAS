<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nrk')->unique()->comment('Nomor Registrasi Karyawan');
            $table->string('password');
            $table->unsignedBigInteger('id_perusahaan');
            $table->unsignedBigInteger('id_departemen');
            $table->string('jabatan')->nullable();
            $table->string('wilker')->nullable()->comment('wilayah kerja');
            $table->tinyInteger('is_admin')->default(0)->comment('1=admin, 0=user');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_perusahaan')->references('id')->on('a01_perusahaan')->onDelete('restrict');
            $table->foreign('id_departemen')->references('id')->on('a02_departemen')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};