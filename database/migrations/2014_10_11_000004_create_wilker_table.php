<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('a04_wilayah_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('wilayah_kerja');
            $table->string('skt_wilayah_kerja')->nullable()->comment('singkatan wilayah kerja');
            $table->string('area_kerja')->nullable();
            $table->string('skt_area_kerja')->nullable()->comment('singkatan area kerja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('a04_wilayah_kerja');
    }
};