<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('polsek_id')->nullable()->constrained('polseks')->nullOnDelete();
            $table->string('judul_laporan');
            $table->text('deskripsi')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('foto_kejadian')->nullable();
            $table->enum('status', ['pending', 'dikonfirmasi', 'ditolak', 'selesai'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
