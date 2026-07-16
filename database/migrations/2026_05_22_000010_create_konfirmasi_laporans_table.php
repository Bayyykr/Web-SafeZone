<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfirmasi_laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->unique()->constrained('laporans')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'valid', 'ditolak'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfirmasi_laporans');
    }
};
