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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('image_tugas_path');
            $table->string('image_chat_path');
            $table->string('image_hasil_path');
            $table->string('image_pelunasan_path');
            $table->string('composed_image_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('client_name')->nullable();
            $table->boolean('posted_to_telegram')->default(false);
            $table->string('telegram_message_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
