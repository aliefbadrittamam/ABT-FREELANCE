# 01. PROMPT 1: DATABASE MIGRATION & ELOQUENT MODELS

Dokumen ini berisi instruksi lengkap untuk membuat skema tabel database dan model Eloquent yang kokoh, teroptimasi, dan memiliki helper methods otomatis untuk perhitungan slot, profit, dan format broadcast.

---

## 📋 Instruksi Prompt 1

Jalankan dan terapkan skema database berikut di dalam project Laravel:

### 1. Buat Migration: `create_tournaments_table`
Jalankan command:
```bash
php artisan make:migration create_tournaments_table
```

Isi migration file:
```php
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
            $table->string('name'); // Contoh: "Turnamen eFootball 5K Get 30K"
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
```

---

### 2. Buat Migration: `create_tournament_participants_table`
Jalankan command:
```bash
php artisan make:migration create_tournament_participants_table
```

Isi migration file:
```php
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
```

Jalankan migrasi ke database MySQL:
```bash
php artisan migrate
```

---

### 3. Model `app/Models/Tournament.php`
Buat model `Tournament` dengan relasi dan kalkulasi otomatis:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tournament extends Model
{
    protected $fillable = [
        'name', 'session_label', 'entry_fee', 'prize_pool',
        'max_slots', 'admin_profit', 'status', 'winner_participant_id',
        'prize_transferred', 'prize_proof_path', 'completed_at', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'entry_fee' => 'decimal:2',
            'prize_pool' => 'decimal:2',
            'admin_profit' => 'decimal:2',
            'max_slots' => 'integer',
            'prize_transferred' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class)->orderBy('slot_number');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'winner_participant_id');
    }

    /**
     * Hitung jumlah slot yang sudah terisi.
     */
    public function getFilledSlotsCountAttribute(): int
    {
        return $this->participants()->count();
    }

    /**
     * Hitung sisa slot yang masih kosong.
     */
    public function getRemainingSlotsCountAttribute(): int
    {
        return max(0, $this->max_slots - $this->filled_slots_count);
    }

    /**
     * Cek apakah semua slot sudah terisi penuh.
     */
    public function isFull(): bool
    {
        return $this->filled_slots_count >= $this->max_slots;
    }

    /**
     * Generate teks format broadcast siap copy ke grup WhatsApp.
     */
    public function generateBroadcastMessage(): string
    {
        $feeFormatted = 'Rp ' . number_format($this->entry_fee, 0, ',', '.');
        $prizeFormatted = 'Rp ' . number_format($this->prize_pool, 0, ',', '.');
        
        $lines = [];
        $lines[] = "🏆 *TURNAMEN eFOOTBALL MOBILE ({$this->name})*";
        $lines[] = "📌 *{$this->session_label}*";
        $lines[] = "💰 Biaya Registrasi: *{$feeFormatted} / Tim*";
        $lines[] = "🎁 Hadiah Juara 1: *{$prizeFormatted}*";
        $lines[] = "";
        $lines[] = "📋 *DAFTAR SLOT PESERTA:*";

        $participants = $this->participants->keyBy('slot_number');

        for ($i = 1; $i <= $this->max_slots; $i++) {
            if (isset($participants[$i])) {
                $p = $participants[$i];
                $crown = $p->is_winner ? " 👑 [JUARA 1]" : " ✅";
                $lines[] = "{$i}. {$p->team_name}{$crown}";
            } else {
                $lines[] = "{$i}. [ KOSONG ]";
            }
        }

        $sisa = $this->remaining_slots_count;
        $lines[] = "";
        if ($sisa > 0) {
            $lines[] = "📢 *Sisa {$sisa} Slot Lagi!*";
            $lines[] = "💬 Hubungi Admin untuk registrasi & kunci slot Anda!";
        } else {
            $lines[] = "🔒 *SLOT SUDAH PENUH! Pertandingan segera dimulai.*";
        }

        return implode("\n", $lines);
    }

    /**
     * Hitung profit admin otomatis (Total Masuk - Hadiah Juara).
     */
    public function calculateProfit(): void
    {
        $totalGross = (float)$this->entry_fee * (float)$this->max_slots;
        $this->admin_profit = max(0, $totalGross - (float)$this->prize_pool);
    }

    protected static function booted(): void
    {
        static::creating(function (Tournament $t) {
            if (empty($t->admin_profit) || $t->admin_profit == 0) {
                $t->calculateProfit();
            }
        });
    }
}
```

---

### 4. Model `app/Models/TournamentParticipant.php`
Buat model `TournamentParticipant`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentParticipant extends Model
{
    protected $fillable = [
        'tournament_id', 'slot_number', 'team_name', 'contact_wa', 'is_winner'
    ];

    protected function casts(): array
    {
        return [
            'slot_number' => 'integer',
            'is_winner' => 'boolean',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Dapatkan link chat WhatsApp langsung ke tim ini jika kontak tersedia.
     */
    public function getWhatsAppUrlAttribute(): ?string
    {
        if (!$this->contact_wa) return null;
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->contact_wa);
        if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
        return "https://api.whatsapp.com/send?phone={$cleanPhone}";
    }
}
```
