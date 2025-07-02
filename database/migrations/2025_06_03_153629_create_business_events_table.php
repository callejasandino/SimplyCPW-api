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
        Schema::create('business_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->enum('event_type', ['launching', 'promotional', 'announcement']);
            $table->string('filename')->nullable();
            $table->string('image');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['draft', 'published', 'archived', 'scheduled']);
            $table->string('cta_link')->nullable();
            $table->string('cta_label')->nullable();
            $table->boolean('visible')->default(false);
            $table->json('discounted_services')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_events');
    }
};
