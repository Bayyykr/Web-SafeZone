<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("locations", function (Blueprint $table) {
            $table->id();
            $table->string("nama_lokasi");
            $table->double("latitude")->nullable();
            $table->double("longitude")->nullable();
            $table->longText("polygon_geojson")->nullable();
            $table
                ->enum("status_kerawanan", ["Aman", "Rawan", "Sangat Rawan"])
                ->default("Aman");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("locations");
    }
};
