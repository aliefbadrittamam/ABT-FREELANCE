<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tournament extends Model
{
    protected $fillable = [
        'name', 'type', 'session_label', 'entry_fee', 'prize_pool',
        'max_slots', 'admin_profit', 'status', 'winner_participant_id',
        'prize_transferred', 'prize_proof_path', 'completed_at', 'notes'
    ];

    public function scopeFastur($query)
    {
        return $query->where('type', 'fastur');
    }

    public function scopeCustomBracket($query)
    {
        return $query->where('type', 'custom_bracket');
    }

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

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class)->orderBy('round')->orderBy('match_number');
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
