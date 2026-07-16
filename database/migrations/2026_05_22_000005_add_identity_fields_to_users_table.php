<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('telepon', 30)->nullable()->after('email');
            $table->string('alamat')->nullable()->after('telepon');
            $table->string('role')->default('user')->after('alamat');
            $table->boolean('aktif')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'telepon', 'alamat', 'role', 'aktif']);
        });
    }
};
