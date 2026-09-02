<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            $table->boolean('is_monitoring')->default(false)->after('require_tte_count');
        });
    }

    public function down(): void
    {
        Schema::table('b02_pengajuan_terusan', function (Blueprint $table) {
            $table->dropColumn('is_monitoring');
        });
    }
};