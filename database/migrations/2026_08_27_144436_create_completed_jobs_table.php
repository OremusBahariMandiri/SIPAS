<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('queue');
            $table->string('display_name');
            $table->longText('payload');
            $table->integer('attempts')->default(1);
            $table->integer('run_time_ms')->nullable(); // durasi eksekusi dalam ms
            $table->timestamp('completed_at');

            $table->index('completed_at');
            $table->index('display_name');
            $table->index('queue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_jobs');
    }
};