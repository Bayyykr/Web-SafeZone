<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("categories", function (Blueprint $table) {
            $table->id();
            $table->string("nama_kategori");
            $table
                ->enum("jenis", ["kejahatan", "kecelakaan"])
                ->default("kejahatan");
            $table->string("warna_marker", 20)->default("#FF0000");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("categories");
    }
};
