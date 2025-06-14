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
        Schema::table('work_results', function (Blueprint $table) {
            $table->string('filename_before_image')->nullable()->before('before_image');
            $table->string('filename_after_image')->nullable()->before('after_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_results', function (Blueprint $table) {
            $table->dropColumn('filename_before_image');
            $table->dropColumn('filename_after_image');
        });
    }
};
