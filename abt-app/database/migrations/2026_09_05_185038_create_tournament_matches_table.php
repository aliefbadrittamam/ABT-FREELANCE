<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->unsignedSmallInteger('round'); // 1, 2, 3...
            $table->string('round_name'); // "64 Besar", "32 Besar", "Perempat Final", "Semifinal", "Grand Final"
            $table->unsignedSmallInteger('match_number'); // 1, 2, 3...
            
            $table->foreignId('team1_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->foreignId('team2_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('tournament_participants')->nullOnDelete();
            
            $table->unsignedBigInteger('next_match_id')->nullable();
            $table->enum('next_match_slot', ['team1', 'team2'])->nullable();

            $table->smallInteger('score1')->nullable();
            $table->smallInteger('score2')->nullable();
            $table->enum('status', ['pending', 'ready', 'completed'])->default('pending');
            $table->timestamps();

            $table->index(['tournament_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
