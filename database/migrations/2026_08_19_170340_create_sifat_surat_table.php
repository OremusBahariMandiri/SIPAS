<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('a07_sifat_surat', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('keterangan', 255)->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('a07_sifat_surat');
    }
};