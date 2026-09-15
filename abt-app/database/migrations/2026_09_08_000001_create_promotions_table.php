<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('category_type')->default('joki'); // joki, website, tournament, general
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->string('banner_path')->nullable();
            $table->enum('banner_type', ['uploaded', 'auto_generated'])->default('uploaded');
            $table->text('copywriting');
            $table->string('target_platform')->default('WhatsApp & Telegram');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
