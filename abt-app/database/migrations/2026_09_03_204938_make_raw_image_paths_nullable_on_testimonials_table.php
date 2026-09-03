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
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('image_tugas_path')->nullable()->change();
            $table->string('image_chat_path')->nullable()->change();
            $table->string('image_hasil_path')->nullable()->change();
            $table->string('image_pelunasan_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('image_tugas_path')->nullable(false)->change();
            $table->string('image_chat_path')->nullable(false)->change();
            $table->string('image_hasil_path')->nullable(false)->change();
            $table->string('image_pelunasan_path')->nullable(false)->change();
        });
    }
};
