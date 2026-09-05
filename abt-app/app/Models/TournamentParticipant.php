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
