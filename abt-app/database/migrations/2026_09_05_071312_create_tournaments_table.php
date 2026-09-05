<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Turnamen 5K Get 30K"
            $table->string('session_label')->default('Sesi 1'); // Contoh: "Sesi 1", "Sesi 2", dll
            $table->decimal('entry_fee', 12, 2)->default(5000.00); // Biaya pendaftaran per tim
            $table->decimal('prize_pool', 12, 2)->default(30000.00); // Hadiah untuk Juara 1
            $table->unsignedSmallInteger('max_slots')->default(8); // Default 8 slot (opsi 4 slot)
            $table->decimal('admin_profit', 12, 2)->default(10000.00); // Selisih keuntungan admin
            $table->enum('status', ['open', 'full', 'ongoing', 'completed', 'canceled'])->default('open');
            
            // Relasi ke pemenang (juara 1)
            $table->unsignedBigInteger('winner_participant_id')->nullable();
            
            // Pengiriman hadiah ke juara
            $table->boolean('prize_transferred')->default(false);
            $table->string('prize_proof_path')->nullable(); // Foto bukti transfer hadiah
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
