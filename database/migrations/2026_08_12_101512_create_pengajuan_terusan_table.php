<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b02_pengajuan_terusan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan');
            $table->unsignedBigInteger('id_departemen');
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->boolean('require_tte')->default(false);
            $table->enum('status', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id')->on('b01_pengajuan_surat')->onDelete('cascade');
            $table->foreign('id_departemen')->references('id')->on('a02_departemen');
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b02_pengajuan_terusan');
    }
};