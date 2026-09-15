<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Category;
use App\Services\PromotionBannerGenerator;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with('category')->latest('sort_order')->latest('id');

        $selectedCategory = $request->query('category', 'all');
        $search = trim($request->query('search', ''));

        if ($selectedCategory !== 'all') {
            if (is_numeric($selectedCategory)) {
                $query->where('category_id', (int)$selectedCategory);
            } else {
                $query->where('category_type', $selectedCategory);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%")
                  ->orWhere('copywriting', 'like', "%{$search}%");
            });
        }

        $promotions = $query->paginate(9)->withQueryString();
        $categories = Category::all();

        $totalCount = Promotion::count();
        $jokiCount = Promotion::where('category_type', 'joki')->count();
        $webCount = Promotion::where('category_type', 'website')->count();
        $tourCount = Promotion::where('category_type', 'tournament')->count();

        return view('promotions.index', compact(
            'promotions', 'categories', 'selectedCategory', 'search',
            'totalCount', 'jokiCount', 'webCount', 'tourCount'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        return view('promotions.create', compact('categories'));
    }

    public function store(Request $request, PromotionBannerGenerator $generator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'category_type' => 'required|in:joki,website,tournament,general',
            'tagline' => 'nullable|string|max:255',
            'copywriting' => 'required|string',
            'banner_option' => 'required|in:upload,generate,none',
            'banner_file' => 'nullable|image|max:5120',
            'target_platform' => 'nullable|string|max:255',
        ]);

        $bannerPath = null;
        $bannerType = 'uploaded';

        if ($request->banner_option === 'upload' && $request->hasFile('banner_file')) {
            $bannerPath = $request->file('banner_file')->store('promotions/banners', 'public');
            $bannerType = 'uploaded';
        } elseif ($request->banner_option === 'generate') {
            $categoryName = null;
            if ($request->filled('category_id')) {
                $cat = Category::find($request->category_id);
                $categoryName = $cat?->name;
            } elseif ($request->category_type === 'tournament') {
                $categoryName = 'Turnamen eFootball';
            } elseif ($request->category_type === 'website') {
                $categoryName = 'Jasa Website & Coding';
            }

            $filename = 'promo_' . time() . '_' . uniqid() . '.jpg';
            $bannerPath = "promotions/banners/{$filename}";
            $absPath = storage_path("app/public/{$bannerPath}");

            $generator->generate($validated['title'], $categoryName, $validated['tagline'] ?? null, $absPath);
            $bannerType = 'auto_generated';
        }

        $promotion = Promotion::create([
            'category_id' => $request->category_id,
            'category_type' => $request->category_type,
            'title' => $validated['title'],
            'tagline' => $validated['tagline'] ?? null,
            'banner_path' => $bannerPath,
            'banner_type' => $bannerType,
            'copywriting' => $validated['copywriting'],
            'target_platform' => $request->input('target_platform', 'WhatsApp & Telegram'),
            'is_active' => true,
        ]);

        return redirect()->route('promotions.index')
            ->with('success', "Materi promosi '{$promotion->title}' berhasil dibuat dan siap disebarkan!");
    }

    public function edit(Promotion $promotion)
    {
        $categories = Category::all();
        return view('promotions.edit', compact('promotion', 'categories'));
    }

    public function update(Request $request, Promotion $promotion, PromotionBannerGenerator $generator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'category_type' => 'required|in:joki,website,tournament,general',
            'tagline' => 'nullable|string|max:255',
            'copywriting' => 'required|string',
            'banner_option' => 'required|in:keep,upload,generate,remove',
            'banner_file' => 'nullable|image|max:5120',
            'target_platform' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $bannerPath = $promotion->banner_path;
        $bannerType = $promotion->banner_type;

        if ($request->banner_option === 'upload' && $request->hasFile('banner_file')) {
            $bannerPath = $request->file('banner_file')->store('promotions/banners', 'public');
            $bannerType = 'uploaded';
        } elseif ($request->banner_option === 'generate') {
            $categoryName = null;
            if ($request->filled('category_id')) {
                $cat = Category::find($request->category_id);
                $categoryName = $cat?->name;
            } elseif ($request->category_type === 'tournament') {
                $categoryName = 'Turnamen eFootball';
            } elseif ($request->category_type === 'website') {
                $categoryName = 'Jasa Website & Coding';
            }

            $filename = 'promo_' . time() . '_' . uniqid() . '.jpg';
            $bannerPath = "promotions/banners/{$filename}";
            $absPath = storage_path("app/public/{$bannerPath}");

            $generator->generate($validated['title'], $categoryName, $validated['tagline'] ?? null, $absPath);
            $bannerType = 'auto_generated';
        } elseif ($request->banner_option === 'remove') {
            $bannerPath = null;
        }

        $promotion->update([
            'category_id' => $request->category_id,
            'category_type' => $request->category_type,
            'title' => $validated['title'],
            'tagline' => $validated['tagline'] ?? null,
            'banner_path' => $bannerPath,
            'banner_type' => $bannerType,
            'copywriting' => $validated['copywriting'],
            'target_platform' => $request->input('target_platform', 'WhatsApp & Telegram'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promotions.index')
            ->with('success', "Materi promosi '{$promotion->title}' berhasil diperbarui!");
    }

    public function destroy(Promotion $promotion)
    {
        if ($promotion->banner_path && file_exists(storage_path('app/public/' . $promotion->banner_path))) {
            @unlink(storage_path('app/public/' . $promotion->banner_path));
        }
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Materi promosi berhasil dihapus.');
    }

    public function downloadBanner(Promotion $promotion)
    {
        if (!$promotion->banner_path || !file_exists(storage_path('app/public/' . $promotion->banner_path))) {
            return back()->with('error', 'File banner promosi tidak ditemukan.');
        }

        $fullPath = storage_path('app/public/' . $promotion->banner_path);
        $filename = 'PROMO_' . \Illuminate\Support\Str::slug($promotion->title) . '.jpg';

        return response()->download($fullPath, $filename);
    }

    public function postToTelegram(Promotion $promotion, TelegramService $telegram)
    {
        if (!$promotion->banner_path || !file_exists(storage_path('app/public/' . $promotion->banner_path))) {
            return back()->with('error', 'Banner promosi belum ada. Buat atau unggah poster terlebih dahulu.');
        }

        $fullPath = storage_path('app/public/' . $promotion->banner_path);
        $caption = $promotion->copywriting;

        $msgId = $telegram->sendPhoto($fullPath, $caption);

        if ($msgId) {
            return back()->with('success', "🚀 Materi promosi '{$promotion->title}' berhasil diposting ke Channel Telegram!");
        }

        $err = $telegram->getLastError();
        return back()->with('error', "Gagal memposting ke Telegram: {$err}");
    }
}
