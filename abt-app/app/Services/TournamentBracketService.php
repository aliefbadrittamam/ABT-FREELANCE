<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentParticipant;
use Illuminate\Support\Facades\DB;

class TournamentBracketService
{
    /**
     * Get round names according to total rounds.
     */
    public function getRoundNames(int $totalRounds): array
    {
        $names = [];
        for ($r = 1; $r <= $totalRounds; $r++) {
            $fromFinal = $totalRounds - $r; // 0 is final, 1 is semi, 2 is qf...
            $names[$r] = match($fromFinal) {
                0 => 'Grand Final',
                1 => 'Semifinal',
                2 => 'Perempat Final',
                3 => 'Babak 16 Besar',
                4 => 'Babak 32 Besar',
                5 => 'Babak 64 Besar',
                default => "Ronde {$r}"
            };
        }
        return $names;
    }

    /**
     * Generate or regenerate full bracket matches tree for a tournament.
     *
     * @param Tournament $tournament
     * @param bool $randomize Whether to shuffle participant seeding
     * @return void
     */
    public function generateBracket(Tournament $tournament, bool $randomize = false): void
    {
        DB::transaction(function () use ($tournament, $randomize) {
            // Delete existing matches for this tournament if any
            $tournament->matches()->delete();

            $maxSlots = in_array($tournament->max_slots, [4, 8, 16, 32, 64]) ? $tournament->max_slots : 8;
            $totalRounds = (int)log($maxSlots, 2);
            $roundNames = $this->getRoundNames($totalRounds);

            // Step 1: Create matches backward from Final (round R down to 1) to link next_match_id
            $matchesByRound = [];

            for ($r = $totalRounds; $r >= 1; $r--) {
                $matchesInThisRound = (int)pow(2, $totalRounds - $r);
                $matchesByRound[$r] = [];

                for ($m = 1; $m <= $matchesInThisRound; $m++) {
                    $nextMatchId = null;
                    $nextMatchSlot = null;

                    if ($r < $totalRounds) {
                        $parentMatchNum = (int)ceil($m / 2);
                        $parentMatch = $matchesByRound[$r + 1][$parentMatchNum] ?? null;
                        if ($parentMatch) {
                            $nextMatchId = $parentMatch->id;
                            $nextMatchSlot = ($m % 2 !== 0) ? 'team1' : 'team2';
                        }
                    }

                    $match = TournamentMatch::create([
                        'tournament_id' => $tournament->id,
                        'round' => $r,
                        'round_name' => $roundNames[$r],
                        'match_number' => $m,
                        'next_match_id' => $nextMatchId,
                        'next_match_slot' => $nextMatchSlot,
                        'status' => 'pending',
                    ]);

                    $matchesByRound[$r][$m] = $match;
                }
            }

            // Step 2: Seed participants into Round 1
            $participants = $tournament->participants()->get();
            if ($randomize) {
                $participants = $participants->shuffle();
            } else {
                $participants = $participants->sortBy('slot_number')->values();
            }

            $round1Matches = $matchesByRound[1];
            $partArray = $participants->all();

            foreach ($round1Matches as $m => $match) {
                $idx1 = ($m - 1) * 2;
                $idx2 = $idx1 + 1;

                $team1 = $partArray[$idx1] ?? null;
                $team2 = $partArray[$idx2] ?? null;

                $match->team1_id = $team1?->id;
                $match->team2_id = $team2?->id;
                $match->status = ($team1 && $team2) ? 'ready' : ($team1 || $team2 ? 'ready' : 'pending');

                // If only 1 team exists in match, automatic BYE win
                if ($team1 && !$team2 && count($partArray) < $maxSlots) {
                    $match->winner_id = $team1->id;
                    $match->status = 'completed';
                    $this->advanceWinner($match, $team1->id);
                } elseif (!$team1 && $team2 && count($partArray) < $maxSlots) {
                    $match->winner_id = $team2->id;
                    $match->status = 'completed';
                    $this->advanceWinner($match, $team2->id);
                }

                $match->save();
            }
        });
    }

    /**
     * Set winner for a match and advance to next round or set champion.
     */
    public function setMatchWinner(TournamentMatch $match, int $winnerId, ?int $score1 = null, ?int $score2 = null): void
    {
        DB::transaction(function () use ($match, $winnerId, $score1, $score2) {
            $match->winner_id = $winnerId;
            $match->score1 = $score1;
            $match->score2 = $score2;
            $match->status = 'completed';
            $match->save();

            $this->advanceWinner($match, $winnerId);
        });
    }

    /**
     * Internal helper to push winner to next match in tree or set tournament champion.
     */
    protected function advanceWinner(TournamentMatch $match, int $winnerId): void
    {
        if ($match->next_match_id) {
            $nextMatch = TournamentMatch::find($match->next_match_id);
            if ($nextMatch) {
                if ($match->next_match_slot === 'team1') {
                    $nextMatch->team1_id = $winnerId;
                } else {
                    $nextMatch->team2_id = $winnerId;
                }

                if ($nextMatch->team1_id && $nextMatch->team2_id) {
                    $nextMatch->status = 'ready';
                }

                $nextMatch->save();
            }
        } else {
            // This is the Grand Final!
            $tournament = $match->tournament;
            if ($tournament) {
                $tournament->participants()->update(['is_winner' => false]);
                $winner = TournamentParticipant::find($winnerId);
                if ($winner) {
                    $winner->update(['is_winner' => true]);
                    $tournament->update([
                        'winner_participant_id' => $winner->id,
                    ]);
                }
            }
        }
    }
}
