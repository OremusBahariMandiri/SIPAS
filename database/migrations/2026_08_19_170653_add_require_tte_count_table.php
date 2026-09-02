<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            // Gantikan boolean require_tte dengan angka jumlah TTE
            // require_tte lama tetap ada untuk backward compat
            // tambah kolom baru: require_tte_count
            $table->unsignedTinyInteger('require_tte_count')
                  ->default(0)
                  ->after('require_tte')
                  ->comment('Jumlah TTE yang wajib ditempatkan di tahap terusan ini (0 = tidak wajib)');
        });
    }

    public function down(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            $table->dropColumn('require_tte_count');
        });
    }
};