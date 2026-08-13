<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b04_pengajuan_tte_placement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan');
            $table->unsignedBigInteger('id_tte');
            $table->enum('tahap', ['terusan', 'kepada']);
            $table->unsignedBigInteger('id_ref')->default(0);
            $table->unsignedSmallInteger('halaman')->default(1);
            $table->decimal('pos_x', 8, 2);
            $table->decimal('pos_y', 8, 2);
            $table->decimal('lebar', 6, 2)->default(100);
            $table->decimal('tinggi', 6, 2)->default(100);
            $table->string('qr_token', 64)->unique();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id')->on('b01_pengajuan_surat')->onDelete('cascade');
            $table->foreign('id_tte')->references('id')->on('a05_tte');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b04_pengajuan_tte_placement');
    }
};