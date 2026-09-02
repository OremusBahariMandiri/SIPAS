<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {
            $table->foreignId('id_sifat_surat')
                  ->nullable()
                  ->after('id_jenis_dokumen')
                  ->constrained('a07_sifat_surat')
                  ->nullOnDelete();

            // Berapa TTE pengaju yang wajib ditempatkan sebelum submit
            $table->unsignedTinyInteger('require_tte_pengaju')
                  ->default(0)
                  ->after('id_sifat_surat')
                  ->comment('Jumlah TTE pengaju yang wajib ditempatkan (0 = tidak wajib)');

            // Berapa TTE kepada yang wajib ditempatkan saat approval final
            $table->unsignedTinyInteger('require_tte_kepada')
                  ->default(1)
                  ->after('require_tte_pengaju')
                  ->comment('Jumlah TTE kepada yang wajib ditempatkan saat approval');
        });
    }

    public function down(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {
            $table->dropForeign(['id_sifat_surat']);
            $table->dropColumn(['id_sifat_surat', 'require_tte_pengaju', 'require_tte_kepada']);
        });
    }
};