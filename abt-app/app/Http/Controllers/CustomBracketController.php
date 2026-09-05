<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Models\TournamentMatch;
use App\Services\TournamentBracketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CustomBracketController extends Controller
{
    /**
     * Tampilkan daftar turnamen laga custom berbagan (8, 16, 32, 64 Tim).
     */
    public function index()
    {
        $tournaments = Tournament::customBracket()
            ->with(['participants', 'winner'])
            ->latest('id')
            ->paginate(12);

        $activeCount = Tournament::customBracket()->whereIn('status', ['open', 'full', 'ongoing'])->count();
        $completedCount = Tournament::customBracket()->where('status', 'completed')->count();
        $totalProfit = (float)Tournament::customBracket()->where('status', 'completed')->sum('admin_profit');

        return view('tour-organizer.custom-bracket.index', compact(
            'tournaments', 'activeCount', 'completedCount', 'totalProfit'
        ));
    }

    /**
     * Form buat turnamen custom bagan baru.
     */
    public function create()
    {
        return view('tour-organizer.custom-bracket.create');
    }

    /**
     * Simpan turnamen custom bagan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'session_label' => 'required|string|max:50',
            'entry_fee' => 'required|numeric|min:0',
            'prize_pool' => 'required|numeric|min:0',
            'max_slots' => 'required|in:8,16,32,64',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fee = (float)$validated['entry_fee'];
        $prize = (float)$validated['prize_pool'];
        $slots = (int)$validated['max_slots'];

        $gross = $fee * $slots;
        $adminProfit = max(0, $gross - $prize);

        $tournament = Tournament::create([
            'name' => $validated['name'],
            'type' => 'custom_bracket',
            'session_label' => $validated['session_label'],
            'entry_fee' => $fee,
            'prize_pool' => $prize,
            'max_slots' => $slots,
            'admin_profit' => $adminProfit,
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tour-organizer.custom-bracket.show', $tournament)
            ->with('success', "Turnamen Custom {$tournament->name} ({$slots} Tim) berhasil dibuat! Silakan buka pendaftaran atau isi tim.");
    }

    /**
     * Tampilkan detail turnamen custom bagan, visual bracket tree, dan manajemen tim.
     */
    public function show(Tournament $tournament)
    {
        $tournament->load(['participants', 'winner']);
        $broadcastMessage = $tournament->generateBroadcastMessage();

        $matchesByRound = $tournament->matches()
            ->with(['team1', 'team2', 'winner'])
            ->get()
            ->groupBy('round');

        return view('tour-organizer.custom-bracket.show', compact('tournament', 'broadcastMessage', 'matchesByRound'));
    }

    /**
     * Daftarkan tim ke slot turnamen custom.
     */
    public function registerParticipant(Request $request, Tournament $tournament)
    {
        $request->validate([
            'team_name' => 'required|string|max:100',
            'contact_wa' => 'nullable|string|max:30',
            'slot_number' => 'nullable|integer|min:1|max:' . $tournament->max_slots,
        ]);

        if ($tournament->isFull()) {
            return back()->with('error', 'Semua slot pendaftaran turnamen sudah penuh!');
        }

        $usedSlots = $tournament->participants()->pluck('slot_number')->toArray();
        $targetSlot = $request->filled('slot_number') ? (int)$request->slot_number : null;

        if ($targetSlot && in_array($targetSlot, $usedSlots)) {
            return back()->with('error', "Slot nomor {$targetSlot} sudah terisi.");
        }

        if (!$targetSlot) {
            for ($i = 1; $i <= $tournament->max_slots; $i++) {
                if (!in_array($i, $usedSlots)) {
                    $targetSlot = $i;
                    break;
                }
            }
        }

        TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'slot_number' => $targetSlot,
            'team_name' => trim($request->team_name),
            'contact_wa' => $request->contact_wa ? trim($request->contact_wa) : null,
        ]);

        if ($tournament->fresh()->isFull() && $tournament->status === 'open') {
            $tournament->update(['status' => 'full']);
        }

        return back()->with('success', "Tim {$request->team_name} berhasil didaftarkan ke Slot #{$targetSlot}!");
    }

    /**
     * Hapus peserta dari turnamen custom.
     */
    public function removeParticipant(Tournament $tournament, TournamentParticipant $participant)
    {
        if ($participant->tournament_id !== $tournament->id) {
            abort(403);
        }

        $slot = $participant->slot_number;
        $team = $participant->team_name;

        if ($tournament->winner_participant_id === $participant->id) {
            $tournament->update(['winner_participant_id' => null]);
        }

        $participant->delete();

        if ($tournament->status === 'full') {
            $tournament->update(['status' => 'open']);
        }

        return back()->with('success', "Slot #{$slot} ({$team}) berhasil dikosongkan.");
    }

    /**
     * Mulai laga turnamen custom.
     */
    public function startTournament(Tournament $tournament)
    {
        if ($tournament->participants()->count() < 2) {
            return back()->with('error', 'Minimal harus ada 2 tim terdaftar untuk memulai turnamen!');
        }

        $tournament->update(['status' => 'ongoing']);

        return back()->with('success', "🚀 Turnamen {$tournament->name} resmi DIMULAI! Anda sekarang dapat menjalankan pertandingan bagan.");
    }

    /**
     * Generate atau Acak Bagan (Randomize) untuk turnamen custom.
     */
    public function generateBracket(Request $request, Tournament $tournament, TournamentBracketService $bracketService)
    {
        if ($tournament->participants()->count() < 2) {
            return back()->with('error', 'Minimal harus ada 2 tim terdaftar untuk membuat bagan!');
        }

        $randomize = $request->boolean('randomize');
        $bracketService->generateBracket($tournament, $randomize);

        $msg = $randomize 
            ? "Bagan turnamen ({$tournament->max_slots} Tim) berhasil diacak (Randomized)!"
            : "Bagan turnamen ({$tournament->max_slots} Tim) berhasil disusun berurutan!";

        return back()->with('success', $msg);
    }

    /**
     * Tentukan pemenang match tertentu dan loloskan ke ronde berikutnya.
     */
    public function advanceMatch(Request $request, Tournament $tournament, TournamentMatch $match, TournamentBracketService $bracketService)
    {
        $request->validate([
            'winner_id' => 'required|exists:tournament_participants,id',
            'score1' => 'nullable|integer|min:0',
            'score2' => 'nullable|integer|min:0',
        ]);

        $winnerId = (int)$request->winner_id;
        $score1 = $request->filled('score1') ? (int)$request->score1 : null;
        $score2 = $request->filled('score2') ? (int)$request->score2 : null;

        $bracketService->setMatchWinner($match, $winnerId, $score1, $score2);

        $winner = TournamentParticipant::find($winnerId);
        $teamName = $winner?->team_name ?? 'Tim';

        // Jika match ini adalah Grand Final
        if (!$match->next_match_id) {
            $prizeFormatted = 'Rp ' . number_format($tournament->prize_pool, 0, ',', '.');
            $waConfirmText = "Halo Tim *{$teamName}*, Selamat telah berhasil memenangkan Turnamen *{$tournament->name}* sebagai *JUARA 1*!\n\n"
                           . "🎁 Hadiah sebesar *{$prizeFormatted}* akan segera kami transfer.\n"
                           . "Mohon kirimkan data rekening Bank / Nomor E-Wallet Anda. Terima kasih!";

            return back()->with('success', "🎉 Tim {$teamName} memenangkan Grand Final dan dinobatkan sebagai JUARA 1!")
                         ->with('winner_wa_message', $waConfirmText)
                         ->with('winner_wa_phone', $winner?->contact_wa);
        }

        return back()->with('success', "Tim {$teamName} berhasil memenangkan match dan melaju ke babak berikutnya!");
    }

    /**
     * Selesaikan turnamen custom.
     */
    public function complete(Tournament $tournament)
    {
        $tournament->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('tour-organizer.custom-bracket.index')
            ->with('success', "Turnamen {$tournament->name} telah selesai. Laba bersih Rp " . number_format($tournament->admin_profit, 0, ',', '.') . " berhasil direkap!");
    }

    /**
     * Update link live monitoring / streaming.
     */
    public function updateLiveLink(Request $request, Tournament $tournament)
    {
        $request->validate([
            'live_link' => 'nullable|string|max:500',
            'save_as_default' => 'nullable|boolean',
        ]);

        $link = $request->filled('live_link') ? trim($request->live_link) : null;

        $tournament->update([
            'live_link' => $link,
        ]);

        if ($request->boolean('save_as_default')) {
            \App\Models\PaymentSetting::getSettings()->update([
                'default_tournament_live_link' => $link,
            ]);
            return back()->with('success', 'Link live berhasil disimpan & dijadikan sebagai DEFAULT untuk semua turnamen!');
        }

        return back()->with('success', 'Link live bagan berhasil disimpan untuk turnamen ini!');
    }

    /**
     * Hapus turnamen custom.
     */
    public function destroy(Tournament $tournament)
    {
        $tournament->delete();
        return redirect()->route('tour-organizer.custom-bracket.index')
            ->with('success', "Turnamen custom berhasil dihapus.");
    }
}
