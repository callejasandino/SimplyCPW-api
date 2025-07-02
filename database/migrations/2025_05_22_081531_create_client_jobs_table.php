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
        Schema::create('client_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops');
            $table->string('title');
            $table->json('client');
            $table->dateTime('date');
            $table->integer('duration')->nullable();
            $table->enum('status', ['Scheduled', 'Pending', 'Confirmed', 'Completed', 'Cancelled']);
            $table->double('price', 10, 2)->nullable();
            $table->string('notes')->nullable();
            $table->json('services')->nullable();
            $table->json('team')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_jobs');
    }
};
