<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'nama_kategori')) {
                $table->string('nama_kategori')->nullable()->after('id');
            }

            if (! Schema::hasColumn('categories', 'jenis')) {
                $table->enum('jenis', ['kejahatan', 'kecelakaan'])->default('kejahatan')->after('nama_kategori');
            }

            if (! Schema::hasColumn('categories', 'warna_marker')) {
                $table->string('warna_marker', 20)->default('#FF0000')->after('jenis');
            }
        });

        if (Schema::hasColumn('categories', 'nama')) {
            DB::table('categories')
                ->whereNull('nama_kategori')
                ->update(['nama_kategori' => DB::raw('nama')]);
        }

        if (Schema::hasColumn('categories', 'tipe')) {
            DB::table('categories')->update(['jenis' => DB::raw('tipe')]);
        }

        DB::table('categories')
            ->whereNull('nama_kategori')
            ->update(['nama_kategori' => 'Kategori Tanpa Nama']);

        DB::table('categories')
            ->whereNull('warna_marker')
            ->update(['warna_marker' => '#FF0000']);

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'nama')) {
                $table->dropColumn('nama');
            }

            if (Schema::hasColumn('categories', 'tipe')) {
                $table->dropColumn('tipe');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'nama')) {
                $table->string('nama')->nullable()->after('id');
            }

            if (! Schema::hasColumn('categories', 'tipe')) {
                $table->enum('tipe', ['kejahatan', 'kecelakaan'])->default('kejahatan')->after('nama');
            }
        });

        if (Schema::hasColumn('categories', 'nama_kategori')) {
            DB::table('categories')->update(['nama' => DB::raw('nama_kategori')]);
        }

        if (Schema::hasColumn('categories', 'jenis')) {
            DB::table('categories')->update(['tipe' => DB::raw('jenis')]);
        }

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'nama_kategori')) {
                $table->dropColumn('nama_kategori');
            }

            if (Schema::hasColumn('categories', 'jenis')) {
                $table->dropColumn('jenis');
            }

            if (Schema::hasColumn('categories', 'warna_marker')) {
                $table->dropColumn('warna_marker');
            }
        });
    }
};
