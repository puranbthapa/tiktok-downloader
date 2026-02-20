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
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->string('platform');          // tiktok, facebook, pinterest
            $table->text('source_url');           // original URL user submitted
            $table->string('video_title')->nullable();
            $table->string('ip_address', 45);
            $table->string('country')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->boolean('downloaded')->default(false); // true when stream/download hit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_logs');
    }
};
