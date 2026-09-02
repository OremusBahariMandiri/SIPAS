<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b03_pengajuan_approval', function (Blueprint $table) {
            $table->string('file_snapshot')->nullable()->after('acted_at');
        });
    }

    public function down(): void
    {
        Schema::table('b03_pengajuan_approval', function (Blueprint $table) {
            $table->dropColumn('file_snapshot');
        });
    }
};