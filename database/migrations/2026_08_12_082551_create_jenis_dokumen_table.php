<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('a06_jenis_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dokumen', 20)->unique();
            $table->string('kategori_dokumen', 100);
            $table->string('jenis_dokumen', 150);
            $table->unsignedBigInteger('departemen_pemilik');
            $table->timestamps();

            $table->foreign('departemen_pemilik')
                  ->references('id')
                  ->on('a02_departemen')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('a06_jenis_dokumen');
    }
};