<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer', 20)->default('smtp');
            $table->string('host', 150);
            $table->unsignedSmallInteger('port')->default(465);
            $table->enum('encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->string('username', 150);
            $table->string('password', 255);           // disimpan terenkripsi
            $table->string('from_address', 150);
            $table->string('from_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamp('tested_at')->nullable();  // terakhir kali tes kirim
            $table->boolean('test_result')->nullable();  // true=berhasil, false=gagal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};