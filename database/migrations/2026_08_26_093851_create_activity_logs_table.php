<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // ── Siapa yang melakukan ─────────────────────────────────
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_nrk', 50)->nullable();          // snapshot agar tetap terbaca walau user dihapus
            $table->string('user_name', 100)->nullable();

            // ── Kategori & aksi ──────────────────────────────────────
            // module  : auth | users | submission | approval | tte
            // action  : login | logout | create | update | delete |
            //           approve | reject | tte_placed | tte_signed | resubmit
            $table->string('module', 50)->index();
            $table->string('action', 50)->index();

            // ── Subyek (objek yang dikenai aksi) ─────────────────────
            // subject_type : App\Models\User | App\Models\Data\PengajuanSurat | …
            // subject_id   : primary-key dari record tersebut
            $table->string('subject_type', 150)->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->string('subject_label', 255)->nullable();     // deskripsi singkat subyek

            // ── Detail perubahan ─────────────────────────────────────
            // before / after : JSON snapshot field yang berubah (tanpa field sensitif)
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            // ── Konteks request ──────────────────────────────────────
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // ── Catatan tambahan ─────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();

            // Composite index untuk filter umum
            $table->index(['module', 'action']);
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};