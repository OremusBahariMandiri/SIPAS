<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM kolom tahap — tambahkan nilai 'pengaju'
        DB::statement("
            ALTER TABLE b04_pengajuan_tte_placement
            MODIFY COLUMN tahap ENUM('terusan', 'kepada', 'pengaju') NOT NULL
        ");
    }

    public function down(): void
    {
        // Rollback — hapus nilai 'pengaju' (data dengan tahap=pengaju harus dihapus dulu)
        DB::statement("
            ALTER TABLE b04_pengajuan_tte_placement
            MODIFY COLUMN tahap ENUM('terusan', 'kepada') NOT NULL
        ");
    }
};