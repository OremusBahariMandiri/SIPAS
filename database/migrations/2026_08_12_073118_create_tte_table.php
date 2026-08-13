<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('a05_tte', function (Blueprint $table) {
            $table->id();

            // Relasi ke user pemilik TTE
            $table->unsignedBigInteger('id_user')->unique(); // 1 user = 1 TTE aktif

            // Kunci kriptografi
            $table->text('private_key');        // Disimpan terenkripsi (AES)
            $table->text('public_key');         // Untuk verifikasi signature

            // Token verifikasi publik
            $table->string('verify_token', 64)->unique(); // Token unik untuk URL verifikasi

            // Status & masa berlaku
            $table->boolean('is_active')->default(true);
            $table->date('expired_at')->nullable(); // null = tidak ada batas waktu

            // Audit
            $table->unsignedBigInteger('created_by'); // Admin yang generate TTE ini
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a05_tte');
    }
};
