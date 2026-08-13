<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('a02_departemen', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('singkatan')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=aktif, 0=nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('a02_departemen');
    }
};