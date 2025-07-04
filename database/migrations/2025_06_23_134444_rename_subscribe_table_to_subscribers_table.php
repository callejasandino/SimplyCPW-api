<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('subscribe', 'subscribers');
    }

    public function down(): void
    {
        Schema::rename('subscribers', 'subscribe');
    }
};
