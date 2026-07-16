<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'nama_lokasi')) {
                $table->string('nama_lokasi')->nullable()->after('id');
            }

            if (! Schema::hasColumn('locations', 'polygon_geojson')) {
                $table->longText('polygon_geojson')->nullable()->after('longitude');
            }

            if (! Schema::hasColumn('locations', 'status_kerawanan')) {
                $table->enum('status_kerawanan', ['Aman', 'Rawan', 'Sangat Rawan'])->default('Aman')->after('polygon_geojson');
            }
        });

        if (Schema::hasColumn('locations', 'nama')) {
            DB::table('locations')
                ->whereNull('nama_lokasi')
                ->update(['nama_lokasi' => DB::raw('nama')]);
        }

        DB::table('locations')
            ->whereNull('nama_lokasi')
            ->update(['nama_lokasi' => 'Lokasi Tanpa Nama']);

        DB::table('locations')
            ->whereNull('status_kerawanan')
            ->update(['status_kerawanan' => 'Aman']);

        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'nama')) {
                $table->dropColumn('nama');
            }

            if (Schema::hasColumn('locations', 'alamat')) {
                $table->dropColumn('alamat');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('locations')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'nama')) {
                $table->string('nama')->nullable()->after('id');
            }

            if (! Schema::hasColumn('locations', 'alamat')) {
                $table->string('alamat')->nullable()->after('nama');
            }
        });

        if (Schema::hasColumn('locations', 'nama_lokasi')) {
            DB::table('locations')->update(['nama' => DB::raw('nama_lokasi')]);
        }

        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'nama_lokasi')) {
                $table->dropColumn('nama_lokasi');
            }

            if (Schema::hasColumn('locations', 'polygon_geojson')) {
                $table->dropColumn('polygon_geojson');
            }

            if (Schema::hasColumn('locations', 'status_kerawanan')) {
                $table->dropColumn('status_kerawanan');
            }
        });
    }
};
