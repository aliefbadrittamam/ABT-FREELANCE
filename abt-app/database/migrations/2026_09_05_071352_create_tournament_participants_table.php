<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->unsignedSmallInteger('slot_number'); // Nomor slot: 1 s/d max_slots
            $table->string('team_name'); // Nama Tim
            $table->string('contact_wa')->nullable(); // No WhatsApp (opsional)
            $table->boolean('is_winner')->default(false); // Penanda Juara 1
            $table->timestamps();

            $table->unique(['tournament_id', 'slot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_participants');
    }
};
