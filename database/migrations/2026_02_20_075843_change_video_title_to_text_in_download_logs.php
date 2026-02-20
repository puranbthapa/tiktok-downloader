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
        Schema::table('download_logs', function (Blueprint $table) {
            $table->text('video_title')->nullable()->change();
            $table->text('source_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('download_logs', function (Blueprint $table) {
            $table->string('video_title')->nullable()->change();
            $table->string('source_url')->change();
        });
    }
};
