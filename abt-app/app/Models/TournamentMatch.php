<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    protected $fillable = [
        'tournament_id', 'round', 'round_name', 'match_number',
        'team1_id', 'team2_id', 'winner_id',
        'next_match_id', 'next_match_slot',
        'score1', 'score2', 'status',
    ];

    protected function casts(): array
    {
        return [
            'round' => 'integer',
            'match_number' => 'integer',
            'score1' => 'integer',
            'score2' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'team1_id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'team2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentParticipant::class, 'winner_id');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_id');
    }
}
