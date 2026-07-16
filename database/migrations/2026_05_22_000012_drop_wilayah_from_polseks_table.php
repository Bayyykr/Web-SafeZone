<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polseks', function (Blueprint $table) {
            if (Schema::hasColumn('polseks', 'wilayah')) {
                $table->dropColumn('wilayah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('polseks', function (Blueprint $table) {
            if (! Schema::hasColumn('polseks', 'wilayah')) {
                $table->string('wilayah')->nullable()->after('nama');
            }
        });
    }
};
