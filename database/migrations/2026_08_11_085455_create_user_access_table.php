<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_users');
            $table->string('menu_access')->comment('nama menu / route yang diakses');
            $table->tinyInteger('index_access')->default(0)->comment('1=boleh, 0=tidak');
            $table->tinyInteger('create_access')->default(0);
            $table->tinyInteger('update_access')->default(0);
            $table->tinyInteger('show_access')->default(0);
            $table->tinyInteger('delete_access')->default(0);
            $table->tinyInteger('download_access')->default(0);
            $table->tinyInteger('export_pdf_access')->default(0);
            $table->tinyInteger('export_excel_access')->default(0);
            $table->tinyInteger('approval_access')->default(0);
            $table->timestamps();

            $table->foreign('id_users')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['id_users', 'menu_access']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_access');
    }
};