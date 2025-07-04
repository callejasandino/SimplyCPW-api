<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dashboard_data', function (Blueprint $table) {
            $table->id();
            $table->json('stats')->nullable();
            $table->json('monthlyData')->nullable();
            $table->json('serviceDistribution')->nullable();
            $table->json('leadSources')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_data');
    }
};
