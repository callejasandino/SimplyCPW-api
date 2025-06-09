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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // IMAP UID
            $table->string('message_id')->nullable(); // Email Message-ID header
            $table->string('subject')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->timestamp('email_date');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->string('priority')->default('normal');
            $table->json('flags')->nullable(); // IMAP flags
            $table->json('attachments')->nullable(); // Attachment metadata
            $table->string('folder')->default('inbox');
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['folder', 'is_deleted']);
            $table->index(['email_date']);
            $table->index(['is_read']);
            $table->index(['from_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
