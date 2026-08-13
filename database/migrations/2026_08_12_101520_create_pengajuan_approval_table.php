<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b03_pengajuan_approval', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan');
            $table->enum('tahap', ['terusan', 'kepada']);
            $table->unsignedBigInteger('id_ref')->default(0); // id_terusan atau 0 jika kepada
            $table->unsignedBigInteger('id_approver');
            $table->enum('aksi', ['approve', 'reject']);
            $table->text('catatan')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id')->on('b01_pengajuan_surat')->onDelete('cascade');
            $table->foreign('id_approver')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b03_pengajuan_approval');
    }
};