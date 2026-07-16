<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('nearest_polsek_id')->nullable()->constrained('polseks')->nullOnDelete();
            $table->string('kode_darurat')->unique();
            $table->enum('status', ['aktif', 'dalam_penanganan', 'selesai', 'arsip'])->default('aktif');
            $table->double('latitude');
            $table->double('longitude');
            $table->text('alamat_terdeteksi')->nullable();
            $table->double('jarak_polsek_km')->nullable();
            $table->timestamp('waktu_sos')->nullable();
            $table->timestamp('waktu_dispatch')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->string('petugas_penanganan')->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->json('telemetri')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_reports');
    }
};
