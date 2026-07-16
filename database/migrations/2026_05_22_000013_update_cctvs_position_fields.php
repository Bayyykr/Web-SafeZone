<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cctvs', 'keterangan')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('url_stream');
            });
        }

        if (Schema::hasColumn('cctvs', 'latitude')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->dropColumn('latitude');
            });
        }

        if (Schema::hasColumn('cctvs', 'longitude')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->dropColumn('longitude');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('cctvs', 'latitude')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table
                    ->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('url_stream');
            });
        }

        if (! Schema::hasColumn('cctvs', 'longitude')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table
                    ->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            });
        }

        if (Schema::hasColumn('cctvs', 'keterangan')) {
            Schema::table('cctvs', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};
