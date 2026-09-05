<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Models\TournamentMatch;
use App\Services\TournamentBracketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    /**
     * Tampilkan halaman utama turnamen: daftar sesi aktif & riwayat selesai.
     */
    public function index()
    {
        // 1. Sesi Aktif (Bisa paralel, status open / full / ongoing)
        $activeTournaments = Tournament::with(['participants'])
            ->whereIn('status', ['open', 'full', 'ongoing'])
            ->latest('id')
            ->get();

        // 2. Riwayat Turnamen yang Selesai
        $completedTournaments = Tournament::with(['participants', 'winner'])
            ->whereIn('status', ['completed', 'canceled'])
            ->latest('completed_at')
            ->paginate(10);

        // 3. Statistik Ringkas Keuangan Turnamen
        $totalCompleted = Tournament::where('status', 'completed')->count();
        $totalProfitAccumulated = Tournament::where('status', 'completed')->sum('admin_profit');
        $activeSessionsCount = $activeTournaments->count();

        return view('tour-organizer.efootball.index', compact(
            'activeTournaments',
            'completedTournaments',
            'totalCompleted',
            'totalProfitAccumulated',
            'activeSessionsCount'
        ));
    }

    /**
     * Form pembuatan sesi turnamen baru.
     */
    public function create()
    {
        // Hitung nomor urut sesi hari ini otomatis (contoh: Sesi 1, Sesi 2)
        $todayCount = Tournament::whereDate('created_at', now()->today())->count();
        $suggestedSession = 'Sesi ' . ($todayCount + 1);

        return view('tour-organizer.efootball.create', compact('suggestedSession'));
    }

    /**
     * Simpan sesi turnamen baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_label' => 'required|string|max:50',
            'entry_fee' => 'required|numeric|min:0',
            'prize_pool' => 'required|numeric|min:0',
            'max_slots' => 'required|in:4,8,16,32,64',
            'notes' => 'nullable|string|max:500',
        ]);

        $fee = (float)$validated['entry_fee'];
        $prize = (float)$validated['prize_pool'];
        $slots = (int)$validated['max_slots'];

        // Format nama turnamen otomatis jika tidak diisi custom
        $feeInK = ($fee >= 1000) ? ($fee / 1000) . 'K' : $fee;
        $prizeInK = ($prize >= 1000) ? ($prize / 1000) . 'K' : $prize;
        $name = "Turnamen {$feeInK} Get {$prizeInK}";

        $gross = $fee * $slots;
        $adminProfit = max(0, $gross - $prize);

        $tournament = Tournament::create([
            'name' => $name,
            'session_label' => $validated['session_label'],
            'entry_fee' => $fee,
            'prize_pool' => $prize,
            'max_slots' => $slots,
            'admin_profit' => $adminProfit,
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tour-organizer.efootball.show', $tournament)
            ->with('success', "Turnamen {$tournament->name} ({$tournament->session_label}) berhasil dibuat!");
    }

    /**
     * Tampilkan detail sesi, tabel slot 1-8, tombol aksi juara, dan copy broadcast.
     */
    public function show(Tournament $tournament)
    {
        $tournament->load(['participants', 'winner']);
        $broadcastMessage = $tournament->generateBroadcastMessage();

        $matchesByRound = $tournament->matches()
            ->with(['team1', 'team2', 'winner'])
            ->get()
            ->groupBy('round');

        return view('tour-organizer.efootball.show', compact('tournament', 'broadcastMessage', 'matchesByRound'));
    }

    /**
     * Generate or randomize bagan / tournament bracket.
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
            : "Bagan turnamen ({$tournament->max_slots} Tim) berhasil dibuat sesuai nomor slot!";

        return back()->with('success', $msg);
    }

    /**
     * Advance winner of a specific bracket match.
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

        // Check if this was the final match
        if (!$match->next_match_id) {
            $prizeFormatted = 'Rp ' . number_format($tournament->prize_pool, 0, ',', '.');
            $waConfirmText = "Halo Tim *{$teamName}*, Selamat telah berhasil menjadi *JUARA 1* pada Turnamen eFootball Mobile ({$tournament->name} - {$tournament->session_label})!\n\n"
                           . "🎁 Hadiah sebesar *{$prizeFormatted}* akan segera kami transfer.\n"
                           . "Mohon kirimkan data rekening Bank / Nomor E-Wallet Anda. Terima kasih!";

            return back()->with('success', "🎉 Tim {$teamName} memenangkan Grand Final dan dinobatkan sebagai JUARA 1!")
                         ->with('winner_wa_message', $waConfirmText)
                         ->with('winner_wa_phone', $winner?->contact_wa);
        }

        return back()->with('success', "Tim {$teamName} berhasil melaju ke babak berikutnya!");
    }

    /**
     * Daftarkan tim ke slot tertentu (1 s/d max_slots).
     * Prinsip: Masuk slot = Pembayaran registrasi sudah lunas masuk ke rekening admin/QRIS.
     */
    public function registerParticipant(Request $request, Tournament $tournament)
    {
        $request->validate([
            'team_name' => 'required|string|max:100',
            'contact_wa' => 'nullable|string|max:30',
            'slot_number' => 'nullable|integer|min:1|max:' . $tournament->max_slots,
        ]);

        // Cek jika slot sudah penuh
        if ($tournament->isFull()) {
            return back()->with('error', 'Semua slot pada turnamen sesi ini sudah penuh!');
        }

        // Tentukan nomor slot (jika dipilih manual atau otomatis mengisi slot kosong berikutnya)
        $usedSlots = $tournament->participants()->pluck('slot_number')->toArray();
        $targetSlot = $request->filled('slot_number') ? (int)$request->slot_number : null;

        if ($targetSlot && in_array($targetSlot, $usedSlots)) {
            return back()->with('error', "Slot nomor {$targetSlot} sudah terisi oleh tim lain.");
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

        // Jika setelah ditambah slot jadi penuh, ubah status ke 'full'
        if ($tournament->fresh()->isFull() && $tournament->status === 'open') {
            $tournament->update(['status' => 'full']);
        }

        return back()->with('success', "Tim {$request->team_name} berhasil dimasukkan ke Slot #{$targetSlot}!");
    }

    /**
     * Hapus peserta dari slot tertentu.
     */
    public function removeParticipant(Tournament $tournament, TournamentParticipant $participant)
    {
        if ($participant->tournament_id !== $tournament->id) {
            abort(403);
        }

        $slot = $participant->slot_number;
        $team = $participant->team_name;

        // Jika tim yang dihapus sebelumnya adalah pemenang, reset juara
        if ($tournament->winner_participant_id === $participant->id) {
            $tournament->update(['winner_participant_id' => null]);
        }

        $participant->delete();

        // Kembalikan status ke 'open' jika sebelumnya 'full'
        if ($tournament->status === 'full') {
            $tournament->update(['status' => 'open']);
        }

        return back()->with('success', "Slot #{$slot} ({$team}) berhasil dikosongkan.");
    }

    /**
     * Memulai pertandingan turnamen (Ubah status ke 'ongoing').
     */
    public function startTournament(Tournament $tournament)
    {
        if ($tournament->participants()->count() < 2) {
            return back()->with('error', 'Minimal harus ada 2 tim terdaftar untuk dapat memulai turnamen!');
        }

        $tournament->update([
            'status' => 'ongoing',
        ]);

        return back()->with('success', "🚀 Turnamen {$tournament->name} ({$tournament->session_label}) resmi DIMULAI! Anda sekarang dapat menandai tim pemenang setelah laga selesai.");
    }

    /**
     * Tandai salah satu tim peserta sebagai JUARA 1 (Winner Takes All).
     * HANYA BISA KETIKA TURNAMEN SUDAH DIMULAI (status: ongoing).
     */
    public function setWinner(Tournament $tournament, TournamentParticipant $participant)
    {
        if ($participant->tournament_id !== $tournament->id) {
            abort(403);
        }

        if ($tournament->status !== 'ongoing') {
            return back()->with('error', '⛔ Pemenang belum dapat dipilih! Turnamen harus dimulai terlebih dahulu (tekan tombol Mulai Turnamen).');
        }

        DB::transaction(function () use ($tournament, $participant) {
            // Reset status juara peserta lain di turnamen ini
            $tournament->participants()->update(['is_winner' => false]);

            // Set peserta ini sebagai juara 1
            $participant->update(['is_winner' => true]);
            $tournament->update([
                'winner_participant_id' => $participant->id,
            ]);
        });

        // Generate template pesan WA untuk menghubungi sang juara
        $prizeFormatted = 'Rp ' . number_format($tournament->prize_pool, 0, ',', '.');
        $waConfirmText = "Halo Tim *{$participant->team_name}*, Selamat telah berhasil menjadi *JUARA 1* pada Turnamen eFootball Mobile ({$tournament->name} - {$tournament->session_label})!\n\n"
                       . "🎁 Hadiah sebesar *{$prizeFormatted}* akan segera kami transfer.\n"
                       . "Mohon kirimkan data rekening Bank / Nomor E-Wallet Anda (DANA/Gopay/OVO/ShopeePay). Terima kasih!";

        return back()->with('success', "Tim {$participant->team_name} berhasil ditetapkan sebagai JUARA 1!")
                     ->with('winner_wa_message', $waConfirmText)
                     ->with('winner_wa_phone', $participant->contact_wa);
    }

    /**
     * Upload bukti transfer hadiah ke pemenang turnamen.
     */
    public function uploadPrizeProof(Request $request, Tournament $tournament)
    {
        $request->validate([
            'prize_proof' => 'required|image|max:5120',
        ]);

        if ($request->hasFile('prize_proof')) {
            // Hapus file lama jika ada
            if ($tournament->prize_proof_path && Storage::disk('public')->exists($tournament->prize_proof_path)) {
                Storage::disk('public')->delete($tournament->prize_proof_path);
            }

            $path = $request->file('prize_proof')->store('tournaments/prize_proofs', 'public');
            $tournament->update([
                'prize_proof_path' => $path,
                'prize_transferred' => true,
            ]);
        }

        return back()->with('success', 'Bukti transfer hadiah ke pemenang berhasil diunggah!');
    }

    /**
     * Selesaikan sesi turnamen (Tandai Selesai & kunci hasil).
     */
    public function completeSession(Tournament $tournament)
    {
        $tournament->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('tour-organizer.efootball.index')
            ->with('success', "Turnamen {$tournament->name} ({$tournament->session_label}) telah selesai. Profit Rp " . number_format($tournament->admin_profit, 0, ',', '.') . " berhasil dicatat!");
    }

    /**
     * Hapus sesi turnamen.
     */
    public function destroy(Tournament $tournament)
    {
        if ($tournament->prize_proof_path && Storage::disk('public')->exists($tournament->prize_proof_path)) {
            Storage::disk('public')->delete($tournament->prize_proof_path);
        }

        $name = "{$tournament->name} ({$tournament->session_label})";
        $tournament->delete();

        return redirect()->route('tour-organizer.efootball.index')
            ->with('success', "{$name} berhasil dihapus.");
    }

    /**
     * Reset semua sesi turnamen yang sedang aktif untuk memulai hari baru.
     */
    public function resetSessions(Request $request)
    {
        $activeSessions = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->get();
        $count = $activeSessions->count();

        foreach ($activeSessions as $session) {
            // Jika sesi sudah ada pesertanya, tandai selesai agar profit tercatat
            if ($session->participants()->count() > 0) {
                $session->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            } else {
                // Jika sesi masih kosong tanpa peserta, hapus langsung
                $session->delete();
            }
        }

        return redirect()->route('tour-organizer.efootball.index')
            ->with('success', "Berhasil mereset {$count} sesi aktif! Sesi baru sekarang dapat dibuka dari awal.");
    }

    /**
     * Dashboard Penghasilan Khusus Divisi Turnamen eFootball.
     */
    public function dashboard()
    {
        // 1. Metrik Finansial Turnamen
        $totalCompleted = Tournament::where('status', 'completed')->count();
        $totalProfitAccumulated = (float)Tournament::where('status', 'completed')->sum('admin_profit');
        
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $thisMonthProfit = (float)Tournament::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->sum('admin_profit');
        $thisMonthCompletedCount = Tournament::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->count();

        $todayProfit = (float)Tournament::where('status', 'completed')
            ->whereDate('completed_at', now()->today())
            ->sum('admin_profit');

        $activeSessionsCount = Tournament::whereIn('status', ['open', 'full', 'ongoing'])->count();

        // 2. Dataset Grafik Harian (7 Hari Terakhir)
        $dailyLabels = [];
        $dailyValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->today()->subDays($i);
            $dailyLabels[] = $date->translatedFormat('d M');
            $dailyValues[] = (float)Tournament::where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->sum('admin_profit');
        }

        // 3. Dataset Grafik Mingguan (6 Minggu Terakhir)
        $weeklyLabels = [];
        $weeklyValues = [];
        for ($w = 5; $w >= 0; $w--) {
            $startWeek = now()->subWeeks($w)->startOfWeek();
            $endWeek = now()->subWeeks($w)->endOfWeek();
            $weeklyLabels[] = $startWeek->format('d/m') . '-' . $endWeek->format('d/m');
            $weeklyValues[] = (float)Tournament::where('status', 'completed')
                ->whereBetween('completed_at', [$startWeek, $endWeek])
                ->sum('admin_profit');
        }

        // 4. Breakdown Performa berdasarkan Biaya Pendaftaran (5K, 10K, 15K, 20K, dll)
        $presetBreakdown = Tournament::where('status', 'completed')
            ->select('entry_fee', DB::raw('count(*) as count'), DB::raw('sum(admin_profit) as total_profit'))
            ->groupBy('entry_fee')
            ->orderBy('entry_fee')
            ->get();

        // 5. Pemenang Terakhir (Hall of Fame)
        $recentWinners = Tournament::with('winner')
            ->where('status', 'completed')
            ->whereNotNull('winner_participant_id')
            ->latest('completed_at')
            ->take(5)
            ->get();

        return view('tour-organizer.index', compact(
            'totalCompleted', 'totalProfitAccumulated', 'thisMonthProfit', 'thisMonthCompletedCount',
            'todayProfit', 'activeSessionsCount', 'dailyLabels', 'dailyValues', 'weeklyLabels', 'weeklyValues',
            'presetBreakdown', 'recentWinners'
        ));
    }
}
