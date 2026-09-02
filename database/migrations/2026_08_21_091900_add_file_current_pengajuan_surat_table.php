<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {
            // Menyimpan path PDF yang paling terakhir di-TTE.
            // Diperbarui setiap kali ada inject QR baru (pengaju, terusan, kepada).
            // NULL berarti belum ada TTE sama sekali → tampilkan file_original.
            $table->string('file_current')->nullable()->after('file_original');
        });
    }

    public function down(): void
    {
        Schema::table('b01_pengajuan_surat', function (Blueprint $table) {
            $table->dropColumn('file_current');
        });
    }
};